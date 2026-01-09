@extends('layouts.app')

@section('subtitle', 'Lobby')

@section('content')
  <div class="grid" style="grid-template-columns: 2fr 1fr;">
    <div class="card">
      <h2 style="margin:0 0 8px 0;">Players</h2>
      <p class="muted">Share code: <strong>{{ $code }}</strong></p>
      <div class="hr"></div>
      <div id="players" class="list"></div>
    </div>

    <div class="card">
      <h3 style="margin:0 0 8px 0;">Host Controls</h3>
      <p class="muted">Start Round 1 when ready.</p>
      <div class="hr"></div>
      <div class="grid" style="grid-template-columns: 1fr; gap:10px;">
        <button class="btn primary" id="btn-start">Start Round</button>
        <a class="btn" href="/g/{{ $code }}/final">Open Final Voting</a>
        <a class="btn" href="/g/{{ $code }}/scoreboard">Scoreboard</a>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);

  async function loadPlayers(){
    const data = await api(`${API}/games/${CODE}/players`);
    const list = q('#players'); list.innerHTML = '';
    (data.players || []).forEach(p => {
      const el = document.createElement('div'); el.className='list-item';
      el.innerHTML = `<div class="row" style="justify-content:space-between">
        <div><strong>${p.name}</strong> <span class="pill">${p.is_host ? 'Host' : 'Player'}</span></div>
        <div class="muted">Score: ${Number(p.score ?? 0).toFixed(2)}</div>
      </div>`;
      list.appendChild(el);
    });
  }

  q('#btn-start').onclick = async () => {
    await api(`${API}/games/${CODE}/rounds`, { method: 'POST', body: {} });
    location.href = `/g/${CODE}/rounds/1`; // backend can redirect to actual latest number
  };

  await loadPlayers();

  // Later: subscribe to Echo channel "game.{code}" to live-update player list
  // window.Echo?.channel(`game.${CODE}`).listen('PlayerJoined', loadPlayers);
</script>
@endsection
