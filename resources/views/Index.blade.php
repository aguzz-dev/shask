<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/Logo.ico') }}" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Meta tag de CSRF -->
    <title>Shhask!</title>
</head>

<body>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');

        * {
            user-select: none;
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
        }

        header img {
            padding: 1em;
            padding-left: 2vw;
            margin: 0;
            width: 20em;
            object-fit: contain;
        }

        body {
            background: linear-gradient(to bottom, #D2C8FF, #A1A2EA);

            font-family: "Roboto", sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            height: auto;
            min-height: 100vh;
            margin: 0;
            padding: 0 10vw;
        }

        /* SECCION 1 */

        .input-otter-c {
            margin-top: 20vh;
            display: flex;
            flex-direction: row-reverse;
        }

        #card {
            width: 80vw;
            max-width: 800px;
            height: 25vh;
            border-radius: 20px;
            overflow: hidden;
            color: white;
            background-color: #2D2D2D;
            z-index: 2;
        }

        .user-info-section {
            background-color: #624e95;
            display: flex;
            user-select: none;
        }

        .profile-picture-c {
            width: 3em;
            height: 3em;
            padding: 0.6em;
            margin: 0.7em;
            background: linear-gradient(to bottom, #FFF1E6, #CDDAFD);
            border-radius: 100%;
            overflow: hidden;
        }

        .profile-picture-c img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            -webkit-user-drag: none;
        }

        .user-info-c p {
            padding: 0;
            margin: 0;
        }

        .user-info-c p:first-of-type {
            font-weight: 300;
        }

        .user-info-c p {
            font-weight: 500;
        }

        .user-info-c {
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            padding: 0.6em 0;
        }

        .input-c {
            width: 100%;
            height: 100%;
        }

        textarea,
        #hint {
            font-size: 15px;
            font-family: "Roboto", sans-serif;
        }

        .input-c textarea {
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            padding: 1em;
            margin: 0;
            line-height: 1.5;
            vertical-align: top;
        }

        .hint-c {
            width: 80vw;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 1em;
            gap: 1em;
            text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.6);
        }

        .hint-c p {
            font-weight: 500;
            margin: 0;
            color: white;
        }

        #hint {
            width: 100%;
            max-height: 1.3em;
            border: none;
            outline: none;
            border-radius: 20px;
            padding: 1em;
            resize: none;
            vertical-align: middle;
        }

        .card-otter-c {
            z-index: -1;
            margin-left: -5em;
            transform: rotate(45deg);
        }

        .card-otter-c img {
            -webkit-user-drag: none;
            width: 10em;
        }

        .button {
            --white: #ffe7ff;
            --bg: #080808;
            --radius: 100px;
            scale: 0.7;
            outline: none;
            cursor: pointer;
            border: 0;
            position: relative;
            border-radius: var(--radius);
            background-color: var(--bg);
            transition: all 0.2s ease;
            box-shadow:
                inset 0 0.3rem 0.9rem rgba(195,189,249, 0.3),
                inset 0 -0.1rem 0.3rem rgba(0, 0, 0, 0.7),
                inset 0 -0.4rem 0.9rem rgba(195,189,249, 0.5),
                0 3rem 3rem rgba(0, 0, 0, 0.3),
                0 1rem 1rem -0.6rem rgba(0, 0, 0, 0.8);
        }
        .button .wrap {
            font-size: 25px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            padding: 32px 45px;
            border-radius: inherit;
            position: relative;
            overflow: hidden;
        }
        .button .wrap p span:nth-child(2) {
            display: none;
        }
        .button:hover .wrap p span:nth-child(1) {
            display: none;
        }
        .button:hover .wrap p span:nth-child(2) {
            display: inline-block;
        }
        .button .wrap p {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            transition: all 0.2s ease;
            transform: translateY(2%);
            mask-image: linear-gradient(to bottom, white 40%, transparent);
        }
        .button .wrap::before,
        .button .wrap::after {
            content: "";
            position: absolute;
            transition: all 0.3s ease;
        }
        .button .wrap::before {
            left: -15%;
            right: -15%;
            bottom: 25%;
            top: -100%;
            border-radius: 50%;
            background-color: rgba(195,189,249, 0.12);
        }
        .button .wrap::after {
            left: 6%;
            right: 6%;
            top: 12%;
            bottom: 40%;
            border-radius: 22px 22px 0 0;
            box-shadow: inset 0 10px 8px -10px rgba(201,193,251, 0.8);
            background: linear-gradient(
                180deg,
                rgba(201,193,251, 0.3) 0%,
                rgba(0, 0, 0, 0) 50%,
                rgba(0, 0, 0, 0) 100%
            );
        }
        .button:hover {
            box-shadow:
                inset 0 0.3rem 0.5rem rgba(172,152,241, 0.4),
                inset 0 -0.1rem 0.3rem rgba(0, 0, 0, 0.7),
                inset 0 -0.4rem 0.9rem rgba(172,152,241, 0.7),
                0 3rem 3rem rgba(0, 0, 0, 0.3),
                0 1rem 1rem -0.6rem rgba(0, 0, 0, 0.8);
        }
        .button:hover .wrap::before {
            transform: translateY(-5%);
        }
        .button:hover .wrap::after {
            opacity: 0.4;
            transform: translateY(5%);
        }
        .button:hover .wrap p {
            transform: translateY(-4%);
        }
        .button:active {
            transform: translateY(4px);
            box-shadow:
                inset 0 0.3rem 0.5rem rgba(172,152,241, 0.5),
                inset 0 -0.1rem 0.3rem rgba(0, 0, 0, 0.8),
                inset 0 -0.4rem 0.9rem rgba(172,152,241, 0.4),
                0 3rem 3rem rgba(0, 0, 0, 0.3),
                0 1rem 1rem -0.6rem rgba(0, 0, 0, 0.8);
        }

        @media (max-width: 600px) {
            .input-otter-c {
                flex-direction: column;
                margin: 0;
                margin-top: 5vh;
            }

            .input-c textarea {
                min-height: 0;
            }

            .card-otter-c {
                margin-left: 30%;
                z-index: -1;
                transform: translateY(5vh);
            }

            body {
                justify-content: flex-start;
            }

            #card {
                height: calc(20vh + 50px);
            }

            header img {
                width: 8em;
            }
        }

        /* SECCION 2 */

        .download-c {
            margin-top: 2em;
            margin-bottom: 2em;
            display: flex;
            align-items: flex-start;
        }

        #otter-2 {
            width: 10em;
            object-fit: contain;
            z-index: -1;
        }

        .download-message-g {
            background-color: white;
            padding: 0.5em;
            border-radius: 20px;
            max-width: 40vw;
        }

        .download-message-g p {
            font-weight: 300;
        }

        .bubble-c {
            display: flex;
            flex-direction: column;
            width: 10em;
        }

        .shops-c {
            width: 100%;
            height: auto;
            overflow: hidden;
            transition: all .3s ease;
            cursor: pointer;
        }

        #playstore {
            margin-top: 1em;
            width: 100%;
            object-fit: cover;
        }

        .shops-c:hover{
            scale: 1.07;
        }

        .shops-c{
            cursor: pointer;
            transition: all .3s ease;
        }
        .shops-c:hover{
            scale: 1.07;
        }

        @media (max-width: 700px) {
            .download-c {
                scale: 0.6;
                position: relative;
                top: -60px;
                left: -50px;
            }
        }

        @media (max-width: 400px) {
            .input-otter-c {
                position: relative;
                scale: 0.9;
            }
            .hint-c {
                position: relative;
                scale: 0.9;
            }
            .download-c {
                scale: 0.6;
                position: relative;
                top: -60px;
                left: -50px;
            }
        }

    </style>

    <main>
        <img style="width: 210px; position: relative; top: 85px;" src="{{ asset('assets/shhask.png') }}" alt="Logotipo Shhask" class="logo" id="logo-sd">
        <section class="input-otter-c">
            <div class="card-otter-c">
                <img src="{{ asset('assets/raccoon-1.png') }}" alt="Otter">
            </div>
            <article id="card">
                <div class="user-info-section">
                    <div class="profile-picture-c">
                        <img src='https://avataaars.io/?{{$avatarUser}}'
                        />
                    </div>
                    <div class="user-info-c">
                        <p>
                            {{$fullNameUser}} (<span>@</span>{{$usernameUser}})
                        </p>
                        <p style="font-size: 25px;">
                            {{$title}}
                        </p>
                    </div>
                </div>
                <div class="input-c">
                    <textarea id="mensaje" maxlength="80" placeholder="Enviame mensajes anónimos"></textarea>
                </div>
            </article>
        </section>

        <div class="hint-c">
            <p>Deja una pista! 💡</p>
            <textarea type="text" name="hint" id="hint" placeholder="Deja una pista"></textarea>
            <button class="button" id="boton-fachero" type="submit">
              <div class="wrap">
                <p>
                  <span>✧</span>
                  <span style="color: #ac98f1;">✦</span>
                  Enviar
                </p>
              </div>
            </button>
        </div>
    </main>

    <section class="download-c">
        <img src="{{ asset('assets/raccoon-2.png') }}" id="otter-2" alt="Otter">
        <div class="bubble-c">
            <div class="download-message-g">
                <p>Recibe preguntas anónimas!</p>
            </div>
            <div class="shops-c" style="width: 300px">
                <img src="{{ asset('assets/google-play-badge.png') }}" alt="Google Play Badge">
            </div>
        </div>
    </section>

    <script>
        // Configurar el token CSRF en las solicitudes AJAX
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
                    backdrop: `
                        rgba(0,0,123,0.4)
                    `
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
                        backdrop: `
                            rgba(0,0,123,0.4)
                        `
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
                            backdrop: `
                                rgba(0,0,123,0.4)
                            `
                        });
                    } else {
                        Swal.fire({
                            title: "Ups, parece que algo no está bien!😥",
                            width: 600,
                            padding: "3em",
                            color: "#E63F3C",
                            backdrop: `
                                rgba(230,63,60,0.4)
                            `
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>
