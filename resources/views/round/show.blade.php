@extends('layouts.app')

@section('subtitle', 'Round')

@section('content')
  <div class="grid" style="grid-template-columns: 2fr 1fr;">
    <div class="card">
      <h2 style="margin:0 0 8px 0;">Round <span id="rnum">{{ $number }}</span></h2>
      <p class="muted">Write your guess, then rate others one by one.</p>
      <div class="hr"></div>

      <form id="guess-form" class="grid" style="grid-template-columns: 1fr; gap:12px;">
        <div>
          <label>Your guess</label>
          <input name="text" maxlength="200" placeholder="Type your guess (max 200 chars)" required>
        </div>
        <div class="right">
          <button class="btn primary" type="submit">Submit Guess</button>
        </div>
      </form>

      <div class="hr"></div>
      <h3 style="margin:0 0 8px 0;">Rate Guesses</h3>
      <div id="rate-box" class="card" style="background:#0b1222;">
        <div id="rate-text" style="margin-bottom:8px;">Loading…</div>
        <div class="row">
          <select id="rate-value">
            <option value="">Score 1–10</option>
            @for ($i=1;$i<=10;$i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
          </select>
          <button class="btn primary" id="btn-rate">Rate</button>
        </div>
        <div class="muted" id="rate-meta" style="margin-top:8px;"></div>
      </div>
    </div>

    <div class="card">
      <h3 style="margin:0 0 8px 0;">Host Controls</h3>
      <div class="grid" style="grid-template-columns: 1fr; gap:10px;">
        <button class="btn" id="btn-lock">Lock Round</button>
        <a class="btn" href="/g/{{ $code }}/rounds/{{ $number+1 }}">Next Round</a>
      </div>
      <div class="hr"></div>
      <div class="muted">Status: <span id="status">open</span></div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);
  const ROUND = Number(@json($number));
  let queue = []; // guesses to rate (excluding mine), one at a time
  let current = null;

  async function submitGuess(text){
    // Assumes backend infers player from cookie
    // You may require round id instead of number; adjust if needed.
    const roundInfo = await api(`${API}/games/${CODE}/rounds`); // list with ids
    const round = (roundInfo.rounds || []).find(r => r.number == ROUND);
    await api(`${API}/rounds/${round.id}/guesses`, { method:'POST', body:{ text } });
    toast('Guess submitted');
    await loadQueue();
  }

  async function loadQueue(){
    const roundInfo = await api(`${API}/games/${CODE}/rounds`);
    const round = (roundInfo.rounds || []).find(r => r.number == ROUND);
    const data = await api(`${API}/rounds/${round.id}/guesses`);
    // server should send can_rate + my_rating + is_mine + author_visible (per spec)
    queue = (data.guesses || []).filter(g => g.can_rate && !g.is_mine && !g.my_rating);
    nextItem();
  }

  function nextItem(){
    current = queue.shift() || null;
    if (!current){
      q('#rate-text').textContent = 'No unrated guesses left.';
      q('#rate-meta').textContent = '';
      return;
    }
    q('#rate-text').textContent = `"${current.text}"`;
    q('#rate-meta').textContent = current.author_visible ? `by ${current.author}` : '(anonymous)';
    q('#rate-value').value = '';
  }

  q('#guess-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = Object.fromEntries(new FormData(e.target).entries());
    await submitGuess(fd.text);
    e.target.reset();
  });

  q('#btn-rate').onclick = async () => {
    if (!current) return;
    const v = Number(q('#rate-value').value);
    if (!v) { toast('Pick a score 1–10', false); return; }
    const res = await api(`${API}/guesses/${current.id}/ratings`, { method:'POST', body:{ value: v } });
    // server response should reveal author to rater for this guess
    toast(`Rated ${v}`);
    nextItem();
  };

  q('#btn-lock').onclick = async () => {
    const roundInfo = await api(`${API}/games/${CODE}/rounds`);
    const round = (roundInfo.rounds || []).find(r => r.number == ROUND);
    await api(`${API}/rounds/${round.id}/lock`, { method:'PUT' });
    q('#status').textContent = 'locked';
    toast('Round locked');
  };

  await loadQueue();

  // Later: subscribe to round.{id} to live-update queue
  // window.Echo?.channel(`round.${round.id}`).listen('GuessCreated', loadQueue);
</script>
@endsection
