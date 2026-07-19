{{-- Reusable disclaimer strip (S1 · teal-edge icon card). Drop under appointment /
     apply / checker sections to make the "we're independent, not the government"
     point at the point of action. Self-contained styling via @once; resolved-hex
     so it renders on both light pages and the dark lp-bold page. --}}
<div class="wrap" style="padding-top:6px;padding-bottom:6px">
  <div class="disc-strip" role="note">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 3 6v6c0 5 3.8 8.5 9 10 5.2-1.5 9-5 9-10V6l-9-4Zm-1 5h2v6h-2V7Zm0 8h2v2h-2v-2Z"/></svg>
    <p><b>Beyond Passports is an independent consultancy, not a government or embassy service.</b> We do not issue visas or decide outcomes. Every decision rests with the relevant authorities. We help you prepare and submit your own application correctly.</p>
  </div>
</div>
@once
<style>
  .disc-strip{display:flex;gap:12px;align-items:flex-start;background:#eef4f6;border:1px solid #d6e5e9;
    border-left:3px solid #155E7A;border-radius:12px;padding:13px 16px;font-family:"Outfit",system-ui,sans-serif}
  .disc-strip svg{width:18px;height:18px;flex:0 0 auto;fill:#155E7A;margin-top:1px}
  .disc-strip p{margin:0;font-size:12.5px;line-height:1.55;color:#3d4b55}
  .disc-strip p b{color:#16222E;font-weight:700}
</style>
@endonce
