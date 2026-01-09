@extends('layouts.app')

@section('subtitle', 'Scoreboard')

@section('content')
  <div class="card">
    <h2 style="margin:0 0 8px 0;">Final Scores</h2>
    <div class="hr"></div>
    <div id="scores" class="list"></div>
  </div>
@endsection

@section('scripts')
<script>
  const CODE = @json($code);

  async function loadScores(){
    const data = await api(`${API}/games/${CODE}/scoreboard`);
    const list = q('#scores'); list.innerHTML = '';
    (data.players || []).sort((a,b)=> (b.score??0) - (a.score??0)).forEach((p,i) => {
      const el = document.createElement('div'); el.className='list-item';
      el.innerHTML = `<div class="row" style="justify-content:space-between">
        <div><strong>#${i+1}</strong> ${p.name}</div>
        <div class="muted">${Number(p.score ?? 0).toFixed(2)}</div>
      </div>`;
      list.appendChild(el);
    });
  }

  await loadScores();
</script>
@endsection
