<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Este buzón cerró 🤫</title>
    <style>
        body { font-family: sans-serif; background: #ECE8E1; display: flex; flex-direction: column;
               align-items: center; justify-content: center; min-height: 100vh; margin: 0; text-align: center; }
        h1 { font-size: 28px; }
        .card { background: #fff; border: 2px solid #000; border-bottom-width: 5px; border-right-width: 4px;
                border-radius: 20px; padding: 32px 24px; max-width: 340px; }
        a.cta { display: inline-block; margin-top: 18px; background: #FF6A13; color: #fff; font-weight: bold;
                text-decoration: none; padding: 14px 22px; border-radius: 14px; border: 2px solid #000;
                border-bottom-width: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>El buzón de {{ '@' . $usernameUser }} cerró 🤫</h1>
        <p>"{{ $title }}" ya no recibe mensajes.<br>Creá el tuyo y empezá a recibir preguntas anónimas.</p>
        <a class="cta" href="/descarga">Descargar Shhask</a>
    </div>
</body>
</html>
