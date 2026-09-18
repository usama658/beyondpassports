{{-- Lead attribution — gclid/UTM capture + WhatsApp CRM ref/beacon + form->CRM (Beyond Passports).
     CRM dev's canonical "complete" script (v2, same-origin proxy), implemented verbatim. Injected
     site-wide (layout + static-country route + LpVariantController). Posts go to same-origin
     /bpx.php (public/bpx.php), which forwards to visacrm-production server-side with the
     X-Site-Proxy-Secret header — so no CSP/CORS/custom-domain issues on the browser side; direct
     sendBeacon to go.beyondpassports.co.uk / visacrm-production is the fallback only.
     REQUIRES env CRM_PROXY_SECRET set on the host for bpx.php to forward to the CRM. --}}
<script>
(function() {
  try {
    if (window.__bpAttr) return;
    window.__bpAttr = true;
    if (typeof URLSearchParams !== "function" || typeof FormData !== "function" || typeof Blob !== "function" || !document.querySelectorAll) return;
    var CRM = "https://go.beyondpassports.co.uk";
    var TRACK = CRM + "/api/track/wa-click?brand=beyond-passports";
    var FORM = CRM + "/api/intake/site?brand=beyond-passports";
    var PREFIX = "BP";
    var PROXY = "/bpx.php";
    var LEAD_COPY = true;
    var LEAD_PATHS = /\/lead\/?$/i;
    var _fetchRef = window.fetch;
    function noop() {}
    function post(kind, body, direct) {
      var fallback = function() {
        try {
          if (navigator.sendBeacon) navigator.sendBeacon(direct, body);
        } catch (e) {}
      };
      if (!PROXY || typeof _fetchRef !== "function") return fallback();
      try {
        _fetchRef.call(window, PROXY + "?t=" + kind, {
          method: "POST",
          body: body,
          keepalive: true,
          credentials: "same-origin"
        }).then(function(r) {
          if (!r || !r.ok) fallback();
        }, fallback);
      } catch (e) {
        fallback();
      }
    }
    var KEYS = [ "gclid", "gbraid", "wbraid", "utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content" ];
    var COOKIE_MAX = 7776e3;
    var MAX_LEN = 300;
    function clean(v) {
      if (typeof v !== "string") return "";
      v = v.replace(/[^\w.\-~:+%/ ]/g, "").trim();
      return v.slice(0, MAX_LEN);
    }
    function setCookie(k, v) {
      try {
        document.cookie = k + "=" + encodeURIComponent(v) + ";path=/;max-age=" + COOKIE_MAX + ";samesite=lax" + (location.protocol === "https:" ? ";secure" : "");
      } catch (e) {}
    }
    function ck(n) {
      try {
        var m = document.cookie.match(new RegExp("(?:^|; )" + n + "=([^;]*)"));
        return m ? clean(decodeURIComponent(m[1])) : "";
      } catch (e) {
        return "";
      }
    }
    try {
      var qs = new URLSearchParams(location.search);
      var sync = null;
      KEYS.forEach(function(k) {
        var v = clean(qs.get(k) || "");
        if (!v) return;
        setCookie(k, v);
        if (!sync) sync = new FormData;
        sync.append(k, v);
      });
      if (sync && PROXY && typeof _fetchRef === "function") {
        _fetchRef.call(window, PROXY + "?t=sync", {
          method: "POST",
          body: sync,
          credentials: "same-origin",
          keepalive: true
        }).catch(function() {});
      }
    } catch (e) {}
    function newRef() {
      var s = "ABCDEFGHJKMNPQRSTUVWXYZ23456789", r = "";
      try {
        var a = new Uint32Array(6);
        window.crypto.getRandomValues(a);
        for (var i = 0; i < 6; i++) r += s[a[i] % s.length];
      } catch (e) {
        for (var j = 0; j < 6; j++) r += s[Math.floor(Math.random() * s.length)];
      }
      return PREFIX + "-" + r;
    }
    function beacon(ref) {
      try {
        var b = {
          ref: ref,
          page: location.pathname.slice(0, 200)
        };
        KEYS.forEach(function(k) {
          var v = ck(k);
          if (v) b[k] = v;
        });
        post("click", new Blob([ JSON.stringify(b) ], {
          type: "text/plain"
        }), TRACK);
      } catch (e) {}
    }
    function isWa(url) {
      return typeof url === "string" && /^(https:\/\/(wa\.me|api\.whatsapp\.com)\/|whatsapp:\/\/send)/i.test(url);
    }
    function toE164(phone) {
      var p = String(phone || "").replace(/[^\d+]/g, "");
      if (p.indexOf("00") === 0) p = "+" + p.slice(2);
      if (p.charAt(0) === "0") p = "+44" + p.slice(1);
      if (p.charAt(0) !== "+") p = "+" + p;
      return /^\+\d{8,15}$/.test(p) ? p : "";
    }
    function googleUserData(email, phone) {
      try {
        if (typeof window.gtag !== "function") return;
        var d = {};
        if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) d.email = email.trim().toLowerCase();
        var p = toE164(phone);
        if (p) d.phone_number = p;
        if (d.email || d.phone_number) window.gtag("set", "user_data", d);
      } catch (e) {}
    }
    var pending = null;
    function noteWa(text) {
      text = String(text || "");
      if (pending && !pending.wa) {
        pending.wa = text.slice(0, 1500);
        return;
      }
      var pm = text.match(/(?:phone|number|mobile|whatsapp)\s*:\s*(\+?[\d][\d\s().-]{6,20}\d)/i);
      if (!pm) return;
      var nm = text.match(/name\s*:\s*([^.\n]{1,80})/i);
      var dm = text.match(/destination\s*:\s*([^.\n]{1,60})/i);
      var o = {
        phone: pm[1].trim(),
        message: ""
      };
      if (nm) o.name = nm[1].trim();
      if (dm) o.destination = dm[1].trim();
      sendData(o, "whatsapp-prefill");
      if (pending && !pending.wa) pending.wa = text.slice(0, 1500);
    }
    var refMemo = {};
    function refFor(text) {
      var key = (text || "").slice(0, 500);
      var hit = refMemo[key];
      if (hit && Date.now() - hit.t < 6e5) return {
        ref: hit.ref,
        fresh: false
      };
      var ref = newRef();
      refMemo[key] = {
        ref: ref,
        t: Date.now()
      };
      return {
        ref: ref,
        fresh: true
      };
    }
    window.bpUtm = function() {
      var o = {};
      KEYS.forEach(function(k) {
        var v = ck(k);
        if (v) o[k] = v;
      });
      return Object.keys(o).length ? o : null;
    };
    window.bpWaUrl = function(url) {
      try {
        if (!isWa(url)) return url;
        var u = new URL(url), t = u.searchParams.get("text") || "";
        if (t.indexOf("(Ref: ") > -1) return url;
        var r = refFor(t);
        if (r.fresh) beacon(r.ref);
        u.searchParams.set("text", (t ? t + " " : "") + "(Ref: " + r.ref + ")");
        noteWa(u.searchParams.get("text"));
        return u.toString();
      } catch (e) {
        return url;
      }
    };
    window.bpWa = function(text) {
      text = text || "";
      if (text.indexOf("(Ref: ") > -1) return text;
      var r = refFor(text);
      if (r.fresh) beacon(r.ref);
      var out = (text ? text + " " : "") + "(Ref: " + r.ref + ")";
      noteWa(out);
      return out;
    };
    var _open = window.open;
    if (typeof _open === "function") {
      try {
        window.open = function(u) {
          try {
            if (typeof u === "string") arguments[0] = window.bpWaUrl(u);
          } catch (e) {}
          return _open.apply(this, arguments);
        };
      } catch (e) {}
    }
    function wireAnchors() {
      document.querySelectorAll('a[href*="wa.me/"],a[href*="api.whatsapp.com"]').forEach(function(a) {
        if (a.getAttribute("data-wa-tracked")) return;
        a.setAttribute("data-wa-tracked", "1");
        a.addEventListener("click", function() {
          try {
            a.href = window.bpWaUrl(a.href);
          } catch (e) {}
        });
      });
    }
    function fillFields() {
      document.querySelectorAll("input[type=hidden]").forEach(function(el) {
        if (KEYS.indexOf(el.name) === -1) return;
        var v = ck(el.name);
        if (v && !el.value) el.value = v;
      });
    }
    var SKIP = /^(_token|consent|password|g-recaptcha-response|cf-turnstile-response|website|url|honeypot)$/i;
    var PHONE = /(phone|mobile|whatsapp|^tel$|^telephone$|contact_?(no|number))/i;
    var DIAL = /^(phone_?dialcode|dial_?code|country_?code|phone_?country)$/i;
    var FIRST = /^(first_?name|fname|given_?name)$/i;
    var LAST = /^(last_?name|lname|surname|family_?name)$/i;
    var FULL = /^(name|full_?name|your_?name|client_?name)$/i;
    var EMAIL = /^(email|e_?mail|your_?email)$/i;
    var MSG = /^(message|notes?|enquiry|inquiry|comments?|details)$/i;
    var DEST = /^(destination|destination_?country|country|going_?to|travel_?destination)$/i;
    var VISA = /^(visa|visa_?type|service|visa_?service|service_?type)$/i;
    var TRAVEL = /^(travel_?date|date_?of_?travel|departure_?date|trip_?date)$/i;
    function fieldKey(el) {
      var n = el.name || el.id || "";
      return n.replace(/^form_fields\[(.+)\]$/, "$1");
    }
    function readForm(f) {
      var out = {
        phone: "",
        dial: "",
        first: "",
        last: "",
        full: "",
        email: "",
        msg: "",
        dest: "",
        visa: "",
        travel: "",
        extra: []
      };
      var els = f.querySelectorAll("input, select, textarea");
      for (var i = 0; i < els.length; i++) {
        var el = els[i];
        var key = fieldKey(el);
        if (!key || SKIP.test(key) || KEYS.indexOf(key) > -1) continue;
        var type = (el.type || "").toLowerCase();
        if (type === "submit" || type === "button" || type === "file" || type === "password") continue;
        if ((type === "checkbox" || type === "radio") && !el.checked) continue;
        var v = typeof el.value === "string" ? el.value.trim() : "";
        if (!v) continue;
        if (!out.phone && (PHONE.test(key) || type === "tel")) out.phone = v; else if (DIAL.test(key)) out.dial = v; else if (FIRST.test(key)) out.first = v; else if (LAST.test(key)) out.last = v; else if (FULL.test(key)) out.full = v; else if (!out.email && (EMAIL.test(key) || type === "email")) out.email = v; else if (MSG.test(key)) out.msg = v; else if (!out.dest && DEST.test(key)) out.dest = el.tagName === "SELECT" && el.selectedOptions && el.selectedOptions[0] ? el.selectedOptions[0].text.trim() : v; else if (!out.visa && VISA.test(key)) out.visa = el.tagName === "SELECT" && el.selectedOptions && el.selectedOptions[0] ? el.selectedOptions[0].text.trim() : v; else if (!out.travel && TRAVEL.test(key)) out.travel = v; else if (type !== "hidden") {
          var label = key.replace(/[_\-]+/g, " ");
          out.extra.push(label.charAt(0).toUpperCase() + label.slice(1) + ": " + v.slice(0, 200));
        }
      }
      return out;
    }
    function hasPhoneField(f) {
      var els = f.querySelectorAll("input");
      for (var i = 0; i < els.length; i++) {
        if ((els[i].type || "").toLowerCase() === "tel" || PHONE.test(fieldKey(els[i]))) return true;
      }
      return false;
    }
    function sendForm(f) {
      try {
        if (!navigator.sendBeacon && typeof _fetchRef !== "function") return;
        if (f.getAttribute("data-crm-sent")) return;
        var consent = f.querySelector('[name="consent"]');
        if (consent && consent.type === "checkbox" && !consent.checked) return;
        if (typeof f.checkValidity === "function" && !f.checkValidity()) return;
        var d = readForm(f);
        if (!d.phone) return;
        var phone = d.phone;
        if (phone.charAt(0) !== "+") phone = (d.dial || "+44") + " " + phone.replace(/^0+/, "");
        var first = d.first, last = d.last;
        if (!first && d.full) {
          var parts = d.full.split(/\s+/);
          first = parts[0];
          last = parts.slice(1).join(" ");
        }
        var lines = [];
        if (d.msg) lines.push(d.msg);
        d.extra.forEach(function(x) {
          lines.push(x);
        });
        var fd = new FormData;
        if (first) fd.append("firstName", first.slice(0, 100));
        if (last) fd.append("lastName", last.slice(0, 100));
        fd.append("phone", phone.slice(0, 30));
        if (d.email) fd.append("email", d.email.slice(0, 254));
        if (d.dest) fd.append("destination", d.dest.slice(0, 100));
        if (d.visa) fd.append("visaType", d.visa.slice(0, 100));
        if (d.travel) fd.append("travelDate", d.travel.slice(0, 40));
        if (lines.length) fd.append("message", lines.join("\n").slice(0, 4e3));
        fd.append("formName", (f.id || f.getAttribute("name") || f.getAttribute("aria-label") || "enquiry").slice(0, 80));
        fd.append("page", location.pathname.slice(0, 200));
        KEYS.forEach(function(k) {
          var v = ck(k);
          if (v) fd.append(k, v);
        });
        fd.append("landing_path", location.pathname.slice(0, 200));
        googleUserData(d.email, phone);
        if (!LEAD_COPY || recentlySent(phone.replace(/\D/g, ""))) return;
        post("lead", fd, FORM);
        f.setAttribute("data-crm-sent", "1");
      } catch (e) {}
    }
    var sentPhones = {};
    function recentlySent(digits) {
      var t = sentPhones[digits];
      if (t && Date.now() - t < 6e4) return true;
      sentPhones[digits] = Date.now();
      return false;
    }
    function pick(o, re) {
      for (var k in o) {
        if (Object.prototype.hasOwnProperty.call(o, k) && re.test(k) && typeof o[k] === "string" && o[k].trim()) return o[k].trim();
      }
      return "";
    }
    function sendData(o, formName) {
      try {
        if (!navigator.sendBeacon && typeof _fetchRef !== "function" || !o || typeof o !== "object") return;
        if (typeof o.website === "string" && o.website) return;
        var phone = pick(o, PHONE);
        var digits = phone.replace(/\D/g, "");
        if (digits.length < 7) return;
        googleUserData(pick(o, EMAIL), phone);
        if (!LEAD_COPY || recentlySent(digits)) return;
        var full = pick(o, FULL), first = pick(o, FIRST), last = pick(o, LAST);
        if (!first && full) {
          var parts = full.split(/\s+/);
          first = parts[0];
          last = parts.slice(1).join(" ");
        }
        var used = /^(website|utm|_token|consent|source|page)$/i;
        var lines = [];
        var msg = pick(o, MSG);
        if (msg) lines.push(msg);
        for (var k in o) {
          if (!Object.prototype.hasOwnProperty.call(o, k) || typeof o[k] !== "string" || !o[k].trim()) continue;
          if (used.test(k) || PHONE.test(k) || FULL.test(k) || FIRST.test(k) || LAST.test(k) || EMAIL.test(k) || MSG.test(k) || DEST.test(k) || VISA.test(k) || TRAVEL.test(k) || /^dest$/i.test(k) || KEYS.indexOf(k) > -1) continue;
          var label = k.replace(/[_\-]+/g, " ");
          lines.push(label.charAt(0).toUpperCase() + label.slice(1) + ": " + o[k].trim().slice(0, 200));
        }
        if (typeof o.source === "string" && o.source) lines.push("Source: " + o.source.slice(0, 120));
        var fd = new FormData;
        if (first) fd.append("firstName", first.slice(0, 100));
        if (last) fd.append("lastName", last.slice(0, 100));
        fd.append("phone", phone.slice(0, 30));
        var email = pick(o, EMAIL);
        if (email) fd.append("email", email.slice(0, 254));
        var dest = pick(o, DEST) || pick(o, /^dest$/i);
        if (dest) fd.append("destination", dest.slice(0, 100));
        var visa = pick(o, VISA);
        if (visa) fd.append("visaType", visa.slice(0, 100));
        var travel = pick(o, TRAVEL);
        if (travel) fd.append("travelDate", travel.slice(0, 40));
        fd.append("formName", (formName || "enquiry").slice(0, 80));
        fd.append("page", location.pathname.slice(0, 200));
        KEYS.forEach(function(k) {
          var v = ck(k);
          if (v) fd.append(k, v);
        });
        fd.append("landing_path", location.pathname.slice(0, 200));
        if (pending) flush();
        pending = {
          fd: fd,
          lines: lines,
          wa: ""
        };
        pending.timer = setTimeout(flush, 400);
      } catch (e) {}
    }
    function flush() {
      var p = pending;
      pending = null;
      if (!p) return;
      try {
        clearTimeout(p.timer);
        var lines = p.lines.slice();
        if (p.wa) lines.push("WhatsApp message:\n" + p.wa);
        if (lines.length) p.fd.append("message", lines.join("\n").slice(0, 4e3));
        post("lead", p.fd, FORM);
      } catch (e) {}
    }
    window.addEventListener("pagehide", function() {
      try {
        flush();
      } catch (e) {}
    });
    var _fetch = window.fetch;
    if (typeof _fetch === "function") {
      var wrapped = function(input, init) {
        var res = _fetch.apply(window, arguments);
        try {
          var url = typeof input === "string" ? input : input && input.url || "";
          var method = String(init && init.method || input && input.method || "GET").toUpperCase();
          var body = init && init.body;
          if (method === "POST" && typeof body === "string" && res && typeof res.then === "function") {
            var u = new URL(url, location.href);
            if (u.origin === location.origin && LEAD_PATHS.test(u.pathname)) {
              var data = null;
              try {
                data = JSON.parse(body);
              } catch (e) {}
              if (data && typeof data === "object") {
                res.then(function(r) {
                  try {
                    if (r && r.ok) sendData(data, u.pathname);
                  } catch (e) {}
                }, noop);
              }
            }
          }
        } catch (e) {}
        return res;
      };
      try {
        window.fetch = wrapped;
      } catch (e) {}
    }
    document.addEventListener("submit", function(e) {
      try {
        var f = e.target;
        if (f && f.tagName === "FORM" && hasPhoneField(f)) sendForm(f);
      } catch (x) {}
    }, true);
    document.addEventListener("input", function(e) {
      try {
        var f = e.target && e.target.form;
        if (f) f.removeAttribute("data-crm-sent");
      } catch (x) {}
    }, true);
    function apply() {
      try {
        wireAnchors();
        fillFields();
      } catch (e) {}
    }
    if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", apply); else apply();
    if (window.MutationObserver) {
      try {
        var to = null;
        new MutationObserver(function() {
          clearTimeout(to);
          to = setTimeout(apply, 300);
        }).observe(document.documentElement, {
          childList: true,
          subtree: true
        });
      } catch (e) {}
    }
    document.addEventListener("submit", function() {
      try {
        fillFields();
      } catch (e) {}
    }, true);
  } catch (e) {}
})();</script>
{{-- window.bpLead(data): explicit lead->CRM helper for BP's WhatsApp-first forms that the
     v2 script above cannot auto-catch (button onclick -> window.open, no form submit; or a
     fetch to a non-/lead endpoint). Posts to the same-origin /bpx.php?t=lead proxy with the
     ad-click cookies attached. 60s per-phone dedup. Call it from a form's send handler with
     {name|firstName/lastName, phone, dial, email, destination, visaType, travelDate, message,
     formName}. Do NOT add it to forms the v2 script already captures (avoids double leads). --}}
<script>
(function () {
  if (window.bpLead) return;
  var sent = {};
  function ck(n) { try { var m = document.cookie.match(new RegExp("(?:^|; )" + n + "=([^;]*)")); return m ? decodeURIComponent(m[1]) : ""; } catch (e) { return ""; } }
  var KEYS = ["gclid", "gbraid", "wbraid", "utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content"];
  window.bpLead = function (d) {
    try {
      d = d || {};
      var phone = (d.phone == null ? "" : "" + d.phone).trim();
      var digits = phone.replace(/\D/g, "");
      if (digits.length < 7) return false;
      var now = Date.now();
      if (sent[digits] && now - sent[digits] < 6e4) return false;
      sent[digits] = now;
      var first = d.firstName || "", last = d.lastName || "", full = (d.name || "").trim();
      if (!first && full) { var p = full.split(/\s+/); first = p[0]; last = p.slice(1).join(" "); }
      if (phone.charAt(0) !== "+") phone = (d.dial || "+44") + " " + phone.replace(/^0+/, "");
      var fd = new FormData();
      if (first) fd.append("firstName", first.slice(0, 100));
      if (last) fd.append("lastName", last.slice(0, 100));
      fd.append("phone", phone.slice(0, 30));
      if (d.email) fd.append("email", ("" + d.email).slice(0, 254));
      if (d.destination) fd.append("destination", ("" + d.destination).slice(0, 100));
      if (d.visaType) fd.append("visaType", ("" + d.visaType).slice(0, 100));
      if (d.travelDate) fd.append("travelDate", ("" + d.travelDate).slice(0, 40));
      if (d.message) fd.append("message", ("" + d.message).slice(0, 4e3));
      fd.append("formName", ("" + (d.formName || "enquiry")).slice(0, 80));
      fd.append("page", location.pathname.slice(0, 200));
      KEYS.forEach(function (k) { var v = ck(k); if (v) fd.append(k, v); });
      fd.append("landing_path", location.pathname.slice(0, 200));
      if (typeof window.fetch === "function") {
        window.fetch("/bpx.php?t=lead", { method: "POST", body: fd, keepalive: true, credentials: "same-origin" }).catch(function () {
          try { if (navigator.sendBeacon) navigator.sendBeacon("/bpx.php?t=lead", fd); } catch (e) {}
        });
      } else if (navigator.sendBeacon) {
        navigator.sendBeacon("/bpx.php?t=lead", fd);
      }
      return true;
    } catch (e) { return false; }
  };
})();</script>
