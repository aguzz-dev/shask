<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Shhask Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Londrina+Solid:wght@400;900&family=Hanken+Grotesk:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #ECE8E1; font-family: 'Hanken Grotesk', sans-serif; color: #111; }
        .display { font-family: 'Londrina Solid', cursive; }
        .naranja { color: #FF6A13; }
        .topbar { background: #111; color: #fff; display: flex; align-items: center; gap: 20px; padding: 12px 20px; }
        .topbar .logo { font-family: 'Londrina Solid'; font-size: 20px; color: #fff; text-decoration: none; }
        .topbar a.nav { color: #aaa; font-size: 13px; font-weight: 700; text-decoration: none; }
        .topbar a.nav.active, .topbar a.nav:hover { color: #FF6A13; }
        .topbar .who { margin-left: auto; color: #777; font-size: 12px; display: flex; gap: 10px; align-items: center; }
        .topbar .who button { background: none; border: none; color: #aaa; font: inherit; cursor: pointer; font-weight: 700; }
        .wrap { max-width: 900px; margin: 0 auto; padding: 22px 16px; }
        .sb { background: #fff; border: solid #111; border-width: 1.2px 4px 4px 1.2px; border-radius: 12px; }
        .btn { display: inline-block; background: #111; color: #fff; border: none; border-radius: 10px;
               padding: 9px 16px; font: 700 13px 'Hanken Grotesk'; cursor: pointer; text-decoration: none; }
        .btn.naranja-bg { background: #FF6A13; color: #fff; }
        .btn.peligro { background: #D62828; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 10px; letter-spacing: 1px; font-weight: 800; color: #777; margin-bottom: 4px; }
        .field input, .field select { width: 100%; border: solid #111; border-width: 1.2px 3px 3px 1.2px;
               border-radius: 8px; padding: 8px 10px; font: 14px 'Hanken Grotesk'; background: #fff; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; font-size: 10px; letter-spacing: 1px; color: #777; padding: 8px; }
        td { padding: 8px; background: #fff; border-top: 1.5px solid #111; vertical-align: middle; }
        .thumb { width: 48px; height: 48px; border-radius: 10px; border: 1.5px solid #111; object-fit: contain; background: #fff; }
        .tag { background: #ECE8E1; border: 1px solid #111; border-radius: 6px; padding: 1px 6px; font-size: 10px; margin-right: 3px; }
        .flash { padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; font-weight: 700; font-size: 13px; }
        .flash.ok { background: #d8f3dc; border: 1.5px solid #111; }
        .flash.error { background: #ffd5d5; border: 1.5px solid #111; }
    </style>
</head>
<body>
@php($adminPath = '/' . config('app.admin_path'))
@if(session('admin_id'))
    <div class="topbar">
        <a class="logo" href="{{ $adminPath }}">SHHASK <span class="naranja">ADMIN</span></a>
        <a class="nav {{ request()->is(trim($adminPath, '/')) ? 'active' : '' }}" href="{{ $adminPath }}">Catálogo</a>
        <a class="nav {{ request()->is('*packs*') ? 'active' : '' }}" href="{{ $adminPath }}/packs">Packs</a>
        <a class="nav {{ request()->is('*upload*') ? 'active' : '' }}" href="{{ $adminPath }}/images/upload">Subir</a>
        <span class="who">
            {{ request()->attributes->get('admin')['name'] ?? '' }}
            <form method="POST" action="{{ $adminPath }}/logout">@csrf<button>salir</button></form>
        </span>
    </div>
@endif
<div class="wrap">
    @if(session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
    @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
    @yield('content')
</div>
</body>
</html>
