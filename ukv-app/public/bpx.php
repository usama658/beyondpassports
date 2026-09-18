<?php

// Read a key from the Laravel .env (../.env) — bpx.php is a standalone file so getenv()
// can't see Laravel's env, and a SetEnv in .htaccess is wiped by `git reset --hard` on
// deploy. The .env lives outside the web root (not git-tracked) and survives deploys.
function bpx_env($key) {
    static $env = null;
    if ($env === null) {
        $env = [];
        $f = __DIR__ . '/../.env';
        if (is_readable($f)) {
            foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
                list($k, $v) = explode('=', $line, 2);
                $env[trim($k)] = trim(trim(trim($v), '"'), "'");
            }
        }
    }
    return $env[$key] ?? '';
}

$BPX = [
    'crm'          => getenv('CRM_URL') ?: (bpx_env('CRM_URL') ?: 'https://visacrm-production.up.railway.app'),
    'brand'        => 'beyond-passports',
    'secret'       => getenv('CRM_PROXY_SECRET') ?: bpx_env('CRM_PROXY_SECRET'),
    'site'         => 'beyondpassports.co.uk',
    'turnstile'    => getenv('TURNSTILE_SECRET_KEY') ?: '',
    'browser_leads'=> getenv('BPX_BROWSER_LEADS') !== 'off',
    'dir'          => rtrim(getenv('BPX_DATA_DIR') ?: sys_get_temp_dir() . '/bpx', '/'),
    'keys'         => ['gclid', 'gbraid', 'wbraid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'],
];

function bpx_clean($v, $max = 300) {
    $v = preg_replace('/[^\w.\-~:+%\/ ]/u', '', (string) $v);
    return substr(trim((string) $v), 0, $max);
}

function bpx_ip_in($ip, $cidr) {
    list($net, $bits) = explode('/', $cidr);
    $a = @inet_pton($ip);
    $b = @inet_pton($net);
    if ($a === false || $b === false || strlen($a) !== strlen($b)) return false;
    $bits = (int) $bits;
    $bytes = intdiv($bits, 8);
    if ($bytes && substr($a, 0, $bytes) !== substr($b, 0, $bytes)) return false;
    $rem = $bits % 8;
    if (!$rem) return true;
    $mask = chr((0xff << (8 - $rem)) & 0xff);
    return ($a[$bytes] & $mask) === ($b[$bytes] & $mask);
}

function bpx_from_cloudflare($ip) {
    $ranges = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
        '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
        '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
        '2a06:98c0::/29', '2c0f:f248::/32',
    ];
    foreach ($ranges as $r) {
        if (bpx_ip_in($ip, $r)) return true;
    }
    return false;
}

function bpx_visitor_ip() {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $ip = $remote;
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && bpx_from_cloudflare($remote)) {
        $ip = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

function bpx_dir($sub) {
    global $BPX;
    $d = $BPX['dir'] . '/' . $sub;
    if (!is_dir($d)) @mkdir($d, 0700, true);
    return is_dir($d) && is_writable($d) ? $d : '';
}

function bpx_allow($bucket, $limit, $window) {
    $d = bpx_dir('rl');
    $ip = bpx_visitor_ip();
    if ($d === '' || $ip === '') return true;
    $f = $d . '/' . $bucket . '-' . sha1($ip);
    $h = @fopen($f, 'c+');
    if (!$h) return true;
    flock($h, LOCK_EX);
    $now = time();
    $hits = array_filter(explode(',', (string) stream_get_contents($h)), function ($t) use ($now, $window) {
        return $t !== '' && (int) $t > $now - $window;
    });
    $ok = count($hits) < $limit;
    if ($ok) $hits[] = $now;
    ftruncate($h, 0);
    rewind($h);
    fwrite($h, implode(',', $hits));
    flock($h, LOCK_UN);
    fclose($h);
    if (mt_rand(1, 200) === 1) {
        foreach ((array) glob($d . '/*') as $old) {
            if (@filemtime($old) < $now - 86400) @unlink($old);
        }
    }
    return $ok;
}

function bpx_post($path, $body, $type, $ip) {
    global $BPX;
    if ($BPX['secret'] === '' || !function_exists('curl_init')) return 0;
    $headers = ['Content-Type: ' . $type, 'X-Site-Proxy-Secret: ' . $BPX['secret']];
    if ($ip !== '') $headers[] = 'X-Visitor-IP: ' . $ip;
    $ch = curl_init($BPX['crm'] . $path);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 8,
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $code;
}

function bpx_queue($path, $body, $type, $ip) {
    $d = bpx_dir('queue');
    if ($d === '') return false;
    $item = json_encode(['path' => $path, 'body' => $body, 'type' => $type, 'ip' => $ip, 'at' => time(), 'tries' => 0]);
    $f = $d . '/' . microtime(true) . '-' . bin2hex(random_bytes(4)) . '.json';
    if (@file_put_contents($f, $item, LOCK_EX) === false) return false;
    @chmod($f, 0600);
    return true;
}

function bpx_send($path, $body, $type) {
    $ip = bpx_visitor_ip();
    $code = bpx_post($path, $body, $type, $ip);
    if ($code >= 200 && $code < 300) return 'sent';
    if ($code >= 400 && $code < 500 && $code !== 429) return 'rejected';
    return bpx_queue($path, $body, $type, $ip) ? 'queued' : 'failed';
}

function bpx_retry($max = 5) {
    $d = bpx_dir('queue');
    if ($d === '') return;
    $lock = @fopen($d . '/.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) return;
    $files = (array) glob($d . '/*.json');
    sort($files);
    foreach (array_slice($files, 0, $max) as $f) {
        $item = json_decode((string) @file_get_contents($f), true);
        if (!is_array($item) || time() - (int) $item['at'] > 172800 || (int) $item['tries'] >= 30) {
            @rename($f, $f . '.dead');
            continue;
        }
        if (time() - (int) @filemtime($f) < 60) continue;
        $code = bpx_post($item['path'], $item['body'], $item['type'], $item['ip']);
        if (($code >= 200 && $code < 300) || ($code >= 400 && $code < 500 && $code !== 429)) {
            @unlink($f);
        } else {
            $item['tries'] = (int) $item['tries'] + 1;
            @file_put_contents($f, json_encode($item), LOCK_EX);
            break;
        }
    }
    flock($lock, LOCK_UN);
    fclose($lock);
}

function bpx_turnstile_ok($token) {
    global $BPX;
    if ($BPX['turnstile'] === '') return true;
    if (!is_string($token) || $token === '' || strlen($token) > 2048 || !function_exists('curl_init')) return false;
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    $fields = ['secret' => $BPX['turnstile'], 'response' => $token];
    $ip = bpx_visitor_ip();
    if ($ip !== '') $fields['remoteip'] = $ip;
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 6,
    ]);
    $res = json_decode((string) curl_exec($ch), true);
    return is_array($res) && !empty($res['success']);
}

function bpx_send_lead(array $lead) {
    global $BPX;
    $allowed = ['firstName', 'lastName', 'phone', 'email', 'message', 'destination', 'visaType', 'travelDate', 'formName', 'page'];
    $fields = [];
    foreach ($allowed as $k) {
        if (isset($lead[$k]) && is_scalar($lead[$k]) && trim((string) $lead[$k]) !== '') {
            $fields[$k] = substr(trim((string) $lead[$k]), 0, $k === 'message' ? 4000 : 254);
        }
    }
    if (empty($fields['phone'])) return 'rejected';
    foreach ($BPX['keys'] as $k) {
        $v = bpx_clean($lead[$k] ?? ($_COOKIE[$k] ?? ''));
        if ($v !== '') $fields[$k] = $v;
    }
    if (empty($fields['page'])) {
        $fields['page'] = substr((string) parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH), 0, 200);
    }
    $fields['landing_path'] = $fields['page'];
    return bpx_send('/api/intake/site?brand=' . $BPX['brand'], http_build_query($fields), 'application/x-www-form-urlencoded');
}

if (defined('BPX_LIBRARY')) return;

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && !preg_match('#^https://(www\.)?' . preg_quote($BPX['site'], '#') . '$#i', $origin)) {
    http_response_code(403);
    exit;
}

$type = $_GET['t'] ?? '';
$status = 404;

if ($type === 'sync') {
    foreach ($BPX['keys'] as $k) {
        if (!isset($_POST[$k]) || !is_string($_POST[$k])) continue;
        $v = bpx_clean($_POST[$k]);
        if ($v === '') continue;
        setcookie($k, $v, [
            'expires'  => time() + 7776000,
            'path'     => '/',
            'secure'   => true,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
    $status = 204;
} elseif ($type === 'click') {
    if (!bpx_allow('click', 60, 600)) {
        $status = 429;
    } else {
        $data = json_decode((string) file_get_contents('php://input', false, null, 0, 8192), true);
        if (!is_array($data) || empty($data['ref']) || !is_string($data['ref'])) {
            $status = 400;
        } else {
            $r = bpx_send('/api/track/wa-click?brand=' . $BPX['brand'], json_encode($data), 'text/plain');
            $status = $r === 'sent' ? 204 : ($r === 'queued' ? 202 : ($r === 'rejected' ? 400 : 502));
        }
    }
} elseif ($type === 'lead') {
    if (!$BPX['browser_leads']) {
        $status = 204;
    } elseif (!bpx_allow('lead', 10, 600)) {
        $status = 429;
    } else {
        $fields = [];
        foreach ($_POST as $k => $v) {
            if (!is_string($k) || !is_string($v) || strlen($k) > 40) continue;
            $fields[$k] = substr($v, 0, 4000);
            if (count($fields) >= 40) break;
        }
        if (empty($fields['phone'])) {
            $status = 400;
        } else {
            $r = bpx_send('/api/intake/site?brand=' . $BPX['brand'], http_build_query($fields), 'application/x-www-form-urlencoded');
            $status = $r === 'sent' ? 204 : ($r === 'queued' ? 202 : ($r === 'rejected' ? 400 : 502));
        }
    }
}

http_response_code($status);
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
    bpx_retry(10);
} elseif ($status === 204) {
    bpx_retry(1);
}
