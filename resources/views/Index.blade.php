<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Shhask - Envía y recibe mensajes anónimos. Descubre una forma divertida de conectarte con otros. Disponible en Google Play.">
    <meta name="keywords" content="mensajes anónimos, Shhask, enviar mensajes, Google Play, mensajes divertidos, app social">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/shhask-icono.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Londrina+Solid:wght@400;900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shhask!</title>
    <style>
        :root {
            /* Acento = color propio del asset (colors[0]); onAccent lo decide
               el servidor con la misma fórmula de luminancia que la app. */
            --accent: rgb({{ isset($accentRgb) ? implode(',', array_slice($accentRgb, 0, 3)) : '255,106,19' }});
            --on-accent: {{ $onAccent ?? '#ffffff' }};
            --glow: rgba({{ isset($colors[1]) ? implode(',', array_slice((array) $colors[1], 0, 3)) : '255,106,19' }}, 0.22);
            --cream: #ECE8E1;
            --white-cream: #FEFEF2;
            --ink: #000000;
            --grey-soft: #676262;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Hanken Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--cream);
            min-height: 100vh;
            padding: 20px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .container {
            max-width: 420px;
            margin: 0 auto;
            padding: 20px 4px;
            position: relative;
        }

        .header {
            text-align: center;
            padding: 12px 0 24px;
        }

        .logo-shhask {
            width: 46%;
            max-width: 190px;
        }

        .card-container {
            position: relative;
            margin: 20px 0;
        }

        /* Halo suave con el color propio del asset detrás de la card: le da
           identidad al buzón sin romper el fondo crema de marca. */
        .card-container::before {
            content: '';
            position: absolute;
            inset: -18px;
            background: radial-gradient(circle at 30% 20%, var(--glow), transparent 65%);
            border-radius: 32px;
            z-index: 0;
        }

        .asset-icon {
            position: absolute;
            width: 104px;
            right: -14px;
            top: -30px;
            z-index: 2;
            transform: rotate(-6deg);
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.18));
        }

        .question-card {
            background: var(--white-cream);
            border-radius: 20px;
            padding: 24px 22px;
            position: relative;
            z-index: 1;
            border: 2px solid var(--ink);
            border-bottom-width: 5px;
            border-right-width: 4px;
        }

        .profile-section {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 18px;
        }

        .profile-image svg {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(to bottom, #FFF1E6, #CDDAFD);
            border: 1.5px solid var(--ink);
        }

        .profile-info {
            flex-grow: 1;
            padding-top: 2px;
        }

        .username {
            display: block;
            color: var(--grey-soft);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .question-text {
            font-family: 'Londrina Solid', sans-serif;
            font-weight: 900;
            font-size: 1.7rem;
            line-height: 1.1;
            color: var(--ink);
            word-wrap: break-word;
        }

        .message-input {
            width: 100%;
            padding: 14px;
            border: 1.5px solid var(--ink);
            border-radius: 14px;
            resize: none;
            margin-bottom: 12px;
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 0.95rem;
            background: #fff;
        }

        .hint-section {
            text-align: center;
        }

        .hint-text {
            font-family: 'Hanken Grotesk', sans-serif;
            font-weight: 600;
            color: var(--grey-soft);
            margin-bottom: 10px;
        }

        .hint-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--ink);
            border-radius: 12px;
            margin-bottom: 16px;
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 0.9rem;
            background: #fff;
        }

        .submit-button {
            width: 100%;
            background: var(--accent);
            color: var(--on-accent);
            border: 2px solid var(--ink);
            border-bottom-width: 5px;
            border-right-width: 4px;
            padding: 14px 25px;
            border-radius: 16px;
            cursor: pointer;
            font-family: 'Hanken Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            transition: transform 0.12s ease;
        }

        .submit-button:active {
            transform: translateY(2px);
        }

        .submit-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .footer {
            margin-top: 36px;
            text-align: center;
        }

        .mascot-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
        }

        .mascot-image {
            width: 74px;
            height: auto;
        }

        .app-promo {
            text-align: left;
        }

        .app-promo p {
            font-family: 'Hanken Grotesk', sans-serif;
            font-weight: 600;
            color: var(--grey-soft);
            font-size: 0.9rem;
        }

        .store-badge {
            max-width: 150px;
            height: auto;
            margin-top: 8px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 12px 4px;
            }

            .asset-icon {
                width: 90px;
                right: -10px;
                top: -26px;
                transform: rotate(-8deg);
            }
        }

        .message-input:focus,
        .hint-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px var(--glow);
        }

        .question-card {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <h1 class="sr-only">shhask</h1>
    <div class="container" style="margin-top: 25px;">
        <header class="header">
            <img src="{{ asset('assets/shhask.png') }}" alt="shhask" class="logo-shhask">
        </header>

        <div class="card-container">
            <img class="asset-icon" src="{{ asset('images/' . $assetIcon . '.png') }}" alt="Sticker">
            <main class="question-card">
                <div class="profile-section">
                    <div id="avatar" class="profile-image">
                    </div>
                    <div class="profile-info">
                        <span class="username"><span>@</span>{{ $usernameUser }}</span>
                        <h2 class="question-text">{{ $title }}</h2>
                    </div>
                </div>

                <form class="question-form" id="message-form">
                    <textarea class="message-input" maxlength="500" id="mensaje" placeholder="Envíame mensajes anónimos" rows="4"></textarea>

                    <div class="hint-section">
                        <p class="hint-text">Deja una pista! 💡</p>
                        <input type="text" maxlength="255" id="hint" class="hint-input"
                            placeholder="Deja una pista">
                        <button type="submit" id="boton-fachero" class="submit-button">Enviar</button>
                    </div>
                </form>
            </main>
        </div>

        <footer class="footer">
            <div class="mascot-container">
                <img src="{{ asset('assets/raccoon-2.png') }}" alt="Mascot" class="mascot-image">
                <div class="app-promo">
                    <p>Recibe preguntas anónimas!</p>
                    <a href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2" target="_blank" class="play-store-button">
                        <img src="{{ asset('assets/google-play-badge.png') }}" alt="Get it on Google Play"
                            class="store-badge">
                    </a>
                </div>
            </div>
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
                // Use SkinService for the skin SVG
                const skinSVG = SkinService.drawSVG({
                    skinColor: SkinColors[properties.SkinColor]?.svg || SkinColors.White.svg
                });

                // Use HairService for the hair SVG
                const hairStyle = HairStyles[properties.HairStyle] || HairStyles.Bald;
                const hairColor = HairColors[properties.HairColor]?.hexCode || HairColors.Black.hexCode;
                const hairSVG = HairService.drawSVG({
                    style: {
                        ...hairStyle,
                        svg: hairStyle?.svg.replaceAll('$TO_REPLACE_WITH_HAIRS_COLOR', hairColor),
                    },
                    color: {
                        hexCode: hairColor
                    },
                });

                // Use FacialHairService for the facial hair SVG
                const facialHairType = FacialHair[properties.FacialHairType];
                const facialHairColor = FacialHairColors[properties.FacialHairColor]?.hexCode || FacialHairColors.Black
                    .hexCode;
                const facialHairSVG = FacialHairService.drawSVG({
                    style: {
                        ...facialHairType,
                        svg: facialHairType?.svg.replaceAll('$TO_REPLACE_WITH_FACIAL_HAIRS_COLOR',
                            facialHairColor),
                    },
                    color: {
                        hexCode: facialHairColor
                    },
                });

                // Use EyesService for the eyes SVG
                const eyeStyle = Eyes[properties.EyeType]?.svg || '';
                const eyeSVG = EyesService.drawSVG({
                    eye: eyeStyle
                });

                // Use EyebrowService for the eye brow SVG
                const eyeBrowType = Eyebrows[properties.EyeBrowType]?.svg || '';
                const eyeBrowSVG = EyesBrowsService.drawSVG({
                    eyebrowType: eyeBrowType
                });

                // Use MouthService for the mouth SVG
                const mouthType = Mouths[properties.MouthType]?.svg || '';
                const mouthSVG = MouthsService.drawSVG({
                    mouthType: mouthType
                });

                // Use OutfitsService for the hair SVG
                const outfitStyle = Outfits[properties.OutfitType];
                const outfitColor = OutfitColors[properties.OutfitColor]?.hexCode || OutfitColors.Black.hexCode;
                const outfitSVG = OutfitsService.drawSVG({
                    style: {
                        ...outfitStyle,
                        svg: outfitStyle?.svg.replaceAll('$TO_REPLACE_WITH_OUTFIT_COLOR', outfitColor),
                    },
                    color: {
                        hexCode: outfitColor
                    },
                });

                // Use NoseService for the nose SVG
                const noseType = Noses[properties.NoseType]?.svg || '';
                const noseSVG = NosesService.drawSVG({
                    noseType: noseType
                });

                // Use AccessoriesService for the accessories SVG
                const accessorieType = Accessories[properties.Accessory]?.svg || '';
                const accessoriesSVG = AccessoriesService.drawSVG({
                    accessorieType: accessorieType
                });

                return `
                <svg width="264px" height="280px" viewBox="0 0 264 280" xmlns="http://www.w3.org/2000/svg">
                    <g>
                        ${skinSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${mouthSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${noseSVG}
                    </g>
                    <g transform="translate(9.5, 2)">
                        ${facialHairSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${eyeSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${eyeBrowSVG}
                    </g>

                    <g transform="translate(10, 0)">
                        ${hairSVG}
                    </g>

                    <g transform="translate(28, 100)">
                        ${outfitSVG}
                    </g>
                    <g transform="translate(75, 80)">
                        ${accessoriesSVG}
                    </g>
                </svg>
            `;
            };

            const renderAvatar = () => {
                const properties = parseURLParams();

                // Validate properties
                if (!HairStyles[properties.HairStyle]) {
                    console.warn(`Invalid hairStyle: ${properties.HairStyle}`);
                    properties.HairStyle = 'Bald';
                }
                if (!HairColors[properties.HairColor]) {
                    console.warn(`Invalid hairColor: ${properties.HairColor}`);
                    properties.HairColor = 'Black';
                }
                if (!SkinColors[properties.SkinColor]) {
                    console.warn(`Invalid skinColor: ${properties.SkinColor}`);
                    properties.SkinColor = 'White';
                }
                if (!FacialHair[properties.FacialHairType]) {
                    console.warn(`Invalid facialHairType: ${properties.FacialHairType}`);
                    properties.FacialHairType = 'Nothing';
                }
                if (!FacialHairColors[properties.FacialHairColor]) {
                    console.warn(`Invalid facialHairColor: ${properties.FacialHairColor}`);
                    properties.FacialHairColor = 'Black';
                }
                if (!Eyes[properties.EyeType]) {
                    console.warn(`Invalid eyeType: ${properties.EyeType}`);
                    properties.EyeType = 'Default';
                }
                if (!Eyebrows[properties.EyeBrowType]) {
                    console.warn(`Invalid eyeBrowType: ${properties.EyeBrowType}`);
                    properties.EyeBrowType = 'Default';
                }
                if (!Mouths[properties.MouthType]) {
                    console.warn(`Invalid mouthType: ${properties.MouthType}`);
                    properties.MouthType = 'Default';
                }
                if (!Outfits[properties.OutfitType]) {
                    console.warn(`Invalid outfitType: ${properties.OutfitType}`);
                    properties.OutfitType = 'BlazerTShirt';
                }
                if (!OutfitColors[properties.OutfitColor]) {
                    console.warn(`Invalid outfitColor: ${properties.OutfitColor}`);
                    properties.OutfitColor = 'Black';
                }
                if (!Noses[properties.NoseType]) {
                    console.warn(`Invalid noseType: ${properties.NoseType}`);
                    properties.NoseType = 'Default';
                }
                if (!Accessories[properties.Accessory]) {
                    console.warn(`Invalid accessoriesType: ${properties.Accessory}`);
                    properties.Accessory = 'Nothing';
                }

                const avatarElement = document.getElementById('avatar');
                avatarElement.innerHTML = drawSVG(properties);
            };

            // Initialize
            renderAvatar();
        </script>
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#boton-fachero').on('click', function(event) {
                event.preventDefault();
                var mensaje = $('#mensaje').val();
                var hint = $('#hint').val();
                var username = @json($usernameUser);
                if (mensaje === null || mensaje === '') {
                    Swal.fire({
                        title: "🖊️Escribe algo para poder enviar el mensaje🤗",
                        width: 600,
                        padding: "3em",
                        color: "#000000",
                        backdrop: `rgba(0,0,0,0.5)`,
                        confirmButtonColor: "#FF6A13"
                    });
                    return;
                }
                Swal.fire({
                    title: "Enviando mensaje anónimo😁",
                    icon: "success",
                    iconColor: "#FF6A13",
                    draggable: true,
                    timer: 5000,
                    timerProgressBar: true,
                    backdrop: 'rgba(0,0,0,0.5)',
                    color: "#000000",
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    willClose: () => {}
                });

                $('#mensaje').val('');
                $('#hint').val('');

                $.ajax({
                    type: 'POST',
                    url: 'question/create-web',
                    data: {
                        id_post: {{ $idPost }},
                        text: mensaje,
                        hint: hint
                    },
                    success: function(data) {
                        $.ajax({
                            type: 'POST',
                            url: '/pushNotification',
                            data: {
                                postId: {{ $idPost }},
                                username: username,
                                text: mensaje
                            },
                            success: function(notificationData) {
                                return;
                            },
                            error: function(xhr, status, error) {
                                return;
                            }
                        });
                        Swal.fire({
                            title: "Mensaje enviado😉 Shhh🤫!",
                            width: 600,
                            padding: "3em",
                            color: "#000000",
                            backdrop: 'rgba(0,0,0,0.5)',
                            confirmButtonColor: "#FF6A13"
                        });
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 429) {
                            Swal.fire({
                                title: "Debes esperar un momento para volver a mandar otro mensaje🤗",
                                width: 600,
                                padding: "3em",
                                color: "#000000",
                                backdrop: `rgba(0,0,0,0.5)`,
                                confirmButtonColor: "#FF6A13"
                            });

                            var $button = $('#boton-fachero');
                            var countdown = 30;

                            $button.prop('disabled', true).text('Espera... ' + countdown + 's');

                            var timer = setInterval(function() {
                                countdown--;
                                $button.text('Espera... ' + countdown + 's');

                                if (countdown <= 0) {
                                    clearInterval(timer);
                                    $button.prop('disabled', false).text('Enviar');
                                }
                            }, 1000);
                        } else if (xhr.status === 423) { //Usuario bloqueado
                            Swal.fire({
                                title: "Se envió el mensaje anónimo😁, Shhh🤫!",
                                width: 600,
                                padding: "3em",
                                color: "#000000",
                                backdrop: `rgba(0,0,0,0.5)`,
                                confirmButtonColor: "#FF6A13"
                            });
                            $('#mensaje').val('');
                            $('#hint').val('');
                        } else {
                            Swal.fire({
                                title: "Ups, parece que algo no está bien!😥",
                                width: 600,
                                padding: "3em",
                                color: "#000000",
                                backdrop: `rgba(229,57,53,0.35)`,
                                confirmButtonColor: "#E53935"
                            });
                        }
                    }
                });
            });
        </script>
</body>

</html>
