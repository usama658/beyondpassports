{{-- Lead attribution — Google Ads gclid/UTM capture + WhatsApp CRM ref/beacon (Beyond Passports).
     CRM dev's canonical "complete" script, implemented verbatim. Injected site-wide (layout +
     static-country route + LpVariantController). Beacon -> go.beyondpassports.co.uk (visacrm-production).
     NOTE: var FORM still holds the PASTE-FORM-ID-HERE placeholder — the direct form->CRM post
     self-disables until a real CRM Web Form id is pasted. This version does NOT define window.bpUtm()
     or the agent-readable [ref:] (external-CRM-only design, per owner instruction). --}}
<script>
(function() {
  if (window.__bpAttr) return;
  window.__bpAttr = true;
  var CRM = "https://go.beyondpassports.co.uk";
  var TRACK = CRM + "/api/track/wa-click?brand=beyond-passports";
  var FORM = CRM + "/api/forms/contact";
  var PREFIX = "BP";
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
    KEYS.forEach(function(k) {
      var v = clean(qs.get(k) || "");
      if (v) setCookie(k, v);
    });
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
      if (!navigator.sendBeacon) return;
      var b = {
        ref: ref,
        page: location.pathname.slice(0, 200)
      };
      KEYS.forEach(function(k) {
        var v = ck(k);
        if (v) b[k] = v;
      });
      navigator.sendBeacon(TRACK, new Blob([ JSON.stringify(b) ], {
        type: "text/plain"
      }));
    } catch (e) {}
  }
  function isWa(url) {
    return typeof url === "string" && /^https:\/\/(wa\.me|api\.whatsapp\.com)\//i.test(url);
  }
  window.bpWaUrl = function(url) {
    try {
      if (!isWa(url)) return url;
      var u = new URL(url), t = u.searchParams.get("text") || "";
      if (t.indexOf("(Ref: ") > -1) return url;
      var ref = newRef();
      beacon(ref);
      u.searchParams.set("text", (t ? t + " " : "") + "(Ref: " + ref + ")");
      return u.toString();
    } catch (e) {
      return url;
    }
  };
  window.bpWa = function(text) {
    text = text || "";
    if (text.indexOf("(Ref: ") > -1) return text;
    var ref = newRef();
    beacon(ref);
    return (text ? text + " " : "") + "(Ref: " + ref + ")";
  };
  var _open = window.open;
  window.open = function(u) {
    try {
      if (typeof u === "string") arguments[0] = window.bpWaUrl(u);
    } catch (e) {}
    return _open.apply(this, arguments);
  };
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
  function val(f, n) {
    var el = f.querySelector('[name="' + n + '"]');
    return el && typeof el.value === "string" ? el.value.trim() : "";
  }
  function sendForm(f) {
    try {
      if (!navigator.sendBeacon || FORM.indexOf("PASTE-") > -1) return;
      if (f.getAttribute("data-crm-sent")) return;
      var phone = val(f, "phone");
      var name = val(f, "name");
      if (!phone || !name) return;
      var consent = f.querySelector('[name="consent"]');
      if (consent && consent.type === "checkbox" && !consent.checked) return;
      var dial = val(f, "phone_dialcode") || "+44";
      if (phone.charAt(0) !== "+") phone = dial + " " + phone.replace(/^0+/, "");
      var parts = name.split(/\s+/);
      var message = val(f, "message");
      var time = val(f, "time");
      var fd = new FormData;
      fd.append("firstName", parts[0].slice(0, 100));
      fd.append("lastName", parts.slice(1).join(" ").slice(0, 100));
      fd.append("phone", phone.slice(0, 30));
      var email = val(f, "email");
      if (email) fd.append("email", email.slice(0, 254));
      fd.append("notes", ((message ? message : "") + (time ? "\nBest time to call: " + time : "") + "\nForm: " + (f.id || "enquiry") + " · " + location.pathname).trim().slice(0, 4e3));
      KEYS.forEach(function(k) {
        var v = ck(k);
        if (v) fd.append(k, v);
      });
      fd.append("landing_path", location.pathname.slice(0, 200));
      if (navigator.sendBeacon(FORM, fd)) f.setAttribute("data-crm-sent", "1");
    } catch (e) {}
  }
  document.addEventListener("submit", function(e) {
    var f = e.target;
    if (f && f.tagName === "FORM" && f.querySelector('[name="phone"]')) sendForm(f);
  }, true);
  document.addEventListener("input", function(e) {
    var f = e.target && e.target.form;
    if (f) f.removeAttribute("data-crm-sent");
  }, true);
  function apply() {
    wireAnchors();
    fillFields();
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", apply); else apply();
  if (window.MutationObserver) {
    var to = null;
    new MutationObserver(function() {
      clearTimeout(to);
      to = setTimeout(apply, 200);
    }).observe(document.documentElement, {
      childList: true,
      subtree: true
    });
  }
  document.addEventListener("submit", fillFields, true);
})();</script>
