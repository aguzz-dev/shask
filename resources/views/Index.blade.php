<?php
use App\Models\PublicPost;
$idPost = (new PublicPost)->getPostIdByUrl($uri);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="app/views/assets/shhask.ico">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Shhask!</title>
    <link rel="stylesheet" href="app/views/css/style.css">
</head>

<body>
    <style>
        .shops-c{
            cursor: pointer;
            transition: all .3s ease;
        }
        .shops-c:hover{
            scale: 1.07;
        }
    </style>
    <header>
        <img style="width: 210px; position:absolute;" src="app/views/assets/logotipo-sd.png" alt="Logotipo Shhask">
    </header>
    <main>
        <section class="input-otter-c">
            <div class="card-otter-c">
                <img src="app/views/assets/raccoon-1.png" alt="Otter">
            </div>
            <article id="card">
                <div class="user-info-section">
                    <div class="profile-picture-c">
                        <img src="https://api.dicebear.com/7.x/pixel-art/png" alt="Profile Picture">
                    </div>
                    <div class="user-info-c">
                        <p>
                            @_User
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
        <img src="app/views/assets/raccoon-2.png" id="otter-2" alt="Otter">
        <div class="bubble-c">
            <div class="download-message-g">
                <p>Recibe preguntas anónimas!</p>
            </div>
            <div class="shops-c">
                <img id="playstore" src="app/views/assets/google-play-badge.png" alt="Download on Google Play">
            </div>
        </div>
    </section>
    <script>
        $('#boton-fachero').on('click', function() {
            event.preventDefault();
            var mensaje = $('#mensaje').val();
            var hint = $('#hint').val();
            if (mensaje == null || mensaje == '') {
                Swal.fire("🖊️Escribe algo para poder enviar el mensaje🤗");
                return;
            }
            $.ajax({
                type: 'POST',
                url: 'question/create-web',
                data: {
                    id_post: '<?php echo $idPost ?>',
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
                    return;
                }
            });
        });
    </script>
</body>

</html>
