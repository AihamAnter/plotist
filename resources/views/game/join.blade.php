@extends('layouts.app')

@section('subtitle', 'Join Game')

@section('content')
  <div class="card">
    <h2 style="margin:0 0 8px 0;">Join Game</h2>
    <p class="muted">Enter the game code, your name, and the game password.</p>
    <div class="hr"></div>

    <form id="join-form" class="grid" style="grid-template-columns: 1fr; gap:12px;">
      <div>
        <label>Game code</label>
        <input name="code" value="{{ $code ?? '' }}" placeholder="e.g. ABCD12" required>
      </div>
      <div class="grid" style="grid-template-columns: 1fr 1fr; gap:12px;">
        <div>
          <label>Name</label>
          <input name="name" placeholder="e.g. Sara" required>
        </div>
        <div>
          <label>Game password</label>
          <input name="password" type="password" required>
        </div>
      </div>
      <div class="right">
        <button class="btn primary" type="submit">Join</button>
      </div>
    </form>
  </div>
@endsection

@section('scripts')
<script>
  q('#join-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = Object.fromEntries(new FormData(e.target).entries());
    await api(`${API}/games/${fd.code}/players`, {
      method: 'POST',
      body: { name: fd.name, password: fd.password }
    });
    // cookie/token set by backend; go to lobby
    location.href = `/g/${fd.code}`;
  });
</script>
@endsection
