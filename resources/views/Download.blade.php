<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Descubre Shhask, la mejor plataforma para mensajes anónimos. Disponible ahora en Google Play.">
    <meta name="keywords" content="mensajes anónimos, Shhask, enviar mensajes, Google Play, mensajes divertidos, app social">
    <meta property="og:title" content="Shhask - Envía y Recibe Mensajes Anónimos">
    <meta property="og:description" content="Explora Shhask, una plataforma para mensajes anónimos, disponible en Google Play. ¡Conéctate de forma divertida y segura!">
    <meta property="og:image" content="{{asset('assets/logotipo-sd.png')}}">
    <meta property="og:url" content="www.shhask.com">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Shhask - Envía y Recibe Mensajes Anónimos">
    <meta name="twitter:description" content="Explora Shhask, disponible en Google Play. ¡Conéctate de forma divertida y segura!">
    <meta name="twitter:image" content="{{asset('assets/logotipo-sd.png')}}">
    <title>Shhask - Mensajes Anónimos Divertidos y Seguros</title>
    <style>
        * {
            user-select: none;
            pointer-events: none;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #8b75ce 0%, #9d89e0 50%, #7c67b5 100%);
            overflow-x: hidden;
            position: relative;
        }

        h1{
            position: absolute;
            opacity: 0;
        }

        .emoji-rain {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
        }

        .emoji {
            position: fixed;
            font-size: 20px;
            user-select: none;
            opacity: 0.3;
            animation: fall linear infinite;
            animation-timing-function: ease-in-out;
        }

        @keyframes fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
            }
        }

        .container {
            width: 100%;
            max-width: 600px;
            padding: 2rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .cards-container {
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            perspective: 1000px;
            z-index: 1000;
        }

        .card {
            pointer-events: auto;
            width: 140px;
            transition: all 0.3s ease-out;
            transform-origin: center center;
            cursor: pointer;
            position: relative;
            z-index: 1;
            will-change: transform;
        }

        .card.red { transform: rotate(20deg); }
        .card.pink { transform: rotate(2deg); }
        .card.green { transform: rotate(-14deg); }

        .card:hover {
            transform: scale(1.2) translateY(-20px) rotate(0deg) !important;
            z-index: 1100 !important;
            filter: brightness(1.1);
        }

        .card img {
            width: 100%;
            height: auto;
            animation: float 2s ease-in-out infinite;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            pointer-events: none;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .logo-container {
            position: relative;
            top: 70px;
            margin: 6rem auto 0.5rem;
            width: 400px;
            height: 400px;
        }

        .logo-fondo {
            position: absolute;
            width: 440px;
            height: 440px;
            object-fit: contain;
            opacity: 0.1;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) rotate(-6deg);
            transition: all 0.2s ease-out;
        }

        .logo-fondo.centered {
            transform: translate(-50%, -50%) rotate(0deg);
            opacity: 0.07;
        }

        .logo {
            pointer-events: auto;
            width: 400px;
            height: 400px;
            object-fit: contain;
            position: relative;
            z-index: 2;
        }

        #logoMain:hover {
            pointer-events: auto;
        }

        .title {
            font-size: 2.2rem;
            font-weight: bold;
            color: #f1f1f1;
            margin-bottom: 2rem;
            line-height: 1.4;
            background: linear-gradient(45deg, #fff, #e0e0ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 2s ease-in-out infinite alternate;
            position: relative;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 1px rgba(255, 255, 255, 0.2),
                0 0 3px rgba(255, 255, 255, 0.2),
                0 0 6px rgba(255, 255, 255, 0.2);
            }
            to {
                text-shadow: 0 0 2px rgba(255, 255, 255, 0.2),
                0 0 6px rgba(255, 255, 255, 0.2),
                0 0 9px rgba(255, 255, 255, 0.2);
            }
        }

        .download-section {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .mascot {
            width: 120px;
            height: 120px;
            object-fit: contain;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px) rotate(5deg); }
        }

        .play-store {
            pointer-events: auto;
            height: 80px;
            transition: transform 0.3s ease;
        }

        .play-store:hover {
            transform: scale(1.05);
        }

        @media (max-width: 480px) {

            .container {
                padding: 1rem;
            }

            .cards-container {
                transform: translateX(-50%) scale(0.7);
                top: -70px;
            }

            .logo-container {
                top: 7px;
                width: 280px;
                height: 280px;
            }

            .logo, .logo-fondo {
                width: 280px;
                height: 280px;
            }

            .title {
                top: -60px;
                font-size: 1.8rem;
                margin-bottom: 3rem;
            }

            .mascot {
                width: 100px;
                height: 100px;
            }

            .play-store {
                height: 60px;
            }

            .mascot, .play-store{
                position: relative;
                top: -35px;
            }
        }
    </style>
</head>
<body>
<div class="emoji-rain"></div>
<div class="container">
    <h1>shhask</h1>
    <div class="cards-container">
        <div class="card red">
            <img src="{{ asset('assets/muestra1.png') }}" alt="Tarjeta Roja">
        </div>
        <div class="card pink">
            <img src="{{ asset('assets/muestra2.png') }}" alt="Tarjeta Rosa">
        </div>
        <div class="card green">
            <img src="{{ asset('assets/muestra3.png') }}" alt="Tarjeta Verde">
        </div>
    </div>

    <div class="logo-container">
        <img src="{{ asset('assets/shhask.png') }}" alt="Logo Fondo" class="logo-fondo" id="logoFondo">
        <img src="{{ asset('assets/shhask.png') }}" alt="Logo" class="logo" id="logoMain" draggable="false">
    </div>

    <h2 class="title">¡Envia y recibe<br>mensajes anónimos!</h2>

    <div class="download-section">
        <img src="{{ asset('assets/raccoon-2.png') }}" alt="Mascota" class="mascot">
        <a href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2" target="_blank">
            <img src="https://play.google.com/intl/en_us/badges/static/images/badges/es_badge_web_generic.png"
                 alt="Descargar en Google Play"
                 class="play-store">
        </a>
    </div>
</div>

<script>
    // Manejo del logo
    document.getElementById('logoMain').addEventListener('mouseenter', function() {
        document.getElementById('logoFondo').classList.add('centered');
    });

    document.getElementById('logoMain').addEventListener('mouseleave', function() {
        document.getElementById('logoFondo').classList.remove('centered');
    });

    // Lluvia de emojis modificada
    const emojis = ['💌', '💭', '💬', '🗨️', '💜', '✨', '💫', '🌟'];
    const emojiContainer = document.querySelector('.emoji-rain');

    function createEmoji() {
        const emoji = document.createElement('div');
        emoji.className = 'emoji';
        emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];

        // Posición inicial aleatoria
        const startPosition = Math.random() * window.innerWidth;
        emoji.style.left = startPosition + 'px';

        // Duración más larga: entre 6 y 10 segundos
        const duration = Math.random() * 4 + 6;
        emoji.style.animationDuration = duration + 's';

        emojiContainer.appendChild(emoji);

        // Eliminar el emoji cuando termine la animación
        setTimeout(() => {
            emoji.remove();
        }, duration * 1000);
    }

    // Crear emojis con menos frecuencia (cada 800ms en lugar de 300ms)
    setInterval(createEmoji, 800);

    // Crear menos emojis iniciales
    for(let i = 0; i < 12; i++) {
        createEmoji();
    }
</script>
</body>
</html>
