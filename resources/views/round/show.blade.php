@extends('layouts.app')

@section('content')
  <div class="round-layout">
    <div class="movie-side">
      <div class="movie-title" id="movie-title">Loading...</div>
      <div class="poster" id="poster-box"></div>
    </div>

    <div>
      <div class="round-top">
        <div class="round-name">Round {{ $number }}</div>
        <div class="round-line"></div>
      </div>

      <div class="guess-grid" id="guess-grid"></div>

      <div class="guess-input-row">
        <input class="input" id="guess-input" maxlength="200" placeholder="Write your guess...">
        <button class="plus-btn" id="btn-add">+</button>
        <button class="icon-btn" id="btn-cancel-edit" style="display:none;">Cancel</button>
      </div>

      <div style="margin-top:18px;">
        <div class="muted" style="margin-bottom:10px;">Your guesses</div>
        <div class="guess-grid" id="my-guess-grid"></div>
      </div>

      <div style="margin-top:22px;">
        <div class="muted" style="margin-bottom:10px;">Rate others</div>

        <div class="panel" style="padding:14px; background:#efebe2;">
          <div id="rate-text" style="font-weight:700; margin-bottom:8px; color:#222;">Loading…</div>
          <div class="muted" id="rate-meta" style="color:#3c3c3c; margin-bottom:10px;"></div>

          <div class="panel-row" style="gap:12px;">
            <select class="input" id="rate-value" style="max-width:220px;">
              <option value="">Score 1–10</option>
              @for ($i=1;$i<=10;$i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
            </select>
            <button class="icon-btn" id="btn-rate">Rate</button>
          </div>

          <div class="muted" id="rate-hint" style="color:#3c3c3c; margin-top:10px;"></div>
        </div>
      </div>

      <div style="margin-top:18px;">
        <button class="lock-btn" id="btn-lock" style="display:none;">Lock Round</button>
        <button class="lock-btn" id="btn-next" style="display:none; margin-left:10px;">Next Round</button>
        <a class="lock-btn" style="display:inline-block; text-align:center; margin-left:10px;"
           id="btn-final" href="/g/{{ $code }}/final">Final Vote</a>
        <a class="lock-btn" style="display:inline-block; text-align:center; margin-left:10px;"
           href="/g/{{ $code }}">Lobby</a>
      </div>

      <div class="muted" id="me-line" style="margin-top:10px;"></div>
      <div class="muted" id="status-line" style="margin-top:6px;"></div>
      <div class="muted" id="progress-line" style="margin-top:6px;"></div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);
  const ROUND = Number(@json($number));

  let ROUND_ID = null;
  let ME = null;

  let editingGuessId = null;

  let queue = [];
  let current = null;

  async function loadMe(){
    const data = await api(`${API}/games/${CODE}`);
    ME = data.me;

    if (!ME || !ME.player_id) {
      location.href = `/join?code=${encodeURIComponent(CODE)}`;
      return;
    }

    q('#me-line').textContent = `You are: ${ME.name ?? ''} (${ME.is_host ? 'Host' : 'Player'})`;

    if (ME.is_host) {
      q('#btn-lock').style.display = 'inline-block';
      q('#btn-next').style.display = 'inline-block';
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

  async function loadRoundId(){
    const data = await api(`${API}/games/${CODE}/rounds`);
    const r = (data.rounds || []).find(x => Number(x.number) === ROUND);
    ROUND_ID = r?.id ?? null;

    q('#status-line').textContent = r?.status ? `Status: ${r.status}` : '';

    return ROUND_ID;
  }

  function esc(s){
    return String(s ?? '').replaceAll('<','&lt;').replaceAll('>','&gt;');
  }

  function renderAllGuesses(guesses){
    const grid = q('#guess-grid');
    grid.innerHTML = '';

    if (!guesses || guesses.length === 0) {
      grid.innerHTML = `<div class="muted">No guesses yet.</div>`;
      return;
    }

    guesses.forEach(g => {
      const card = document.createElement('div');
      card.className = 'guess-card';

      const author = g.author_visible ? (g.author || 'Player') : 'Anonymous';

      card.innerHTML = `
        <div class="guess-author">${esc(author)}</div>
        <div class="guess-text">${esc(g.text)}</div>
      `;
      grid.appendChild(card);
    });
  }

  function renderMyGuesses(guesses){
    const grid = q('#my-guess-grid');
    grid.innerHTML = '';

    const mine = (guesses || []).filter(g => g.is_mine);

    if (mine.length === 0) {
      grid.innerHTML = `<div class="muted">No guesses yet.</div>`;
      return;
    }

    mine.forEach(g => {
      const card = document.createElement('div');
      card.className = 'guess-card';

      card.innerHTML = `
        <div class="guess-author">You</div>
        <div class="guess-text">${esc(g.text)}</div>

        <div style="display:flex; gap:8px; margin-top:10px;">
          <button class="icon-btn" data-edit="${g.id}" style="padding:8px 10px;">Edit</button>
          <button class="icon-btn" data-del="${g.id}" style="padding:8px 10px;">Delete</button>
        </div>
      `;
      grid.appendChild(card);
    });

    qa('[data-edit]').forEach(btn => {
      btn.onclick = () => {
        const id = Number(btn.dataset.edit);
        const g = mine.find(x => x.id === id);
        if (!g) return;

        editingGuessId = id;
        q('#guess-input').value = g.text;
        q('#btn-add').textContent = '✓';
        q('#btn-cancel-edit').style.display = 'inline-block';
        toast('Editing your guess');
      };
    });

    qa('[data-del]').forEach(btn => {
      btn.onclick = async () => {
        const id = Number(btn.dataset.del);
        if (!confirm('Delete this guess?')) return;

        btn.disabled = true;

        try {
          await api(`${API}/guesses/${id}`, { method:'DELETE' });
          toast('Guess deleted');
          await loadGuesses();
          cancelEdit();
        } catch (err) {
          toast(err.message || 'Delete failed', false);
        } finally {
          btn.disabled = false;
        }
      };
    });
  }

  function buildRatingQueue(guesses){
    queue = (guesses || []).filter(g => g.can_rate && !g.is_mine && !g.my_rating);
    current = queue.shift() || null;

    if (!current) {
      q('#rate-text').textContent = 'No guesses to rate.';
      q('#rate-meta').textContent = '';
      q('#rate-hint').textContent = 'Submit at least 1 guess, and wait for all players to submit.';
      q('#btn-rate').disabled = true;
      return;
    }

    q('#btn-rate').disabled = false;
    q('#rate-text').textContent = `"${current.text}"`;
    q('#rate-meta').textContent = current.author_visible ? `by ${current.author}` : '(anonymous)';
    q('#rate-hint').textContent = '';
    q('#rate-value').value = '';
  }

  async function updateProgress(guesses){
    const playersRes = await api(`${API}/games/${CODE}/players`);
    const totalPlayers = (playersRes.players || []).length;

    const submittedPlayers = new Set((guesses || []).map(g => g.player_id).filter(Boolean)).size;
    q('#progress-line').textContent = `Players submitted: ${submittedPlayers} / ${totalPlayers}`;
  }

  async function loadGuesses(){
    await loadRoundId();
    if (!ROUND_ID) return;

    const data = await api(`${API}/rounds/${ROUND_ID}/guesses`);
    const guesses = data.guesses || [];

    renderAllGuesses(guesses);
    renderMyGuesses(guesses);
    buildRatingQueue(guesses);
    await updateProgress(guesses);
  }

  function cancelEdit(){
    editingGuessId = null;
    q('#guess-input').value = '';
    q('#btn-add').textContent = '+';
    q('#btn-cancel-edit').style.display = 'none';
  }

  q('#btn-cancel-edit').onclick = cancelEdit;

  q('#btn-add').onclick = async () => {
    const text = (q('#guess-input').value || '').trim();
    if (!text) return toast('Write a guess first', false);

    q('#btn-add').disabled = true;

    try {
      await loadRoundId();

      if (editingGuessId) {
        await api(`${API}/guesses/${editingGuessId}`, { method:'PUT', body:{ text } });
        toast('Guess updated');
        cancelEdit();
      } else {
        await api(`${API}/rounds/${ROUND_ID}/guesses`, { method:'POST', body:{ text } });
        toast('Guess added');
        q('#guess-input').value = '';
      }

      await loadGuesses();
    } catch (err) {
      toast(err.message || 'Failed', false);
    } finally {
      q('#btn-add').disabled = false;
    }
  };

  q('#btn-rate').onclick = async () => {
    if (!current) return;

    const v = Number(q('#rate-value').value);
    if (!v) return toast('Pick 1–10', false);

    q('#btn-rate').disabled = true;

    try {
      await api(`${API}/guesses/${current.id}/ratings`, { method:'POST', body:{ value: v } });
      toast(`Rated ${v}`);
      await loadGuesses();
    } catch (err) {
      toast(err.message || 'Rating failed', false);
      q('#btn-rate').disabled = false;
    }
  };

  q('#btn-lock').onclick = async () => {
    if (!ME?.is_host) return toast('Host only', false);

    q('#btn-lock').disabled = true;

    try {
      await loadRoundId();
      await api(`${API}/rounds/${ROUND_ID}/lock`, { method:'PUT' });
      toast('Round locked');
      await loadGuesses();
      await loadRoundId();
    } catch (err) {
      toast(err.message || 'Lock failed', false);
    } finally {
      q('#btn-lock').disabled = false;
    }
  };

  q('#btn-next').onclick = async () => {
    if (!ME?.is_host) return toast('Host only', false);

    q('#btn-next').disabled = true;

    try {
      await loadRoundId();
      try { await api(`${API}/rounds/${ROUND_ID}/lock`, { method:'PUT' }); } catch (_) {}

      const created = await api(`${API}/games/${CODE}/rounds`, { method:'POST', body:{} });
      const nextNum = created?.round?.number ?? (ROUND + 1);

      location.href = `/g/${CODE}/rounds/${nextNum}`;
    } catch (err) {
      toast(err.message || 'Next round failed', false);
    } finally {
      q('#btn-next').disabled = false;
    }
  };

  async function redirectIfDifferentOpenRound(){
    const data = await api(`${API}/games/${CODE}/rounds`);
    const open = (data.rounds || []).find(r => r.status === 'open');
    if (open && Number(open.number) !== ROUND) {
      location.href = `/g/${CODE}/rounds/${open.number}`;
    }
  }

  (async () => {
    await loadMe();
    await loadMovie();
    await loadGuesses();

    setInterval(async () => {
      await redirectIfDifferentOpenRound();
      await loadGuesses();
    }, 5000);
  })();
</script>
@endsection
