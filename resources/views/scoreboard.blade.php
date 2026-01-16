@extends('layouts.app')

@section('content')
  <div class="hero">
    <div class="title">Plotist</div>
    <div class="desc">
      Scoreboard
    </div>
  </div>

  <div class="panel" style="margin-top:18px;">
    <div class="panel-row" style="margin-bottom:14px;">
      <div class="field" style="flex:1;">
        <div class="label">Game Code</div>
        <div class="dark-pill" id="code-pill">{{ $code }}</div>
      </div>

      <a class="icon-btn" href="/g/{{ $code }}">Lobby</a>
    </div>

    <div class="panel-row" style="margin-bottom:14px;">
      <div class="muted" id="me-line">Loading...</div>
    </div>

    <div id="movie-line" class="muted" style="text-align:center; margin-bottom:14px;"></div>

    <div class="chips" id="players"></div>

    <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-top:12px;">
      <a class="big-btn" style="min-width:180px; text-align:center;" href="/g/{{ $code }}/final">Final Vote</a>
      <a class="big-btn" style="min-width:180px; text-align:center;" href="/g/{{ $code }}/explorer">Explorer</a>
      <button class="big-btn" style="min-width:180px;" id="btn-refresh">Refresh</button>
    </div>

    <div class="muted" id="meta" style="text-align:center; margin-top:10px;"></div>
  </div>

  <style>
    .chip.score{
      display:flex;
      gap:10px;
      align-items:center;
      justify-content:center;
      min-width:160px;
    }
    .chip .score-num{
      font-weight:900;
      padding-left:6px;
    }
  </style>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);

  async function loadMeAndMovie(){
    const data = await api(`${API}/games/${CODE}`);
    const me = data.me;

    if (!me || !me.player_id) {
      location.href = `/join?code=${encodeURIComponent(CODE)}`;
      return;
    }

    q('#me-line').textContent = `You are: ${me.name ?? ''} (${me.is_host ? 'Host' : 'Player'})`;

    const movie = data.game?.movie;
    if (movie?.title) {
      q('#movie-line').textContent = movie.title;
    } else {
      q('#movie-line').textContent = '';
    }
  }

  async function loadScoreboard(){
    try {
      const data = await api(`${API}/games/${CODE}/scoreboard`);
      const wrap = q('#players');
      wrap.innerHTML = '';

      const players = data.players || [];

      players.forEach(p => {
        const chip = document.createElement('div');
        chip.className = 'chip score';
        chip.innerHTML = `
          <span>${p.name}${p.is_host ? ' ⭐' : ''}</span>
          <span class="score-num">${Number(p.score ?? 0).toFixed(2)}</span>
        `;
        wrap.appendChild(chip);
      });

      q('#meta').textContent = `Players: ${players.length}`;
    } catch (err) {
      console.log(err);
      toast(err.message || 'Scoreboard load failed', false);
    }
  }

  q('#btn-refresh').onclick = loadScoreboard;

  (async () => {
    await loadMeAndMovie();
    await loadScoreboard();

    setInterval(loadScoreboard, 6000);
  })();
</script>
@endsection
