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
  <a class="tab" href="/">Create</a>
  <a class="tab active" href="/join">Join</a>
</div>


  <div class="panel">
    <form id="join-form">

      <div class="panel-row">
        <div class="field" style="flex:1;">
          <div class="label">Game Code</div>
          <input class="input" name="code" id="code" value="{{ $code ?? '' }}" placeholder="e.g. ABC123" required>
        </div>
      </div>

      <div class="panel-row" style="margin-top:14px;">
        <div class="field" style="flex:1;">
          <div class="label">Name</div>
          <input class="input" name="name" id="name" placeholder="e.g. Sami" required>
        </div>
      </div>

      <div class="center-actions" style="margin-top:18px;">
        <button type="submit" class="big-btn" id="btn-join">Join</button>
      </div>

      <div class="muted" id="status" style="text-align:center; margin-top:10px;"></div>
    </form>
  </div>
@endsection

@section('scripts')
<script>
  const initialCode = @json($code ?? '');

  function normCode(x){
    return (x || '').trim().toUpperCase();
  }

  q('#join-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const code = normCode(q('#code').value);
    const name = (q('#name').value || '').trim();

    if (!code) return toast('Enter a code', false);
    if (!name) return toast('Enter your name', false);

    const btn = q('#btn-join');
    btn.disabled = true;
    q('#status').textContent = '';

    try {
      await api(`${API}/games/${code}/players`, { method: 'POST', body: { name } });
      toast('Joined!');
      location.href = `/g/${code}`;
    } catch (err) {
      console.log(err);
      q('#status').textContent = err.message || 'Join failed.';
      toast(err.message || 'Join failed', false);
    } finally {
      btn.disabled = false;
    }
  });

  (async () => {
    const code = normCode(initialCode);
    if (code) q('#code').value = code;
  })();
</script>
@endsection
