@extends('admin.layout')

@section('content')
<div class="sb" style="max-width: 360px; margin: 60px auto; padding: 28px; text-align: center;">
    <div class="display" style="font-size: 26px;">SHHASK <span class="naranja">ADMIN</span></div>
    <form method="POST" action="/{{ config('app.admin_path') }}/login" style="margin-top: 18px; text-align: left;">
        @csrf
        <div class="field">
            <label>EMAIL</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="field">
            <label>PASSWORD</label>
            <input type="password" name="password" required>
        </div>
        <button class="btn" style="width: 100%;">ENTRAR</button>
    </form>
</div>
@endsection
