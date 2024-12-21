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
            width: 200px;
            right: -115px;
            top: -60px;
            z-index: 2;
            transform: rotate(-12deg);
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

        .profile-image {
            width: 40px;
            height: 40px;
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
            background: var(--color-2);
            color: #131215;
            border: 2px solid var(--color-3);
            padding: 10px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .submit-button:hover {
            background: var(--negro);
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
            html, body {
                overflow: hidden;
                height: 100vh;
            }

            .container {
                padding: 10px;
            }

            .asset-icon{
                scale: 0.5;
                right: -80px;
                top: -70px;
            }
        }

        .message-input:focus,
        .hint-input:focus {
            outline: none;
            border-color: var(--color-2);
            box-shadow: 0 0 0 2px rgba(144, 238, 144, 0.2);
        }

        .question-card {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            <img class="asset-icon" src="{{ asset('images/'.$assetIcon.'.png') }}" alt="Snake">
            <main class="question-card">
                <div class="profile-section">
                    <img src="{{$avatarUser}}" alt="Profile" class="profile-image">
                    <div class="profile-info">
                        <span class="username"><span>@</span>{{$usernameUser}}</span>
                        <h2 class="question-text">{{$title}}</h2>
                    </div>
                </div>

                <form class="question-form" id="message-form">
                    <textarea
                        class="message-input"
                        id="mensaje"
                        placeholder="Envíame mensajes anónimos"
                        rows="4"></textarea>

                    <div class="hint-section">
                        <p class="hint-text">Deja una pista! 💡</p>
                        <input type="text" id="hint" class="hint-input" placeholder="Deja una pista">
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
                        <img src="{{ asset('assets/google-play-badge.png') }}" alt="Get it on Google Play" class="store-badge">
                    </a>
                </div>
            </div>
        </footer>
    </div>

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

            $.ajax({
                type: 'POST',
                url: 'question/create-web',
                data: {
                    id_post: {{$idPost}},
                    text: mensaje,
                    hint: hint
                },
                success: function(data) {
                    Swal.fire({
                        title: "Se envió el mensaje anónimo😁, Shhh🤫!",
                        width: 600,
                        padding: "3em",
                        color: "#716add",
                        backdrop: `rgba(0,0,123,0.4)`
                    });
                    $('#mensaje').val('');
                    $('#hint').val('');
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 429) {
                        Swal.fire({
                            title: "Debes esperar un momento para volver a mandar otra mensaje🤗",
                            width: 600,
                            padding: "3em",
                            color: "#716add",
                            backdrop: `rgba(0,0,123,0.4)`
                        });
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
