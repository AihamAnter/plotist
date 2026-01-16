@extends('layouts.app')

@section('content')
  <div class="round-layout">
    <div class="movie-side">
      <div class="movie-title" id="movie-title">Loading...</div>
      <div class="poster" id="poster-box"></div>
    </div>

    <div>
      <div class="round-top">
        <div class="round-name">Final Voting</div>
        <div class="round-line"></div>
      </div>

      <div class="muted" id="me-line" style="margin-bottom:14px;">Loading…</div>

      <div id="meta" class="muted" style="margin-bottom:10px;"></div>


      @include('partials.guess-card-template')

      <div class="guess-grid" id="guesses"></div>

      <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap;">
        <button type="button" class="lock-btn" id="btn-refresh">Refresh</button>
        <button type="button" class="lock-btn" id="btn-finish" style="display:none;">Compute Scores</button>

        <a class="lock-btn" style="display:inline-block; text-align:center;" href="/g/{{ $code }}/scoreboard">Scoreboard</a>
        <a class="lock-btn" style="display:inline-block; text-align:center;" href="/g/{{ $code }}/explorer">Explorer</a>
        <a class="lock-btn" style="display:inline-block; text-align:center;" href="/g/{{ $code }}">Lobby</a>
      </div>
    </div>
  </div>

  <style>
    .vote-btn{
      border:0;
      border-radius:10px;
      padding:8px 12px;
      cursor:pointer;
      font-weight:700;
      background:#e5e1d8;
      color:#2c2c2c;
    }
    .vote-btn:disabled{ opacity:.75; cursor:not-allowed; }

    .vote-correct{
      background:#16a34a !important;
      color:#0b1222 !important;
    }
    .vote-incorrect{
      background:#dc2626 !important;
      color:#0b1222 !important;
    }
  </style>
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

    if (ME.is_host) {
      q('#btn-finish').style.display = 'inline-block';
    }
  }

  async function loadMovie(){
    const data = await api(`${API}/games/${CODE}`);
    const m = data.game?.movie;

    q('#movie-title').textContent = m?.title ? m.title : 'Movie';
    q('#poster-box').innerHTML = m?.poster_url
      ? `<img src="${m.poster_url}" style="width:100%; display:block;">`
      : `<div class="muted" style="padding:16px;">No poster</div>`;
  }

  function esc(s){
    return String(s ?? '').replaceAll('<','&lt;').replaceAll('>','&gt;');
  }

  function makeGuessCard({badge, text, meta, actionsHtml}){
    const tpl = q('#tpl-guess-card');
    const node = tpl.content.firstElementChild.cloneNode(true);

    node.querySelector('[data-author]').textContent = badge ?? '';
    node.querySelector('[data-text]').textContent = text ?? '';
    node.querySelector('[data-meta]').textContent = meta ?? '';

    const actions = node.querySelector('[data-actions]');
    actions.innerHTML = actionsHtml ?? '';

    return node;
  }

  async function loadGuesses(){
    try {
      q('#meta').textContent = 'Loading...';

      const roundsRes = await api(`${API}/games/${CODE}/rounds`);
      const rounds = (roundsRes.rounds || []);

      const guessesRes = [];
      for (const r of rounds) {
        guessesRes.push(await api(`${API}/rounds/${r.id}/guesses`));
      }

      const all = [];
      guessesRes.forEach((g, idx) => {
        const round = rounds[idx];
        (g.guesses || []).forEach(x => {
          all.push({ ...x, round_number: round.number });
        });
      });

      const list = q('#guesses');
      list.innerHTML = '';

      if (all.length === 0) {
        q('#meta').textContent = 'No guesses found yet.';
        return;
      }

      all.forEach(g => {
        const author = g.author_visible ? (g.author || 'Player') : 'Anonymous';
        const avg = Number(g.avg_rating ?? 0).toFixed(2);

        const card = makeGuessCard({
          badge: `Round ${g.round_number}`,
          text: `"${esc(g.text)}"`,
          meta: `${author} · avg ${avg}`,
          actionsHtml: `
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
              <button class="vote-btn" data-vote="correct" data-id="${g.id}">Correct</button>
              <button class="vote-btn" data-vote="incorrect" data-id="${g.id}">Incorrect</button>
            </div>
          `
        });

        list.appendChild(card);
      });

      qa('[data-vote]').forEach(b => {
        b.onclick = async () => {
          const guessId = b.dataset.id;
          const decision = b.dataset.vote;

          const wrap = b.closest('.guess-card');
          const btns = wrap?.querySelectorAll('[data-vote]') || [];

          btns.forEach(x => x.disabled = true);

          try {
            await api(`${API}/guesses/${guessId}/final-votes`, { method:'POST', body:{ decision } });

            btns.forEach(x => x.classList.remove('vote-correct', 'vote-incorrect'));

            if (decision === 'correct') b.classList.add('vote-correct');
            if (decision === 'incorrect') b.classList.add('vote-incorrect');

            toast('Voted: ' + decision);
          } catch (err) {
            console.log(err);
            toast(err.message || 'Vote failed', false);
            btns.forEach(x => x.disabled = false);
          }
        };
      });

      q('#meta').textContent = `Loaded ${all.length} guesses.`;
    } catch (err) {
      console.log(err);
      q('#meta').textContent = 'Failed to load guesses.';
      toast(err.message || 'Network error', false);
    }
  }

  q('#btn-refresh').onclick = loadGuesses;

  q('#btn-finish').onclick = async () => {
    try {
      await api(`${API}/games/${CODE}/compute-scores`, { method:'POST' });
      location.href = `/g/${CODE}/scoreboard`;
    } catch (err) {
      console.log(err);
      toast(err.message || 'Compute failed', false);
    }
  };

  (async () => {
    await loadMe();
    await loadMovie();
    await loadGuesses();
  })();
</script>
@endsection
