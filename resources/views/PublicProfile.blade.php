<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('assets/favicon-16.png') }}" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('assets/apple-touch-icon.png') }}">
    <title>{{ '@' . $username }} — Shhask</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Londrina+Solid:wght@100;300;400;900&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        :root {
            --orange-500: #F85A00;
            --orange-300: #FF9757;
            --orange-50: #FFF1E7;
            --ink-900: #0D0B0A;
            --ink-700: #2A2320;
            --ink-500: #6B5C55;
            --ink-300: #C7BBB4;
            --paper: #FFFFFF;
            --paper-warm: #FFF8F2;
            --text-strong: var(--ink-900);
            --text-body: #171311;
            --text-muted: #6B5C55;
            --line-ink: var(--ink-900);
            --font-display: "Londrina Solid", "Hanken Grotesk", system-ui, sans-serif;
            --font-sans: "Hanken Grotesk", system-ui, -apple-system, "Segoe UI", sans-serif;
            --text-body-md: 16px; --text-body-sm: 14px; --text-subhead: 19px; --text-caption: 13px; --text-micro: 11px;
            --leading-body: 1.5; --leading-tight: 1.12;
            --weight-bold: 700; --weight-black: 800;
            --tracking-caps: 0.09em;
            --radius-asym-md: 22px 6px 22px 6px;
            --radius-asym-lg: 34px 8px 34px 8px;
            --radius-pill: 999px;
            --shadow-hard-md: 5px 6px 0 var(--ink-900);
            --ease-out-strong: cubic-bezier(.23,1,.32,1);
            --dur-fast: 150ms;
            --ease-pop: cubic-bezier(.34,1.56,.64,1);
            --pattern-scallop: radial-gradient(circle at 50% 0, transparent 9px, currentColor 9px 10px, transparent 10px) 0 0/22px 14px;
            --pattern-grid: linear-gradient(currentColor 1px, transparent 1px) 0 0/22px 22px, linear-gradient(90deg, currentColor 1px, transparent 1px) 0 0/22px 22px;
            --pattern-zigzag: repeating-linear-gradient(135deg, currentColor 0 2px, transparent 2px 10px), repeating-linear-gradient(45deg, currentColor 0 2px, transparent 2px 10px);
            --pattern-waves: repeating-radial-gradient(circle at 0 50%, transparent 0 8px, currentColor 8px 9px) 0 0/24px 18px;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--paper-warm); font-family: var(--font-sans); color: var(--text-body); min-height: 100vh; padding-bottom: 98px; overflow-x: hidden; }
        a { color: inherit; text-decoration: none; }

        .hero { position: relative; background: var(--ink-900); border-bottom: 3px solid var(--line-ink); overflow: hidden; }
        .hero-pattern { position: absolute; inset: 0; color: var(--orange-500); opacity: .18; background: var(--pattern-grid); pointer-events: none; }

        .marquee { position: relative; overflow: hidden; padding: 9px 0; border-bottom: 2px solid var(--ink-700); }
        .marquee-track { display: flex; width: max-content; animation: shh-marquee 22s linear infinite; will-change: transform; }
        .marquee-set { display: flex; gap: 20px; padding-right: 20px; font-size: var(--text-micro); font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--orange-300); white-space: nowrap; }
        .marquee-set span.dot { color: var(--ink-500); }

        .hero-inner { position: relative; max-width: 420px; margin: 0 auto; padding: 26px 16px 34px; }
        .profile-sticker {
            position: absolute; top: 10px; right: 10px; width: 66px; height: 66px; z-index: 3;
            background: url('{{ asset('assets/logo-sticker.webp') }}') center/contain no-repeat;
            transform: rotate(-7deg); animation: shh-bob 5s ease-in-out infinite;
        }
        .avatar-frame {
            display: inline-block; padding: 7px; background: var(--paper); border: 3px solid var(--line-ink);
            border-radius: var(--radius-asym-lg); box-shadow: 6px 7px 0 var(--orange-500); transform: rotate(-1.4deg);
        }
        .avatar-frame .profile-image { width: 96px; height: 96px; border-radius: 50%; overflow: hidden; background: var(--orange-50); }
        .avatar-frame .profile-image svg { width: 100%; height: 100%; }

        .hero h1 {
            margin: 18px 0 0; font-family: var(--font-display); font-weight: 400; font-size: clamp(50px, 15vw, 64px);
            line-height: .82; letter-spacing: -0.015em; text-transform: lowercase;
        }
        .hero h1 .handle { display: block; color: var(--orange-500); transform: rotate(-2.5deg); }
        .hero h1 .tagline { display: block; color: transparent; -webkit-text-stroke: 2px var(--paper-warm); transform: rotate(1.5deg); }
        .hero .bio { margin: 18px 0 0; max-width: 30ch; font-size: var(--text-body-md); line-height: var(--leading-body); color: var(--ink-300); text-wrap: pretty; }

        .wrap { position: relative; max-width: 420px; margin: 0 auto; padding: 0 16px 20px; }

        .medals { display: flex; flex-wrap: wrap; gap: 8px; margin: -16px 0 0; position: relative; z-index: 4; }
        .medal {
            display: inline-flex; align-items: center; gap: 6px; background: var(--paper); border: 2px solid var(--line-ink);
            border-radius: var(--radius-pill); box-shadow: 3px 4px 0 var(--ink-900); padding: 7px 13px;
            font-size: var(--text-caption); font-weight: var(--weight-bold); color: var(--ink-900);
        }
        .medal:nth-child(odd) { transform: rotate(-1.2deg); }
        .medal:nth-child(even) { transform: rotate(1.2deg); }

        .section-head {
            display: flex; align-items: baseline; justify-content: space-between; gap: 12px;
            margin: 30px 0 14px; padding-bottom: 10px; border-bottom: 2px solid var(--line-ink);
        }
        .section-head h2 {
            margin: 0; font-family: var(--font-display); font-weight: 400; font-size: 32px; line-height: 1;
            letter-spacing: -0.015em; color: var(--text-strong); text-transform: lowercase; transform: rotate(-2.5deg);
        }
        .section-count { flex: none; font-size: var(--text-micro); font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--text-muted); }

        .mailboxes { display: flex; flex-direction: column; gap: 16px; }
        .mailbox {
            position: relative; overflow: hidden; display: flex; align-items: center; gap: 14px;
            padding: 16px 18px; min-height: 108px; border: 2px solid var(--line-ink);
            border-radius: var(--radius-asym-lg); transition: transform var(--dur-fast) var(--ease-out-strong), box-shadow var(--dur-fast) var(--ease-out-strong);
        }
        .mailbox:nth-child(odd) { transform: rotate(-0.9deg); }
        .mailbox:nth-child(even) { transform: rotate(0.9deg); }
        .mailbox:active { transform: translate(4px, 5px) !important; }
        .mailbox .pattern { position: absolute; inset: 0; pointer-events: none; }
        .mailbox .sticker { position: relative; flex: none; width: 66px; height: 66px; background-position: center; background-size: contain; background-repeat: no-repeat; }
        .mailbox .copy { position: relative; flex: 1; min-width: 0; }
        .mailbox .eyebrow { display: block; font-size: var(--text-micro); font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--ink-700); }
        .mailbox .title { display: block; margin-top: 4px; font-size: var(--text-subhead); font-weight: var(--weight-black); line-height: var(--leading-tight); color: var(--ink-900); }
        .mailbox .cta { display: inline-flex; align-items: center; gap: 5px; margin-top: 8px; font-size: var(--text-caption); font-weight: var(--weight-bold); color: var(--ink-900); }
        .mailbox .cta svg { width: 15px; height: 15px; }

        .empty { text-align: center; color: var(--text-muted); padding: 30px 10px; }

        .privacy-note {
            margin: 20px 0 0; display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: var(--text-caption); color: var(--text-muted);
        }
        .privacy-note svg { width: 15px; height: 15px; }

        .promo-footer { position: fixed; left: 0; right: 0; bottom: 0; z-index: 10; background: var(--orange-500); border-top: 3px solid var(--line-ink); }
        .promo-inner { max-width: 420px; margin: 0 auto; padding: 13px 16px; display: flex; align-items: center; gap: 14px; }
        .promo-inner span { flex: 1; min-width: 0; font-family: var(--font-display); font-weight: 400; font-size: 23px; line-height: 1; letter-spacing: -0.015em; color: var(--ink-900); text-transform: lowercase; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            font: var(--weight-bold) var(--text-body-sm)/1 var(--font-sans); letter-spacing: .005em;
            padding: 12px 20px; border-radius: var(--radius-asym-md); border: 2px solid var(--ink-900);
            cursor: pointer; transition: transform var(--dur-fast) var(--ease-pop), box-shadow var(--dur-fast) var(--ease-out-strong);
        }
        .btn--ink { background: var(--ink-900); color: var(--paper-warm); box-shadow: 5px 6px 0 var(--orange-500); }
        .btn:hover { transform: translate(-1px, -2px); }
        .btn:active { transform: translate(4px, 5px); box-shadow: 1px 1px 0 var(--ink-900) !important; }

        @keyframes shh-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @keyframes shh-bob { 0%, 100% { transform: translateY(0) rotate(-7deg); } 50% { transform: translateY(-5px) rotate(-5deg); } }
        @keyframes shh-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: 150ms !important; }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-pattern" aria-hidden="true"></div>

        <div class="marquee">
            <div class="marquee-track">
                <div class="marquee-set">
                    <span>100% anónimo</span><span class="dot">•</span>
                    <span>nadie sabe quién escribe</span><span class="dot">•</span>
                    <span>buzón abierto</span><span class="dot">•</span>
                </div>
                <div class="marquee-set" aria-hidden="true">
                    <span>100% anónimo</span><span class="dot">•</span>
                    <span>nadie sabe quién escribe</span><span class="dot">•</span>
                    <span>buzón abierto</span><span class="dot">•</span>
                </div>
            </div>
        </div>

        <div class="hero-inner">
            <div class="profile-sticker" aria-hidden="true"></div>

            <div class="avatar-frame">
                <div id="avatar" class="profile-image"></div>
            </div>

            <h1>
                <span class="handle"><span>@</span>{{ $username }}</span>
                <span class="tagline">te escucha</span>
            </h1>

            @if (!empty($bio))
                <p class="bio">{{ $bio }}</p>
            @endif
        </div>
    </div>

    <div class="wrap">
        @if (count($achievements))
            <div class="medals">
                @foreach ($achievements as $a)
                    <span class="medal">{{ $a['emoji'] }} {{ $a['name'] }}</span>
                @endforeach
            </div>
        @endif

        <div class="section-head">
            <h2>sus buzones</h2>
            <span class="section-count">{{ count($mailboxes) }} activos</span>
        </div>

        <div class="mailboxes">
            @forelse ($mailboxes as $i => $mailbox)
                @php
                    // color es [[r,g,b,a], ...] (mismo formato que usa Index.blade).
                    $accent = (isset($mailbox['colors'][0]) && is_array($mailbox['colors'][0]))
                        ? implode(',', array_slice($mailbox['colors'][0], 0, 3))
                        : '248,90,0';
                    $patterns = ['var(--pattern-scallop)', 'var(--pattern-grid)', 'var(--pattern-zigzag)', 'var(--pattern-waves)'];
                    $pattern = $patterns[$i % count($patterns)];
                @endphp
                <a class="mailbox" href="{{ url('/' . $mailbox['url']) }}"
                   style="background:rgba({{ $accent }},0.16); box-shadow:5px 6px 0 rgba({{ $accent }},1); animation:shh-in 300ms var(--ease-out-strong) {{ $i * 60 }}ms both">
                    <span class="pattern" style="color:rgb({{ $accent }}); opacity:.16; background:{{ $pattern }}"></span>
                    @if ($mailbox['icon'])
                        <span class="sticker" style="background-image:url('{{ asset('images/' . $mailbox['icon'] . '.png') }}')"></span>
                    @endif
                    <span class="copy">
                        <span class="eyebrow">buzón abierto</span>
                        <span class="title">{{ $mailbox['title'] }}</span>
                        <span class="cta">
                            mandarle algo
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </span>
                    </span>
                </a>
            @empty
                <p class="empty">No hay buzones activos ahora mismo 🤫</p>
            @endforelse
        </div>

        <p class="privacy-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
            No guardamos tu nombre, tu IP ni tu cuenta.
        </p>
    </div>

    <div class="promo-footer">
        <div class="promo-inner">
            <span>creá el tuyo, es gratis</span>
            <a href="{{ url('/descarga') }}" class="btn btn--ink">Descargar</a>
        </div>
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
