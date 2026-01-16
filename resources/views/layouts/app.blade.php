<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Plotist</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    :root{
      --bg:#3b3a36;
      --text:#e9e7e0;
      --muted:#5e532a;

      --panel:#d8d4cb;
      --panelText:#2c2c2c;

      --btn:#d7c08b;      
      --btn2:#e5e1d8;     
      --btnText:#2c2c2c;

      --chip:#9fb97a;    
      --chipText:#1f2a18;

      --darkBox:#2f2f2f;
      --darkBoxText:#b9b6ad;

      --danger:#dc2626;
      --success:#16a34a;

      --radius:18px;
    }

    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto;
      background:var(--bg);
      color:var(--text);
    }

    a{ color:inherit; text-decoration:none; }

    .screen{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:40px 16px;
    }

    .stage{
      width:min(980px, 100%);
    }

    .hero{
      text-align:center;
      margin-bottom:22px;
    }
    .hero .title{
      font-size:44px;
      font-weight:600;
      letter-spacing:.3px;
      margin:0 0 10px 0;
    }
    .hero .desc{
      max-width:520px;
      margin:0 auto;
      color:var(--muted);
      font-size:18px;
      line-height:1.45;
    }

    .tabs{
      display:flex;
      gap:16px;
      justify-content:center;
      margin:18px 0 22px;
    }
    .tab{
      min-width:140px;
      padding:10px 16px;
      border-radius:10px;
      border:0;
      cursor:pointer;
      font-size:18px;
      background:var(--btn2);
      color:var(--btnText);
    }
    .tab.active{
      background:var(--btn);
    }

    .panel{
      background:var(--panel);
      color:var(--panelText);
      border-radius:var(--radius);
      padding:26px;
      box-shadow: 0 0 0 1px rgba(0,0,0,.08);
    }

    .panel-row{
      display:flex;
      gap:16px;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
    }

    .field{
      display:flex;
      align-items:center;
      gap:14px;
      width:100%;
    }

    .label{
      width:120px;
      font-size:18px;
      color:#4a4a4a;
    }

    .input{
      width:100%;
      padding:12px 14px;
      border-radius:10px;
      border:1px solid rgba(0,0,0,.25);
      background:#ece9e1;
      outline:none;
      font-size:16px;
      color:#222;
    }

    .dark-pill{
      background:var(--darkBox);
      color:#91b86c;
      padding:12px 14px;
      border-radius:10px;
      width:100%;
      text-align:center;
      font-weight:700;
      letter-spacing:1px;
      border:1px solid rgba(255,255,255,.08);
    }

    .icon-btn{
      border:0;
      border-radius:10px;
      padding:12px 14px;
      background:var(--btn2);
      cursor:pointer;
      color:var(--btnText);
      font-size:16px;
    }

    .chips{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      justify-content:center;
      margin:18px 0;
    }
    .chip{
      background:var(--chip);
      color:var(--chipText);
      padding:8px 14px;
      border-radius:999px;
      font-weight:600;
      font-size:14px;
      border:1px solid rgba(0,0,0,.12);
    }

    .center-actions{
      display:flex;
      justify-content:center;
      margin-top:10px;
    }
    .big-btn{
      min-width:160px;
      padding:12px 18px;
      border-radius:10px;
      border:0;
      cursor:pointer;
      background:var(--btn2);
      color:var(--btnText);
      font-size:18px;
    }

    .muted{
      color:var(--muted);
      font-size:14px;
    }

    .round-layout{
      display:grid;
      grid-template-columns: 280px 1fr;
      gap:26px;
      align-items:start;
    }

    .movie-side{
      text-align:left;
    }

    .movie-title{
      font-size:28px;
      font-weight:500;
      color:var(--btn);
      margin:0 0 14px 0;
      line-height:1.2;
    }

    .poster{
      width:100%;
      border-radius:14px;
      box-shadow: 0 0 0 2px rgba(215,192,139,.8);
      overflow:hidden;
      background:#1f1f1f;
    }

    .round-top{
      display:flex;
      align-items:center;
      gap:14px;
      margin-bottom:16px;
    }

    .round-top .round-name{
      font-size:22px;
      font-weight:600;
      color:#e9e7e0;
    }

    .round-line{
      flex:1;
      height:1px;
      background:rgba(233,231,224,.25);
    }

    .guess-grid{
      display:flex;
      gap:16px;
      overflow-x:auto;
      padding-bottom:10px;
    }

    .guess-card{
      min-width:220px;
      background:#e6e2da;
      color:#2b2b2b;
      border-radius:12px;
      padding:12px;
      border:1px solid rgba(0,0,0,.12);
    }

    .guess-author{
      display:inline-block;
      background:var(--btn);
      color:#2b2b2b;
      padding:6px 12px;
      border-radius:999px;
      font-size:12px;
      font-weight:700;
      margin-bottom:10px;
    }

    .guess-text{
      font-size:13px;
      line-height:1.35;
    }

    .guess-input-row{
      display:flex;
      gap:12px;
      align-items:center;
      margin-top:14px;
      max-width:520px;
    }

    .plus-btn{
      width:52px;
      height:44px;
      border-radius:10px;
      border:0;
      cursor:pointer;
      background:var(--chip);
      color:var(--chipText);
      font-size:22px;
      font-weight:800;
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .lock-btn{
      margin-top:18px;
      background:var(--btn2);
      color:var(--btnText);
      border:0;
      border-radius:10px;
      padding:12px 18px;
      cursor:pointer;
      font-size:16px;
      min-width:160px;
    }

    .toast{
      position:fixed;
      right:16px;
      bottom:16px;
      padding:10px 12px;
      border-radius:10px;
      background:#2e2e2e;
      border:1px solid rgba(255,255,255,.12);
      color:#e9e7e0;
      z-index:9999;
    }
    .toast.ok{ border-color: rgba(22,163,74,.6); }
    .toast.no{ border-color: rgba(220,38,38,.6); }

    button:disabled{ opacity:.7; cursor:not-allowed; }

    @media (max-width: 900px){
      .round-layout{ grid-template-columns: 1fr; }
      .movie-side{ display:flex; gap:16px; align-items:flex-start; }
      .poster{ max-width:220px; }
    }
  </style>
</head>

<body>
  <div class="screen">
    <div class="stage">
      @yield('content')
    </div>
  </div>

  <script>
    const API = '/api/v1';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    async function api(url, opts={}) {
      const o = Object.assign({
        credentials: 'same-origin',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf }
      }, opts);

      if (o.body && typeof o.body !== 'string') o.body = JSON.stringify(o.body);

      const res = await fetch(url, o);

      if (!res.ok) {
        let data = null;
        let msg = '';
        try {
          data = await res.json();
          msg = data?.message || '';
        } catch (_) {
          try { msg = await res.text(); } catch (_) {}
        }
        msg = (msg && String(msg).trim()) ? msg : ('HTTP ' + res.status);
        const err = new Error(msg);
        err.status = res.status;
        err.data = data;
        throw err;
      }

      const ct = res.headers.get('content-type') || '';
      return ct.includes('application/json') ? res.json() : res.text();
    }

    function q(sel, root=document){ return root.querySelector(sel); }
    function qa(sel, root=document){ return Array.from(root.querySelectorAll(sel)); }

    function toast(msg, ok=true){
      const el = document.createElement('div');
      el.className = 'toast ' + (ok ? 'ok' : 'no');
      el.textContent = msg;
      document.body.appendChild(el);
      setTimeout(()=>el.remove(), 2200);
    }
  </script>

  @yield('scripts')
</body>
</html>
