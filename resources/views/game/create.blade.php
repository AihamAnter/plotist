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
    <a class="tab active" href="/">Create</a>
    <a class="tab" href="/join">Join</a>
  </div>

  <div class="panel">
    <div class="panel-row" style="margin-bottom:14px;">
      <div class="field" style="flex:1;">
        <div class="label">Host name</div>
        <input class="input" id="host_name" placeholder="e.g. Ali">
      </div>
      <div class="field" style="flex:1;">
        <div class="label">Password</div>
        <input class="input" id="host_password" type="password" placeholder="min 4 chars">
      </div>
    </div>

    <div class="panel-row">
      <div class="field" style="flex:1;">
        <div class="label">Find A Movie</div>
        <input class="input" id="movie-q" placeholder="Search movie title...">
      </div>
      <button type="button" class="icon-btn" id="btn-search">🔍</button>
    </div>

    <div style="margin-top:14px;" id="picked" hidden></div>
    <div style="margin-top:14px;" id="results"></div>

    <div class="center-actions" style="margin-top:18px;">
      <button type="button" class="big-btn" id="btn-create">Create</button>
    </div>

    <div class="muted" id="status" style="text-align:center; margin-top:10px;"></div>
  </div>
@endsection

@section('scripts')
<script>
  let picked = null;

  function showPicked(){
    const box = q('#picked');
    if (!picked) {
      box.hidden = true;
      box.innerHTML = '';
      return;
    }

    box.hidden = false;
    box.innerHTML = `
      <div class="panel" style="padding:14px; background:#efebe2;">
        <div style="font-weight:700;">Picked:</div>
        <div class="muted" style="color:#3c3c3c;">
          ${picked.title} (${picked.release_year ?? ''}) · TMDb ${picked.vote_average ?? 'n/a'}
        </div>
      </div>
    `;
  }

  function renderResults(results){
    const wrap = q('#results');
    wrap.innerHTML = '';

    const list = document.createElement('div');
    list.style.display = 'grid';
    list.style.gap = '10px';
    wrap.appendChild(list);

    results.forEach(m => {
      const row = document.createElement('div');
      row.className = 'panel';
      row.style.padding = '12px';
      row.style.background = '#efebe2';

      row.innerHTML = `
        <div style="display:flex; justify-content:space-between; gap:12px; align-items:center;">
          <div>
            <div style="font-weight:700;">
              ${m.title} <span class="muted" style="color:#3c3c3c;">(${m.release_year ?? ''})</span>
            </div>
            <div class="muted" style="color:#3c3c3c;">TMDb: ${m.vote_average ?? 'n/a'}</div>
          </div>
          <button type="button" class="icon-btn" data-pick="${m.id}">Pick</button>
        </div>
      `;
      list.appendChild(row);
    });

    qa('[data-pick]').forEach(b => {
      b.onclick = () => {
        picked = results.find(x => String(x.id) === String(b.dataset.pick));
        toast(`Picked: ${picked?.title}`);
        showPicked();
      };
    });
  }

  q('#btn-search').addEventListener('click', async () => {
    const qv = q('#movie-q').value.trim();
    if (!qv) return;

    q('#status').textContent = 'Searching...';

    try {
      const data = await api(`${API}/movies/search?q=${encodeURIComponent(qv)}`);
      const results = (data.results || []).slice(0, 8);

      if (results.length === 0) {
        q('#status').textContent = 'No results.';
        renderResults([]);
        return;
      }

      q('#status').textContent = `Found ${results.length} movies`;
      renderResults(results);
    } catch (err) {
      console.log(err);
      q('#status').textContent = 'Search failed.';
      toast('Search failed', false);
    }
  });

  q('#btn-create').addEventListener('click', async () => {
    const host_name = (q('#host_name').value || '').trim();
    const host_password = (q('#host_password').value || '').trim();

    if (!host_name) return toast('Enter host name', false);
    if (!host_password) return toast('Enter host password', false);
    if (!picked) return toast('Pick a movie first', false);

    const btn = q('#btn-create');
    btn.disabled = true;
    q('#status').textContent = 'Creating game...';

    try {
      const created = await api(`${API}/games`, {
        method: 'POST',
        body: { host_name, host_password }
      });

      const code = created.code;
      if (!code) throw new Error('Create failed: missing game code');

      await api(`${API}/movies/pick`, {
        method: 'POST',
        body: { code, tmdb_id: picked.id }
      });

      q('#status').textContent = 'Going to lobby...';
      location.href = `/g/${code}`;
    } catch (err) {
      console.log(err);
      q('#status').textContent = err.message || 'Create failed.';
      toast(err.message || 'Create failed', false);
    } finally {
      btn.disabled = false;
    }
  });

  showPicked();
</script>
@endsection
