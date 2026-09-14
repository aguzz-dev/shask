<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ '@' . $username }} — Shhask</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Londrina+Solid:wght@100;300;400;900&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        :root {
            --orange-500: #F85A00;
            --orange-50: #FFF1E7;
            --ink-900: #0D0B0A;
            --ink-700: #2A2320;
            --paper: #FFFFFF;
            --paper-warm: #FFF8F2;
            --text-strong: var(--ink-900);
            --text-body: #171311;
            --text-muted: #6B5C55;
            --text-faint: #9A8A82;
            --text-accent: #D44B00;
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
            --shadow-hard-sm: 3px 3px 0 var(--ink-900);
            --shadow-hard-md: 5px 6px 0 var(--ink-900);
            --ease-out-strong: cubic-bezier(.23,1,.32,1);
            --dur-fast: 150ms;
            --ease-pop: cubic-bezier(.34,1.56,.64,1);
            --pattern-dots: radial-gradient(circle at 50% 50%, currentColor 1.6px, transparent 1.7px) 0 0/14px 14px;
            --pattern-scallop: radial-gradient(circle at 50% 0, transparent 9px, currentColor 9px 10px, transparent 10px) 0 0/22px 14px;
            --pattern-grid: linear-gradient(currentColor 1px, transparent 1px) 0 0/22px 22px, linear-gradient(90deg, currentColor 1px, transparent 1px) 0 0/22px 22px;
            --pattern-zigzag: repeating-linear-gradient(135deg, currentColor 0 2px, transparent 2px 10px), repeating-linear-gradient(45deg, currentColor 0 2px, transparent 2px 10px);
            --pattern-waves: repeating-radial-gradient(circle at 0 50%, transparent 0 8px, currentColor 8px 9px) 0 0/24px 18px;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--paper-warm); font-family: var(--font-sans); color: var(--text-body); min-height: 100vh; padding-bottom: 98px; position: relative; overflow-x: hidden; }
        a { color: inherit; }

        .bg-pattern { position: absolute; inset: 0; color: var(--ink-900); opacity: .05; background: var(--pattern-dots); pointer-events: none; }
        .wrap { position: relative; max-width: 420px; margin: 0 auto; padding: 24px 16px 20px; }

        .profile-header { position: relative; text-align: center; }
        .profile-sticker {
            position: absolute; top: -6px; right: 4px; width: 64px; height: 64px; z-index: 3;
            background: url('{{ asset('assets/logo-sticker.webp') }}') center/contain no-repeat;
            transform: rotate(-7deg); animation: shh-bob 5s ease-in-out infinite;
        }
        .avatar-frame {
            display: inline-block; padding: 8px; background: var(--paper); border: 3px solid var(--line-ink);
            border-radius: var(--radius-asym-lg); box-shadow: var(--shadow-hard-md); transform: rotate(1.4deg);
        }
        .avatar-frame .profile-image { width: 112px; height: 112px; border-radius: 50%; overflow: hidden; background: linear-gradient(180deg, #CDDAFD, #FFF1E6); }
        .avatar-frame .profile-image svg { width: 100%; height: 100%; }

        h1.handle {
            margin: 16px 0 0; font-family: var(--font-display); font-weight: 400; font-size: 44px; line-height: .9;
            letter-spacing: -0.015em; color: var(--text-strong); text-transform: lowercase; transform: rotate(-2.5deg);
        }
        .lema { margin: 8px 0 0; font-size: var(--text-body-sm); color: var(--text-muted); }
        .bio { margin: 12px auto 0; max-width: 300px; font-size: var(--text-body-md); line-height: var(--leading-body); color: var(--text-body); }

        .medals { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 18px; }
        .medal {
            display: inline-flex; align-items: center; gap: 6px; background: var(--paper); border: 2px solid var(--line-ink);
            border-radius: var(--radius-pill); box-shadow: 3px 4px 0 var(--ink-900); padding: 6px 13px;
            font-size: var(--text-caption); font-weight: var(--weight-bold); color: var(--ink-900);
        }
        .medal:nth-child(odd) { transform: rotate(-1.2deg); }
        .medal:nth-child(even) { transform: rotate(1.2deg); }

        .section-head {
            display: flex; align-items: baseline; justify-content: space-between; gap: 12px;
            margin: 32px 0 14px; padding-bottom: 10px; border-bottom: 2px solid var(--line-ink);
        }
        .section-head h2 {
            margin: 0; font-family: var(--font-display); font-weight: 400; font-size: 30px; line-height: 1;
            letter-spacing: -0.015em; color: var(--text-strong); text-transform: lowercase;
        }
        .section-count { flex: none; font-size: var(--text-micro); font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--text-muted); }
        .section-tag { flex: none; display: inline-flex; align-items: center; gap: 5px; font-size: var(--text-micro); font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--text-accent); }
        .section-sub { margin: 0 0 14px; font-size: var(--text-caption); line-height: var(--leading-body); color: var(--text-muted); }

        .mailboxes { display: flex; flex-direction: column; gap: 14px; }
        .mailbox {
            position: relative; overflow: hidden; display: flex; align-items: center; gap: 14px;
            padding: 16px 18px; min-height: 104px; text-decoration: none; border: 2px solid var(--line-ink);
            border-radius: var(--radius-asym-lg); transition: transform var(--dur-fast) var(--ease-out-strong), box-shadow var(--dur-fast) var(--ease-out-strong);
        }
        .mailbox:nth-child(odd) { transform: rotate(-0.9deg); }
        .mailbox:nth-child(even) { transform: rotate(0.9deg); }
        .mailbox:active { transform: translate(4px, 5px) !important; }
        .mailbox .pattern { position: absolute; inset: 0; pointer-events: none; }
        .mailbox .sticker { position: relative; flex: none; width: 64px; height: 64px; background-position: center; background-size: contain; background-repeat: no-repeat; }
        .mailbox .copy { position: relative; flex: 1; min-width: 0; }
        .mailbox .eyebrow { display: block; font-size: var(--text-micro); font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--ink-700); }
        .mailbox .title { display: block; margin-top: 4px; font-size: var(--text-subhead); font-weight: var(--weight-black); line-height: var(--leading-tight); color: var(--ink-900); }
        .mailbox .cta { display: inline-flex; align-items: center; gap: 5px; margin-top: 8px; font-size: var(--text-caption); font-weight: var(--weight-bold); color: var(--ink-900); }
        .mailbox .cta svg { width: 15px; height: 15px; }

        .empty { text-align: center; color: var(--text-muted); padding: 30px 10px; }

        .designs-rail { display: flex; gap: 12px; overflow-x: auto; scrollbar-width: none; scroll-snap-type: x proximity; padding: 4px 0 6px; }
        .designs-rail::-webkit-scrollbar { display: none; }
        .design-card {
            position: relative; overflow: hidden; scroll-snap-align: start; flex: none; width: 124px; aspect-ratio: 1;
            padding: 12px; display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-end;
            gap: 6px; border: 2px solid var(--line-ink); border-radius: var(--radius-asym-md);
        }
        .design-card .pattern { position: absolute; inset: 0; pointer-events: none; }
        .design-card .sticker { position: absolute; top: 10px; right: 8px; width: 52px; height: 52px; background-position: center; background-size: contain; background-repeat: no-repeat; transform: rotate(-6deg); }
        .design-card .downloads { position: relative; font-size: var(--text-micro); font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--ink-900); }

        .promo-footer { position: fixed; left: 0; right: 0; bottom: 0; z-index: 10; background: var(--ink-900); border-top: 3px solid var(--line-ink); }
        .promo-inner { max-width: 420px; margin: 0 auto; padding: 13px 16px; display: flex; align-items: center; gap: 14px; }
        .promo-inner span { flex: 1; min-width: 0; font-family: var(--font-display); font-weight: 400; font-size: 23px; line-height: 1; letter-spacing: -0.015em; color: var(--paper-warm); text-transform: lowercase; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            font: var(--weight-bold) var(--text-body-sm)/1 var(--font-sans); letter-spacing: .005em;
            padding: 12px 20px; border-radius: var(--radius-asym-md); border: 2px solid var(--ink-900);
            text-decoration: none; cursor: pointer;
            transition: transform var(--dur-fast) var(--ease-pop), box-shadow var(--dur-fast) var(--ease-out-strong);
        }
        .btn--primary { background: var(--orange-500); color: #FFFFFF; box-shadow: var(--shadow-hard-md); }
        .btn:hover { transform: translate(-1px, -2px); }
        .btn:active { transform: translate(4px, 5px); box-shadow: 1px 1px 0 var(--ink-900) !important; }

        .icon { display: inline-flex; flex: none; }
        .icon svg { width: 100%; height: 100%; display: block; }

        @keyframes shh-bob { 0%, 100% { transform: translateY(0) rotate(-7deg); } 50% { transform: translateY(-5px) rotate(-5deg); } }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: 150ms !important; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern" aria-hidden="true"></div>

    <div class="wrap">
        <div class="profile-header">
            <div class="profile-sticker" aria-hidden="true"></div>

            <div class="avatar-frame">
                <div id="avatar" class="profile-image"></div>
            </div>

            <h1 class="handle"><span>@</span>{{ $username }}</h1>
            <p class="lema">preguntame lo que sea 👀</p>

            @if (!empty($bio))
                <p class="bio">{{ $bio }}</p>
            @endif

            @if (count($achievements))
                <div class="medals">
                    @foreach ($achievements as $a)
                        <span class="medal">{{ $a['emoji'] }} {{ $a['name'] }}</span>
                    @endforeach
                </div>
            @endif
        </div>

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
                   style="background:rgba({{ $accent }},0.16); box-shadow:5px 6px 0 rgba({{ $accent }},1)">
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

        @if (count($creator_designs))
            @php
                $designPatterns = ['var(--pattern-dots)', 'var(--pattern-grid)', 'var(--pattern-waves)', 'var(--pattern-scallop)'];
            @endphp
            <div class="section-head">
                <h2>lo que diseñó</h2>
                <span class="section-tag">
                    <span class="icon" style="width:15px;height:15px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg>
                    </span>
                    en la tienda
                </span>
            </div>
            <p class="section-sub">Diseños suyos que otros ya están usando en sus buzones.</p>

            <div class="designs-rail">
                @foreach ($creator_designs as $i => $design)
                    @php
                        $designColors = json_decode($design['color'] ?? '[]', true);
                        $designAccent = (isset($designColors[0]) && is_array($designColors[0]))
                            ? implode(',', array_slice($designColors[0], 0, 3))
                            : '248,90,0';
                        $designPattern = $designPatterns[$i % count($designPatterns)];
                    @endphp
                    <div class="design-card" style="background:rgba({{ $designAccent }},0.16); box-shadow:4px 5px 0 rgba({{ $designAccent }},1)">
                        <span class="pattern" style="color:rgb({{ $designAccent }}); opacity:.16; background:{{ $designPattern }}"></span>
                        @if ($design['icon'] ?? null)
                            <span class="sticker" style="background-image:url('{{ asset('images/' . $design['icon'] . '.png') }}')"></span>
                        @endif
                        <span class="downloads">{{ $design['downloads_count'] ?? 0 }} usos</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="promo-footer">
        <div class="promo-inner">
            <span>creá el tuyo, es gratis</span>
            <a href="{{ url('/descarga') }}" class="btn btn--primary">Descargar</a>
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
