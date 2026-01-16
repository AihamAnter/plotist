@extends('layouts.app')

@section('content')
  <div class="hero">
    <div class="title">Plotist</div>
    <div class="desc">
      Friends guessing the plot of a movie<br>
      while watching together, start a new<br>
      game or join an existing one
    </div>
  </div>

  <div class="tabs">
    <a class="tab active" href="/create">Create</a>
    <a class="tab" href="/join">Join</a>
  </div>

  <div class="panel">

    <div class="panel-row" style="margin-bottom:14px;">
      <div class="field" style="flex:1;">
        <div class="label">Game Code</div>
        <div class="dark-pill" id="share-code">{{ $code }}</div>
      </div>

      <button type="button" class="icon-btn" id="btn-copy">Copy</button>
    </div>

    <div class="muted" id="movie-line" style="text-align:center;"></div>

    <div class="chips" id="players"></div>

    <div class="center-actions">
      <button type="button" class="big-btn" id="btn-start">Start</button>
    </div>

    <div class="center-actions" style="margin-top:12px;">
      <a class="big-btn" style="text-align:center;" href="/g/{{ $code }}/explorer">Explorer</a>
    </div>

    <div class="muted" id="me-line" style="text-align:center; margin-top:12px;"></div>
  </div>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);
  let ME = null;

  async function loadMe(){
    const data = await api(`${API}/games/${CODE}`);
    ME = data.me;

    if (!ME || !ME.player_id) {
      location.href = `/join?code=${encodeURIComponent(CODE)}`;
      return;
    }

    q('#me-line').textContent = `You are: ${ME.name ?? ''} (${ME.is_host ? 'Host' : 'Player'})`;

    const movie = data.game?.movie;
    if (movie?.title) {
      q('#movie-line').textContent = movie.title;
    }
  }

  async function loadPlayers(){
    const data = await api(`${API}/games/${CODE}/players`);
    const wrap = q('#players');
    wrap.innerHTML = '';

    (data.players || []).forEach(p => {
      const chip = document.createElement('div');
      chip.className = 'chip';
      chip.textContent = p.name;
      wrap.appendChild(chip);
    });
  }

  async function checkOpenRoundAndRedirect(){
    const data = await api(`${API}/games/${CODE}/rounds`);
    const open = (data.rounds || []).find(r => r.status === 'open');
    if (open) location.href = `/g/${CODE}/rounds/${open.number}`;
  }

  q('#btn-copy').onclick = async () => {
    try {
      await navigator.clipboard.writeText(String(CODE));
      toast('Copied!');
    } catch (_) {
      toast('Copy failed', false);
    }
  };

  q('#btn-start').onclick = async () => {
    try {
      const res = await api(`${API}/games/${CODE}/rounds`, { method: 'POST', body: {} });
      const roundNum = res?.round?.number ?? 1;
      location.href = `/g/${CODE}/rounds/${roundNum}`;
    } catch (err) {
      toast(err.message || 'Start failed', false);
    }
  };

  (async () => {
    await loadMe();
    await loadPlayers();
    await checkOpenRoundAndRedirect();

    setInterval(async () => {
      await loadPlayers();
      await checkOpenRoundAndRedirect();
    }, 5000);
  })();
</script>
@endsection
