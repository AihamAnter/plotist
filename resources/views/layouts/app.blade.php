<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Movie Guess Game</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    :root { --bg:#0f172a; --card:#111827; --muted:#cbd5e1; --text:#e5e7eb; --accent:#38bdf8; }
    * { box-sizing:border-box; }
    body { margin:0; font-family: ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto; background:var(--bg); color:var(--text); }
    a { color: var(--accent); text-decoration: none; }
    .container { max-width: 1000px; margin: 0 auto; padding: 24px; }
    .nav { display:flex; align-items:center; justify-content:space-between; padding:16px 24px; background:#0b1222; border-bottom:1px solid #1f2937; }
    .brand { font-weight:700; letter-spacing:.3px; }
    .card { background:var(--card); border:1px solid #1f2937; border-radius:16px; padding:20px; }
    .grid { display:grid; gap:16px; }
    .row { display:flex; gap:12px; align-items:center; }
    .btn { padding:10px 14px; border-radius:10px; border:1px solid #1f2937; background:#0b1222; color:var(--text); cursor:pointer; }
    .btn.primary { background: var(--accent); color:#0b1222; border-color: transparent; font-weight:600; }
    .btn.ghost { background:transparent; }
    input, select { width:100%; padding:10px 12px; border-radius:10px; border:1px solid #374151; background:#0b1222; color:var(--text); }
    label { font-size:14px; color:var(--muted); }
    .muted { color: var(--muted); font-size: 14px; }
    .list { display:grid; gap:10px; }
    .list-item { background:#0b1222; border:1px solid #1f2937; border-radius:10px; padding:12px; }
    .hr { height:1px; background:#1f2937; margin:16px 0; }
    .right { text-align:right; }
    .pill { font-size:12px; padding:4px 8px; border-radius:999px; border:1px solid #1f2937; }
    .warn { color:#f87171; }
    .success { color:#34d399; }
  </style>
</head>
<body>
  <div class="nav">
    <div class="brand">🎬 Movie Guess Game</div>
    <div class="muted">@yield('subtitle')</div>
  </div>
  <div class="container">
    @yield('content')
  </div>

  <script>
    const API = '/api/v1';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    async function api(url, opts={}) {
      const o = Object.assign({ headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf } }, opts);
      if (o.body && typeof o.body !== 'string') o.body = JSON.stringify(o.body);
      const res = await fetch(url, o);
      if (!res.ok) {
        const t = await res.text();
        throw new Error(t || ('HTTP ' + res.status));
      }
      const ct = res.headers.get('content-type') || '';
      return ct.includes('application/json') ? res.json() : res.text();
    }

    function q(sel, root=document){ return root.querySelector(sel); }
    function qa(sel, root=document){ return Array.from(root.querySelectorAll(sel)); }
    function params() { return new URLSearchParams(location.search); }

    function toast(msg, ok=true){
      const el = document.createElement('div');
      el.textContent = msg;
      el.style.cssText = 'position:fixed;right:16px;bottom:16px;padding:10px 12px;border-radius:10px;background:#0b1222;border:1px solid #1f2937;color:'+ (ok?'#34d399':'#f87171') +';z-index:9999';
      document.body.appendChild(el); setTimeout(()=>el.remove(), 2200);
    }
  </script>

  {{-- Real-time (wire up later when you enable Echo) --}}
  {{-- <script src="https://unpkg.com/laravel-echo/dist/echo.iife.js"></script>
  <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
  <script>
  </script> --}}
  @yield('scripts')
</body>
</html>
