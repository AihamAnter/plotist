@extends('layouts.app')

@section('subtitle', 'Create Game')

@section('content')
  <div class="grid" style="grid-template-columns: 1fr;">
    <div class="card">
      <h2 style="margin:0 0 8px 0;">Create Game</h2>
      <p class="muted">Pick a movie from TMDb, set host name and password. A share code will be generated.</p>
      <div class="hr"></div>

      <form id="create-form" class="grid" style="grid-template-columns: 1fr; gap:12px;">
        <div class="grid" style="grid-template-columns: 1fr 1fr; gap:12px;">
          <div>
            <label>Host name</label>
            <input name="host_name" placeholder="e.g. Ali" required>
          </div>
          <div>
            <label>Host password</label>
            <input name="host_password" type="password" placeholder="min 4 chars" required>
          </div>
        </div>

        <div class="row">
          <div style="flex:1">
            <label>Search movie (TMDb)</label>
            <input id="movie-q" placeholder="e.g. Inception">
          </div>
          <button type="button" class="btn" id="btn-search">Search</button>
        </div>

        <div id="results" class="list"></div>

        <div class="right">
          <button class="btn primary" type="submit">Create Game</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  let picked = null;

  q('#btn-search').addEventListener('click', async () => {
    const qv = q('#movie-q').value.trim();
    if (!qv) return;
    const data = await api(`${API}/movies/search?q=${encodeURIComponent(qv)}`);
    const list = q('#results'); list.innerHTML = '';
    (data.results || []).slice(0,8).forEach(m => {
      const item = document.createElement('div'); item.className='list-item';
      item.innerHTML = `
        <div class="row" style="justify-content:space-between">
          <div>
            <div><strong>${m.title}</strong> <span class="muted">(${m.release_year ?? ''})</span></div>
            <div class="muted">TMDb: ${m.vote_average ?? 'n/a'}</div>
          </div>
          <button class="btn" data-pick="${m.id}">Pick</button>
        </div>`;
      list.appendChild(item);
    });

    qa('[data-pick]').forEach(b => b.onclick = () => {
      picked = (data.results || []).find(x => x.id == b.dataset.pick);
      toast(`Picked: ${picked?.title}`);
    });
  });

  q('#create-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = Object.fromEntries(new FormData(e.target).entries());
    if (!picked) { toast('Pick a movie first', false); return; }

    // 1) Create game
    const created = await api(`${API}/games`, {
      method: 'POST',
      body: {
        host_name: fd.host_name,
        host_password: fd.host_password,
      }
    });

    // 2) Attach movie
    await api(`${API}/movies/pick`, {
      method: 'POST',
      body: {
        code: created.code,
        tmdb_id: picked.id,
      }
    });

    // Go to lobby
    location.href = `/g/${created.code}`;
  });
</script>
@endsection
