<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background-color: #ef4444;
            font-family: Arial, sans-serif;
            padding: 1rem;
            overflow: hidden;
            position: relative;
        }

        .error-container {
            text-align: center;
            color: white;
            margin-top: 10vh;
        }

        .error-code {
            font-size: clamp(8rem, 20vw, 16rem);
            font-weight: bold;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .oops {
            font-size: clamp(1.5rem, 4vw, 2rem);
            margin-bottom: 0.5rem;
        }

        .message {
            font-size: clamp(2rem, 5vw, 2.5rem);
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .home-button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: transparent;
            border: 2px solid white;
            color: white;
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .home-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .image-container {
            position: fixed;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: min(400px, 80%);
            max-width: 100%;
            display: flex;
            justify-content: center;
        }

        .image-container img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-code">404</div>
    <div class="oops">¡Oops!</div>
    <div class="message">Página no encontrada</div>
    <a href="{{ url('/') }}" class="home-button">Volver al Inicio</a>
</div>
<div class="image-container">
    <img src="{{asset("assets/404W.webp")}}" alt="Mascota mapache">
</div>
</body>
</html>
