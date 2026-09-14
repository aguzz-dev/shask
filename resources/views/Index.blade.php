<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Shhask - Envía y recibe mensajes anónimos. Descubre una forma divertida de conectarte con otros. Disponible en Google Play.">
    <meta name="keywords" content="mensajes anónimos, Shhask, enviar mensajes, Google Play, mensajes divertidos, app social">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/shhask-icono.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Londrina+Solid:wght@100;300;400;900&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shhask!</title>
    <style>
        :root {
            /* Acento = color propio del asset (colors[0]); onAccent lo decide
               el servidor con la misma fórmula de luminancia que la app. El
               resto de los tokens --asset-* no tienen todavía un dato real
               por post (no hay fondo/patrón por buzón en el backend), así
               que quedan fijos en el tono "paper" de marca. */
            --asset-accent: rgb({{ isset($accentRgb) ? implode(',', array_slice($accentRgb, 0, 3)) : '255,106,19' }});
            --asset-on-accent: {{ $onAccent ?? '#ffffff' }};
            --asset-bg: #FFFFFF;
            --asset-bg-2: #FAF3ED;
            --asset-ink: #0D0B0A;
            --asset-border-color: #0D0B0A;

            --font-display: "Londrina Solid", "Hanken Grotesk", system-ui, sans-serif;
            --font-sans: "Hanken Grotesk", system-ui, -apple-system, "Segoe UI", sans-serif;
            --text-body-lg: 18px; --text-body-md: 16px; --text-body-sm: 14px; --text-caption: 13px; --text-micro: 11px;
            --leading-body: 1.5; --leading-snug: 1.3;
            --weight-semibold: 600; --weight-bold: 700;
            --tracking-caps: 0.09em;
            --radius-asym-sm: 14px 4px 14px 4px;
            --radius-asym-md: 22px 6px 22px 6px;
            --radius-asym-lg: 34px 8px 34px 8px;
            --ease-out-strong: cubic-bezier(.23,1,.32,1);
            --ease-pop: cubic-bezier(.34,1.56,.64,1);
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: var(--font-sans);
            background: var(--asset-bg);
            min-height: 100vh;
            color: var(--asset-ink);
        }

        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }

        .wrap { max-width: 420px; margin: 0 auto; padding: 22px 16px 40px; position: relative; }

        header { display: flex; align-items: center; justify-content: center; padding: 2px 0 20px; }
        header img { height: 20px; width: auto; display: block; }

        .card-shell { position: relative; }

        .sticker {
            position: absolute; top: -30px; right: -12px; width: 92px; z-index: 3;
            display: block; transform: rotate(-6deg);
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, .18));
            animation: shh-bob 4.5s ease-in-out infinite;
        }

        .card {
            position: relative;
            background: var(--asset-bg-2);
            border: 3px solid var(--asset-border-color);
            border-radius: var(--radius-asym-lg);
            box-shadow: 6px 7px 0 var(--asset-border-color);
            padding: 22px;
            animation: shh-drop 260ms var(--ease-pop) both;
        }

        .profile-row { display: flex; align-items: center; gap: 13px; margin-bottom: 18px; }
        .avatar-badge {
            flex: none; width: 54px; height: 54px; border-radius: 50%;
            background: linear-gradient(to bottom, #FFF1E6, #CDDAFD);
            border: 2px solid var(--asset-border-color);
            overflow: hidden; display: flex; align-items: center; justify-content: center;
        }
        .avatar-badge svg { width: 100%; height: 100%; }
        .username {
            display: block; font-size: var(--text-caption); font-weight: var(--weight-bold);
            letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--asset-ink); opacity: .65;
        }
        .question-text {
            margin: 3px 0 0; font-family: var(--font-display); font-weight: 400; letter-spacing: -0.015em;
            font-size: 32px; line-height: .94; color: var(--asset-ink); text-transform: lowercase;
            word-wrap: break-word;
        }

        .message-field { position: relative; display: block; }
        .message-input {
            width: 100%; padding: 15px 15px 30px; background: var(--asset-bg); color: var(--asset-ink);
            border: 2px solid var(--asset-border-color); border-radius: var(--radius-asym-md); resize: none;
            font-family: var(--font-sans); font-size: var(--text-body-md); line-height: var(--leading-body);
            outline: none; display: block;
        }
        .message-input::placeholder { color: currentColor; opacity: .5; }
        .message-count {
            position: absolute; right: 12px; bottom: 12px; font-size: var(--text-micro);
            font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); color: var(--asset-ink); opacity: .55;
        }

        .hint-box { margin-top: 14px; border: 2px dashed var(--asset-accent); border-radius: var(--radius-asym-md); padding: 13px 14px; }
        .hint-label {
            display: flex; align-items: center; gap: 6px; margin-bottom: 8px; font-size: var(--text-micro);
            font-weight: var(--weight-bold); letter-spacing: var(--tracking-caps); text-transform: uppercase; color: var(--asset-ink);
        }
        .hint-input {
            width: 100%; padding: 10px 12px; background: var(--asset-bg); color: var(--asset-ink);
            border: 2px solid var(--asset-border-color); border-radius: var(--radius-asym-sm);
            font-family: var(--font-sans); font-size: var(--text-body-sm); outline: none; display: block;
        }
        .hint-input::placeholder { color: currentColor; opacity: .5; }

        .send-btn {
            width: 100%; margin-top: 16px; padding: 16px; display: flex; align-items: center; justify-content: center; gap: 9px;
            font-family: var(--font-sans); font-size: var(--text-body-lg); font-weight: var(--weight-bold);
            border: 2px solid var(--asset-border-color); border-radius: var(--radius-asym-md);
            transition: transform 150ms var(--ease-out-strong), box-shadow 150ms var(--ease-out-strong), background-color 150ms var(--ease-out-strong);
            background: var(--asset-bg); color: var(--asset-ink); box-shadow: none; cursor: not-allowed; opacity: .55;
        }
        .send-btn.is-ready { background: var(--asset-accent); color: var(--asset-on-accent); box-shadow: 5px 6px 0 var(--asset-border-color); cursor: pointer; opacity: 1; }
        .send-btn:active:not(:disabled) { transform: translate(4px, 5px); box-shadow: 1px 1px 0 var(--asset-border-color); }

        .privacy-note {
            margin: 13px 0 0; display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: var(--text-caption); color: var(--asset-ink); opacity: .7;
        }

        .form-error {
            margin: 10px 0 0; text-align: center; font-size: var(--text-body-sm); font-weight: var(--weight-semibold); color: #A73B00;
        }

        .sent-card {
            position: relative; background: var(--asset-bg-2); border: 3px solid var(--asset-border-color);
            border-radius: var(--radius-asym-lg); box-shadow: 6px 7px 0 var(--asset-border-color);
            padding: 30px 22px 26px; text-align: center; animation: shh-drop 260ms var(--ease-pop) both;
        }
        .sent-icon {
            display: inline-grid; place-items: center; width: 58px; height: 58px; margin-bottom: 16px;
            background: var(--asset-accent); color: var(--asset-on-accent); border: 2px solid var(--asset-border-color); border-radius: 50%;
        }
        .sent-title {
            margin: 0 0 8px; font-family: var(--font-display); font-weight: 400; letter-spacing: -0.015em;
            font-size: 40px; line-height: .92; color: var(--asset-ink); text-transform: lowercase; transform: rotate(-2.5deg);
        }
        .sent-sub { margin: 0 0 20px; font-size: var(--text-body-md); line-height: var(--leading-body); color: var(--asset-ink); opacity: .75; }
        .reset-btn {
            width: 100%; padding: 14px; background: var(--asset-bg); color: var(--asset-ink);
            border: 2px solid var(--asset-border-color); border-radius: var(--radius-asym-md);
            box-shadow: 4px 5px 0 var(--asset-border-color); font-family: var(--font-sans); font-size: var(--text-body-md);
            font-weight: var(--weight-bold); cursor: pointer; transition: transform 150ms var(--ease-out-strong), box-shadow 150ms var(--ease-out-strong);
        }
        .reset-btn:active { transform: translate(4px, 5px); box-shadow: 1px 1px 0 var(--asset-border-color); }

        .promo-footer {
            margin-top: 34px; background: var(--asset-bg-2); border: 2px solid var(--asset-border-color);
            border-radius: var(--radius-asym-md); box-shadow: 4px 5px 0 var(--asset-border-color);
            padding: 18px 20px; display: flex; align-items: center; gap: 14px;
        }
        .promo-copy { flex: 1; min-width: 0; }
        .promo-copy p:first-child {
            margin: 0 0 3px; font-family: var(--font-display); font-weight: 400; letter-spacing: -0.015em;
            font-size: 23px; line-height: .95; color: var(--asset-ink); text-transform: lowercase;
        }
        .promo-copy p:last-child { margin: 0; font-size: var(--text-caption); line-height: var(--leading-snug); color: var(--asset-ink); opacity: .7; }
        .promo-cta {
            flex: none; padding: 11px 16px; background: var(--asset-accent); color: var(--asset-on-accent);
            border: 2px solid var(--asset-border-color); border-radius: var(--radius-asym-sm); box-shadow: 3px 4px 0 var(--asset-border-color);
            font-size: var(--text-body-sm); font-weight: var(--weight-bold); text-decoration: none;
            transition: transform 150ms var(--ease-out-strong), box-shadow 150ms var(--ease-out-strong);
        }
        .promo-cta:active { transform: translate(3px, 4px); box-shadow: 1px 1px 0 var(--asset-border-color); }

        .icon { display: inline-flex; flex: none; }
        .icon svg { width: 100%; height: 100%; display: block; }

        @keyframes shh-bob { 0%, 100% { transform: translateY(0) rotate(-6deg); } 50% { transform: translateY(-6px) rotate(-4deg); } }
        @keyframes shh-drop { from { opacity: 0; transform: translateY(-10px) scale(.96); } to { opacity: 1; transform: translateY(0) scale(1); } }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: 150ms !important; }
        }
    </style>
</head>

<body>
    <h1 class="sr-only">shhask</h1>
    <div class="wrap">
        <header>
            <img src="{{ asset('assets/logo-wordmark-ink.webp') }}" alt="Shhask">
        </header>

        <div class="card-shell">
            <img class="sticker" src="{{ asset('images/' . $assetIcon . '.png') }}" alt="Sticker">

            <main id="compose-card" class="card">
                <div class="profile-row">
                    <div class="avatar-badge" id="avatar"></div>
                    <div style="min-width:0">
                        <span class="username"><span>@</span>{{ $usernameUser }}</span>
                        <h2 class="question-text">{{ $title }}</h2>
                    </div>
                </div>

                <form id="message-form">
                    <label class="message-field">
                        <textarea id="mensaje" class="message-input" maxlength="500" rows="4" placeholder="escribí lo que quieras..."></textarea>
                        <span class="message-count"><span id="msg-count">0</span>/500</span>
                    </label>

                    <div class="hint-box">
                        <span class="hint-label">
                            <span class="icon" style="width:13px;height:13px">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
                            </span>
                            dejale una pista
                        </span>
                        <input type="text" id="hint" class="hint-input" maxlength="80" placeholder="nos cruzamos siempre en el 152">
                    </div>

                    <button type="submit" id="send-btn" class="send-btn" disabled>
                        <span class="icon" style="width:19px;height:19px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.714 3.048a.498.498 0 0 0-.683.627l2.843 7.627a2 2 0 0 1 0 1.396l-2.842 7.627a.498.498 0 0 0 .682.627l18-8.5a.5.5 0 0 0 0-.904z"/><path d="M6 12h16"/></svg>
                        </span>
                        <span id="send-label">Mandar anónimo</span>
                    </button>

                    <p id="form-error" class="form-error" hidden></p>

                    <p class="privacy-note">
                        <span class="icon" style="width:14px;height:14px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                        </span>
                        No guardamos tu nombre, tu IP ni tu cuenta.
                    </p>
                </form>
            </main>

            <div id="sent-card" class="sent-card" hidden>
                <span class="sent-icon">
                    <span class="icon" style="width:28px;height:28px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </span>
                <h2 class="sent-title">listo, ya salió</h2>
                <p class="sent-sub">Nadie va a saber que fuiste vos.</p>
                <button type="button" id="reset-btn" class="reset-btn">Mandar otra</button>
            </div>
        </div>

        <footer class="promo-footer">
            <div class="promo-copy">
                <p>armá el tuyo</p>
                <p>Gratis en Google Play</p>
            </div>
            <a href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2" target="_blank" rel="noopener" class="promo-cta">Descargar</a>
        </footer>

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

        <script>
            const avatarUserData = @json($avatarUser);
            const parseURLParams = () => {
                return {
                    HairStyle: avatarUserData.HairStyle || 'Bald',
                    HairColor: avatarUserData.HairColor || 'Black',
                    FacialHairType: avatarUserData.FacialHairType || 'Nothing',
                    FacialHairColor: avatarUserData.FacialHairColor || 'Black',
                    EyeType: avatarUserData.EyeType || 'Default',
                    EyeBrowType: avatarUserData.EyebrowType || 'Default',
                    NoseType: avatarUserData.Nose || 'Default',
                    MouthType: avatarUserData.MouthType || 'Default',
                    SkinColor: avatarUserData.SkinColor || 'White',
                    OutfitType: avatarUserData.OutfitType || 'BlazerTShirt',
                    OutfitColor: avatarUserData.OutfitColor || 'Black',
                    Accessory: avatarUserData.Accessory || 'Nothing',
                };
            };

            const drawSVG = (properties) => {
                const skinSVG = SkinService.drawSVG({
                    skinColor: SkinColors[properties.SkinColor]?.svg || SkinColors.White.svg
                });

                const hairStyle = HairStyles[properties.HairStyle] || HairStyles.Bald;
                const hairColor = HairColors[properties.HairColor]?.hexCode || HairColors.Black.hexCode;
                const hairSVG = HairService.drawSVG({
                    style: {
                        ...hairStyle,
                        svg: hairStyle?.svg.replaceAll('$TO_REPLACE_WITH_HAIRS_COLOR', hairColor),
                    },
                    color: { hexCode: hairColor },
                });

                const facialHairType = FacialHair[properties.FacialHairType];
                const facialHairColor = FacialHairColors[properties.FacialHairColor]?.hexCode || FacialHairColors.Black.hexCode;
                const facialHairSVG = FacialHairService.drawSVG({
                    style: {
                        ...facialHairType,
                        svg: facialHairType?.svg.replaceAll('$TO_REPLACE_WITH_FACIAL_HAIRS_COLOR', facialHairColor),
                    },
                    color: { hexCode: facialHairColor },
                });

                const eyeStyle = Eyes[properties.EyeType]?.svg || '';
                const eyeSVG = EyesService.drawSVG({ eye: eyeStyle });

                const eyeBrowType = Eyebrows[properties.EyeBrowType]?.svg || '';
                const eyeBrowSVG = EyesBrowsService.drawSVG({ eyebrowType: eyeBrowType });

                const mouthType = Mouths[properties.MouthType]?.svg || '';
                const mouthSVG = MouthsService.drawSVG({ mouthType: mouthType });

                const outfitStyle = Outfits[properties.OutfitType];
                const outfitColor = OutfitColors[properties.OutfitColor]?.hexCode || OutfitColors.Black.hexCode;
                const outfitSVG = OutfitsService.drawSVG({
                    style: {
                        ...outfitStyle,
                        svg: outfitStyle?.svg.replaceAll('$TO_REPLACE_WITH_OUTFIT_COLOR', outfitColor),
                    },
                    color: { hexCode: outfitColor },
                });

                const noseType = Noses[properties.NoseType]?.svg || '';
                const noseSVG = NosesService.drawSVG({ noseType: noseType });

                const accessorieType = Accessories[properties.Accessory]?.svg || '';
                const accessoriesSVG = AccessoriesService.drawSVG({ accessorieType: accessorieType });

                return `
                <svg width="264px" height="280px" viewBox="0 0 264 280" xmlns="http://www.w3.org/2000/svg">
                    <g>${skinSVG}</g>
                    <g transform="translate(75, 80)">${mouthSVG}</g>
                    <g transform="translate(75, 80)">${noseSVG}</g>
                    <g transform="translate(9.5, 2)">${facialHairSVG}</g>
                    <g transform="translate(75, 80)">${eyeSVG}</g>
                    <g transform="translate(75, 80)">${eyeBrowSVG}</g>
                    <g transform="translate(10, 0)">${hairSVG}</g>
                    <g transform="translate(28, 100)">${outfitSVG}</g>
                    <g transform="translate(75, 80)">${accessoriesSVG}</g>
                </svg>
            `;
            };

            const renderAvatar = () => {
                const properties = parseURLParams();

                if (!HairStyles[properties.HairStyle]) properties.HairStyle = 'Bald';
                if (!HairColors[properties.HairColor]) properties.HairColor = 'Black';
                if (!SkinColors[properties.SkinColor]) properties.SkinColor = 'White';
                if (!FacialHair[properties.FacialHairType]) properties.FacialHairType = 'Nothing';
                if (!FacialHairColors[properties.FacialHairColor]) properties.FacialHairColor = 'Black';
                if (!Eyes[properties.EyeType]) properties.EyeType = 'Default';
                if (!Eyebrows[properties.EyeBrowType]) properties.EyeBrowType = 'Default';
                if (!Mouths[properties.MouthType]) properties.MouthType = 'Default';
                if (!Outfits[properties.OutfitType]) properties.OutfitType = 'BlazerTShirt';
                if (!OutfitColors[properties.OutfitColor]) properties.OutfitColor = 'Black';
                if (!Noses[properties.NoseType]) properties.NoseType = 'Default';
                if (!Accessories[properties.Accessory]) properties.Accessory = 'Nothing';

                document.getElementById('avatar').innerHTML = drawSVG(properties);
            };

            renderAvatar();
        </script>

        <script>
            (function () {
                const idPost = {{ (int) $idPost }};
                const username = @json($usernameUser);
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                const textarea = document.getElementById('mensaje');
                const hintInput = document.getElementById('hint');
                const counter = document.getElementById('msg-count');
                const sendBtn = document.getElementById('send-btn');
                const sendLabel = document.getElementById('send-label');
                const composeCard = document.getElementById('compose-card');
                const sentCard = document.getElementById('sent-card');
                const errorMsg = document.getElementById('form-error');
                const form = document.getElementById('message-form');

                function updateSendState() {
                    const ready = textarea.value.trim().length > 0;
                    sendBtn.disabled = !ready;
                    sendBtn.classList.toggle('is-ready', ready);
                }

                function showError(msg) {
                    errorMsg.textContent = msg;
                    errorMsg.hidden = false;
                }
                function hideError() {
                    errorMsg.hidden = true;
                }

                function showSent() {
                    composeCard.hidden = true;
                    sentCard.hidden = false;
                }

                function resetCompose() {
                    textarea.value = '';
                    hintInput.value = '';
                    counter.textContent = '0';
                    hideError();
                    updateSendState();
                    sentCard.hidden = true;
                    composeCard.hidden = false;
                }

                let cooldownTimer = null;
                function startCooldown(seconds) {
                    sendBtn.disabled = true;
                    let remaining = seconds;
                    sendLabel.textContent = `Esperá ${remaining}s`;
                    clearInterval(cooldownTimer);
                    cooldownTimer = setInterval(() => {
                        remaining -= 1;
                        if (remaining <= 0) {
                            clearInterval(cooldownTimer);
                            sendLabel.textContent = 'Mandar anónimo';
                            updateSendState();
                        } else {
                            sendLabel.textContent = `Esperá ${remaining}s`;
                        }
                    }, 1000);
                }

                textarea.addEventListener('input', () => {
                    counter.textContent = textarea.value.length;
                    updateSendState();
                    hideError();
                });

                document.getElementById('reset-btn').addEventListener('click', resetCompose);

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const message = textarea.value.trim();
                    if (!message) return;

                    sendBtn.disabled = true;
                    hideError();

                    const headers = {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    };

                    try {
                        const res = await fetch('question/create-web', {
                            method: 'POST',
                            headers,
                            body: new URLSearchParams({ id_post: idPost, text: message, hint: hintInput.value }),
                        });

                        if (res.ok) {
                            fetch('/pushNotification', {
                                method: 'POST',
                                headers,
                                body: new URLSearchParams({ postId: idPost, username, text: message }),
                            }).catch(() => {});
                            showSent();
                        } else if (res.status === 429) {
                            showError('Esperá un momento para volver a mandar otro mensaje.');
                            startCooldown(30);
                        } else if (res.status === 423) {
                            // Usuario bloqueado: no revelamos el bloqueo, mismo comportamiento que antes.
                            showSent();
                        } else {
                            showError('Ups, parece que algo no está bien. Probá de nuevo.');
                            updateSendState();
                        }
                    } catch (e) {
                        showError('Ups, parece que algo no está bien. Probá de nuevo.');
                        updateSendState();
                    }
                });

                updateSendState();
            })();
        </script>
    </div>
</body>

</html>
