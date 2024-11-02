<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="app/views/assets/shhask.ico">
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
            background: linear-gradient(to bottom, #5187ad, #ADD8E6);

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
            background-color: #1D1D1D;
            display: flex;
            user-select: none;
        }

        .profile-picture-c {
            width: 3em;
            height: 3em;
            padding: 0.6em;
            margin: 0.7em;
            background-color: black;
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

        #boton-fachero {
            background-color: #1b1b1b;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 12px;
            transition: background-color 0.3s, transform 0.3s;
            z-index: 1000;
        }

        #boton-fachero:hover {
            background-color: #000000;
            transform: scale(1.05);
        }

        #boton-fachero:active {
            background-color: #000000;
            transform: scale(1);
        }

        #boton-fachero:focus {
            outline: none;
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

            .logo{
                position: relative;
                scale: 0.7;
                top: -10px;
                left: -22px;
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

            .logo{
                position: relative;
                scale: 0.7;
                top: -10px;
                left: -22px;
            }
        }

    </style>

    <header>
        <img style="width: 210px; position:absolute;" src="{{ asset('assets/logotipo-sd.png') }}" alt="Logotipo Shhask" class="logo" id="logo-sd"> <img style="width: 210px; position:absolute;">
    </header>

    <main>
        <section class="input-otter-c">
            <div class="card-otter-c">
                <img src="{{ asset('assets/raccoon-1.png') }}" alt="Otter">
            </div>
            <article id="card">
                <div class="user-info-section">
                    <div class="profile-picture-c">
                        <img src="https://api.dicebear.com/7.x/pixel-art/png" alt="Profile Picture">
                    </div>
                    <div class="user-info-c">
                        <p>
                            {{$fullNameUser}} ({{$usernameUser}})
                        </p>
                        <p>
                            hazme una pregunta
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
            <textarea type="text" name="hint" id="hint" placeholder="Deja una pista si quieres"></textarea>
            <button id="boton-fachero" type="submit">Enviar</button>
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
                Swal.fire("🖊️Escribe algo para poder enviar el mensaje🤗");
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
                    Swal.fire("Se envió el mensaje anónimo😁, Shhh🤫!");
                    $('#mensaje').val('');
                    $('#hint').val('');
                },
                error: function(xhr, status, error) {
                    Swal.fire("Ups, parece que algo no está bien!😥");
                }
            });
        });
    </script>
</body>

</html>
