@extends('layouts.app')

@section('content')
  <div class="hero">
    <div class="title">Plotist</div>
    <div class="desc">Explorer (Filter + Sort)</div>
  </div>

  <div class="panel">
    <div class="panel-row" style="margin-bottom:14px;">
      <div class="field" style="flex:1;">
        <div class="label">Game</div>
        <div class="dark-pill">{{ $code }}</div>
      </div>
      <a class="icon-btn" href="/g/{{ $code }}">Lobby</a>
    </div>

    <div class="panel-row" style="margin-bottom:14px;">
      <div class="field" style="flex:1;">
        <div class="label">Round</div>
        <select class="input" id="round"></select>
      </div>

      <div class="field" style="flex:1;">
        <div class="label">Sort</div>
        <select class="input" id="sort">
          <option value="created_at">Created</option>
          <option value="avg_rating">Avg Rating</option>
        </select>
      </div>

      <div class="field" style="flex:1;">
        <div class="label">Dir</div>
        <select class="input" id="dir">
          <option value="asc">ASC</option>
          <option value="desc">DESC</option>
        </select>
      </div>
    </div>

    <div class="panel-row" style="margin-bottom:14px;">
      <div class="field" style="flex:1;">
        <div class="label">Mine</div>
        <select class="input" id="mine">
          <option value="0">All</option>
          <option value="1">Only mine</option>
        </select>
      </div>

      <button class="icon-btn" id="btn-load">Load</button>
    </div>

    <div class="muted" id="meta" style="text-align:center; margin-bottom:12px;"></div>

    @include('partials.guess-card-template')

    <div class="guess-grid" id="grid"></div>
  </div>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);
  let rounds = [];

  function esc(s){
    return String(s ?? '').replaceAll('<','&lt;').replaceAll('>','&gt;');
  }

  function makeGuessCard({badge, text, meta}){
    const tpl = q('#tpl-guess-card');
    const node = tpl.content.firstElementChild.cloneNode(true);

    node.querySelector('[data-author]').textContent = badge ?? '';
    node.querySelector('[data-text]').textContent = text ?? '';
    node.querySelector('[data-meta]').textContent = meta ?? '';

    // explorer has no actions
    node.querySelector('[data-actions]').innerHTML = '';

    return node;
  }

  async function loadRounds(){
    const res = await api(`${API}/games/${CODE}/rounds`);
    rounds = res.rounds || [];

    const sel = q('#round');
    sel.innerHTML = '';

    if (rounds.length === 0) {
      sel.innerHTML = `<option value="">No rounds</option>`;
      return;
    }

    rounds.forEach(r => {
      const opt = document.createElement('option');
      opt.value = r.id;
      opt.textContent = `Round ${r.number} (${r.status})`;
      sel.appendChild(opt);
    });
  }

  async function loadGuesses(){
    const roundId = q('#round').value;
    if (!roundId) return;

    const sort = q('#sort').value;
    const dir  = q('#dir').value;
    const mine = q('#mine').value;

    const url = `${API}/rounds/${roundId}/guesses?sort=${encodeURIComponent(sort)}&dir=${encodeURIComponent(dir)}&mine=${encodeURIComponent(mine)}`;

    const res = await api(url);

    const guesses = res.guesses || [];
    q('#meta').textContent = `Guesses: ${guesses.length}`;

    const grid = q('#grid');
    grid.innerHTML = '';

    if (guesses.length === 0) {
      grid.innerHTML = `<div class="muted">No guesses found.</div>`;
      return;
    }

    guesses.forEach(g => {
      const author = g.author_visible ? (g.author || 'Player') : 'Anonymous';
      const avg = Number(g.avg_rating ?? 0).toFixed(2);

      const card = makeGuessCard({
        badge: author,
        text: `"${esc(g.text)}"`,
        meta: `avg: ${avg}`,
      });

      grid.appendChild(card);
    });
  }

  q('#btn-load').onclick = loadGuesses;

  (async () => {
    await loadRounds();
    await loadGuesses();
  })();
</script>
@endsection
