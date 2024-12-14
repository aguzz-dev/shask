<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/Logo.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <title>Descarga Shhask!</title>
</head>
<style>
    body{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100vh;
        overflow: hidden;
        background: linear-gradient(to bottom, #D2C8FF, #A1A2EA);
    }

    .container{
        margin-top: 10vh;
        display: flex;
        flex-direction: row-reverse;
        justify-content: space-between;
        align-items: center;
    }

    b{
        font-family: 'Bebas Neue', sans-serif;
        font-size: 60px;
        color: white;
        letter-spacing: 3px;
        position: relative;
        top: 180px;
        left: -15px;
    }

    .logo{
        position: relative;
        top: 220px;
        left: -15px;
        width: 300px;

    }
    .playstore {
        transition: all 0.3s ease;
        position: relative;
    }
    .playstore:hover{
        scale: 1.1;
    }
</style>
<body>
    <img src="{{asset('assets/shhask.png')}}" alt="" class="logo">
    <div class="container">
        <img src="{{asset('assets/raccoon-1.png')}}" style="width: 430px;" alt="mascota-shhask" class="raccoon1">
        <img src="{{asset('assets/google-play-badge.png')}}" alt="" class="playstore">
        <img src="{{asset('assets/raccoon-2.png')}}" alt="mascota-shhask2" class="raccoon2">
    </div>
</body>
</html>
