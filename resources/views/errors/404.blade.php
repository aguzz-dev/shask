<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <link rel="stylesheet" href="app/views/css/404.css">
</head>

<body>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');

        * {
            user-select: none;
        }


        body {
            background: linear-gradient(to bottom, #ec4141, #c5321f);
            color: white;
            font-family: "Roboto", sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: auto;
            min-height: 100vh;
            margin: 0;
        }

        section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: -7em;
        }

        h2 {
            font-size: 15em;
            margin: 0;
        }

        .image-c {
            height: 11vw;
            overflow: hidden;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        img {
            -webkit-user-drag: none;
        }

        p {
            font-size: 2em;
        }

        h1 {
            font-size: 3em;
        }

        p,
        h1 {
            margin: 0;
        }

        .boton-fachero {
            margin-top: 3em;
            background-color: transparent;
            border: 2px solid white;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .boton-fachero:hover {
            background-color: rgb(141, 0, 0);
        }
    </style>
    <section>
        <h2>404</h2>
        <p>Oops!</p>
        <h1>Page Not Found</h1>
        <div>
            <div class="image-c">
                <img src="{{asset('assets/raccoon-1.png')}}" alt="Raccoon">
            </div>
            <button type="button" class="boton-fachero" id="backToHomeButton">Back To Home</button>

            <script>
                document.getElementById('backToHomeButton').onclick = function() {
                    window.location.href = '/login';
                };
            </script>

        </div>
    </section>
</body>

</html>
