<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ '@' . $username }} — Shhask</title>
    <link href="https://fonts.googleapis.com/css2?family=Londrina+Solid:wght@400;900&family=Hanken+Grotesk:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #ECE8E1; font-family: 'Hanken Grotesk', sans-serif; color: #111;
               min-height: 100vh; padding-bottom: 90px; }
        .display { font-family: 'Londrina Solid', cursive; }
        .sb { background: #fff; border: solid #111; border-width: 1.2px 4px 4px 1.2px; border-radius: 16px; }
        .wrap { max-width: 420px; margin: 0 auto; padding: 24px 16px; }
        .header { text-align: center; }
        .avatar-frame { display: inline-block; padding: 8px; transform: rotate(1.4deg); }
        .profile-image { width: 110px; height: 110px; border-radius: 50%; overflow: hidden;
                         background: linear-gradient(180deg,#CDDAFD,#FFF1E6); }
        .profile-image svg { width: 100%; height: 100%; }
        h1 { font-size: 38px; margin-top: 12px; letter-spacing: .5px; }
        .lema { color: #777; font-size: 14px; margin-top: 2px; }
        .medals { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 16px; }
        .medal { padding: 6px 12px; font-size: 13px; font-weight: 700; transform: rotate(-1.2deg); }
        .medal:nth-child(even) { transform: rotate(1.2deg); }
        .section-title { font-family: 'Londrina Solid'; font-size: 20px; margin: 26px 0 10px; }
        a.mailbox { display: flex; align-items: center; gap: 12px; padding: 14px;
                    margin-bottom: 12px; text-decoration: none; color: #111;
                    transform: rotate(-0.8deg); }
        a.mailbox:nth-of-type(even) { transform: rotate(0.8deg); }
        .mailbox-sticker { width: 44px; height: 44px; border-radius: 12px; border: 2px solid #111;
                           display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                           overflow: hidden; }
        .mailbox-sticker img { width: 30px; height: 30px; object-fit: contain; }
        .mailbox-title { font-weight: 800; font-size: 16px; flex: 1; }
        .arrow { color: #FF6A13; font-size: 22px; font-weight: 800; }
        .empty { text-align: center; color: #777; padding: 30px 10px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #111; color: #fff;
                  text-align: center; padding: 16px; }
        .footer a { color: #fff; text-decoration: none; font-size: 18px; letter-spacing: .5px; }
        .footer .naranja { color: #FF6A13; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <div class="sb avatar-frame">
                <div id="avatar" class="profile-image"></div>
            </div>
            <h1 class="display">{{ '@' . strtoupper($username) }}</h1>
            <p class="lema">preguntame lo que sea 👀</p>
            @if (count($achievements))
                <div class="medals">
                    @foreach ($achievements as $a)
                        <span class="sb medal">{{ $a['emoji'] }} {{ $a['name'] }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <h2 class="section-title display">SUS BUZONES</h2>
        @forelse ($mailboxes as $mailbox)
            @php
                // color es [[r,g,b,a], ...] (mismo formato que usa Index.blade).
                $stickerBg = isset($mailbox['colors'][0]) && is_array($mailbox['colors'][0])
                    ? 'rgba(' . implode(',', $mailbox['colors'][0]) . ')'
                    : '#FFF1E6';
            @endphp
            <a class="sb mailbox" href="{{ url('/' . $mailbox['url']) }}">
                <span class="mailbox-sticker" style="background: {{ $stickerBg }};">
                    @if ($mailbox['icon'])
                        <img src="{{ asset('images/' . $mailbox['icon'] . '.png') }}" alt="">
                    @endif
                </span>
                <span class="mailbox-title">{{ $mailbox['title'] }}</span>
                <span class="arrow">→</span>
            </a>
        @empty
            <p class="empty">No hay buzones activos ahora mismo 🤫</p>
        @endforelse
    </div>

    <div class="footer">
        <a href="{{ url('/descarga') }}" class="display">CREÁ EL TUYO — BAJATE <span class="naranja">SHHASK</span></a>
    </div>

    {{-- Render del avatar: mismos catálogos que Index.blade.php + render compartido --}}
    <script src="{{ asset('avatar/hairstyles.js') }}"></script>
    <script src="{{ asset('avatar/haircolors.js') }}"></script>
    <script src="{{ asset('avatar/skincolors.js') }}"></script>
    <script src="{{ asset('avatar/facialhair.js') }}"></script>
    <script src="{{ asset('avatar/facialhaircolors.js') }}"></script>
    <script src="{{ asset('avatar/eyes.js') }}"></script>
    <script src="{{ asset('avatar/eyebrows.js') }}"></script>
    <script src="{{ asset('avatar/mouth.js') }}"></script>
    <script src="{{ asset('avatar/outfits.js') }}"></script>
    <script src="{{ asset('avatar/outfitcolors.js') }}"></script>
    <script src="{{ asset('avatar/noses.js') }}"></script>
    <script src="{{ asset('avatar/accessories.js') }}"></script>
    <script src="{{ asset('avatar/render.js') }}"></script>
    <script>
        renderShhaskAvatar('avatar', @json($avatar));
    </script>
</body>
</html>
