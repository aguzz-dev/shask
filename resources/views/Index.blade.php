<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/shhask-icono.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shhask!</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');

        :root {
            --color-boton-card: #131215;
            --color-1: rgba({{ isset($colors[0]) ? implode(',', $colors[0]) : '192,192,209,0.5' }});
            --color-2: rgba({{ isset($colors[1]) ? implode(',', $colors[1]) : '192,192,209,0.9' }});
            --color-3: rgba({{ isset($colors[2]) ? implode(',', $colors[2]) : '192,192,209,0.9' }});
            --negro: #131215;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(to bottom, var(--color-1), var(--color-2));
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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }

        .header {
            text-align: center;
            padding: 20px 0;
        }

        .logo-shhask {
            width: 60%;
        }

        .card-container {
            position: relative;
            margin: 20px 0;
        }

        .asset-icon {
            position: absolute;
            width: 120px;
            right: -50px;
            top: -40px;
            z-index: 2;
            transform: rotate(-7deg);
        }

        .question-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }

        .profile-section {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .profile-image svg {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(to bottom, #FFF1E6, #CDDAFD);
        }

        .profile-info {
            flex-grow: 1;
        }

        .username {
            color: #666;
            font-size: 0.9rem;
        }

        .question-text {
            font-size: 1.2rem;
            margin: 5px 0;
            word-wrap: break-word;
        }

        .message-input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            resize: none;
            margin-bottom: 15px;
            font-family: inherit;
        }

        .hint-section {
            text-align: center;
        }

        .hint-text {
            color: #666;
            margin-bottom: 10px;
        }

        .hint-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .submit-button {
            background: var(--color-1);
            color: white;
            border: 2px solid var(--color-3);
            padding: 10px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .submit-button:hover {
            background: var(--color-2);
            color: #fff;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
        }

        .mascot-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .mascot-image {
            width: 80px;
            height: auto;
        }

        .app-promo {
            text-align: left;
        }

        .store-badge {
            max-width: 160px;
            height: auto;
            margin-top: 10px;
        }

        @media (max-width: 480px) {

            html,
            body {
                overflow: hidden;
                height: 100vh;
            }

            .container {
                padding: 10px;
            }

            .asset-icon {
                position: absolute;
                width: 200px;
                right: -115px;
                top: -60px;
                z-index: 2;
                transform: rotate(-12deg);
                scale: 0.5;
                right: -80px;
                top: -70px;
            }
        }

        .message-input:focus,
        .hint-input:focus {
            outline: none;
            border-color: var(--color-2);
            box-shadow: 0 0 0 2px rgba(170, 170, 170, 0.2);
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
            <img class="asset-icon" src="{{ asset('images/' . $assetIcon . '.png') }}" alt="Snake">
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
                    <textarea class="message-input" maxlength="400" id="mensaje" placeholder="Envíame mensajes anónimos" rows="4"></textarea>

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
                    <a href="#" class="play-store-button">
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
            console.log(avatarUserData);
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
                        color: "#716add",
                        backdrop: `rgba(0,0,123,0.4)`
                    });
                    return;
                }
                Swal.fire({
                    title: "Enviando mensaje anónimo😁",
                    icon: "success",
                    draggable: true,
                    timer: 5000,
                    timerProgressBar: true,
                    backdrop: 'rgba(0,0,123,0.4)',
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
                            color: "#716add",
                            backdrop: 'rgba(0,0,123,0.4)',
                        });
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 429) {
                            Swal.fire({
                                title: "Debes esperar un momento para volver a mandar otro mensaje🤗",
                                width: 600,
                                padding: "3em",
                                color: "#716add",
                                backdrop: `rgba(0,0,123,0.4)`
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
                                color: "#716add",
                                backdrop: `rgba(0,0,123,0.4)`
                            });
                            $('#mensaje').val('');
                            $('#hint').val('');
                        } else {
                            Swal.fire({
                                title: "Ups, parece que algo no está bien!😥",
                                width: 600,
                                padding: "3em",
                                color: "#E63F3C",
                                backdrop: `rgba(230,63,60,0.4)`
                            });
                        }
                    }
                });
            });
        </script>
</body>

</html>
