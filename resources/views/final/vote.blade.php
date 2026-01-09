@extends('layouts.app')

@section('subtitle', 'Final Voting')

@section('content')
  <div class="card">
    <h2 style="margin:0 0 8px 0;">Final Voting</h2>
    <p class="muted">Vote each guess as correct or incorrect.</p>
    <div class="hr"></div>
    <div id="guesses" class="list"></div>
    <div class="hr"></div>
    <div class="row right">
      <button class="btn primary" id="btn-finish">Compute Scores</button>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);

  async function loadGuesses(){
    // Flatten all rounds’ guesses for final voting
    const rounds = await api(`${API}/games/${CODE}/rounds`);
    const all = [];
    for (const r of (rounds.rounds||[])) {
      const g = await api(`${API}/rounds/${r.id}/guesses`);
      (g.guesses||[]).forEach(x => all.push(x));
    }
    const list = q('#guesses'); list.innerHTML = '';
    all.forEach(g => {
      const el = document.createElement('div'); el.className='list-item';
      el.innerHTML = `
        <div class="row" style="justify-content:space-between">
          <div>
            <div>"${g.text}"</div>
            <div class="muted">by ${g.author ?? 'unknown'} · avg ${Number(g.avg_rating ?? 0).toFixed(2)}</div>
          </div>
          <div class="row">
            <button class="btn" data-vote="correct" data-id="${g.id}">Correct</button>
            <button class="btn" data-vote="incorrect" data-id="${g.id}">Incorrect</button>
          </div>
        </div>`;
      list.appendChild(el);
    });
    qa('[data-vote]').forEach(b => b.onclick = () => vote(b.dataset.id, b.dataset.vote));
  }

  async function vote(guessId, decision){
    await api(`${API}/guesses/${guessId}/final-votes`, { method:'POST', body:{ decision } });
    toast('Voted: ' + decision);
  }

  q('#btn-finish').onclick = async () => {
    await api(`${API}/games/${CODE}/compute-scores`, { method:'POST' });
    location.href = `/g/${CODE}/scoreboard`;
  };

  await loadGuesses();
</script>
@endsection
