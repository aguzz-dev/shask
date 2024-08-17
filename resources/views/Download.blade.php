<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{asset('css/download.css')}}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <title>Descarga Shhask!</title>
</head>
<style>
    .playstore {
        transition: all 0.3s ease;
        position: relative;
    }
    .playstore:hover{
        scale: 1.1;
    }
</style>
<body>
    <img style="width: 300px;" src="{{asset('assets/logotipo-sd.png')}}" alt="" class="logo">
    <div style="margin-bottom: 10px;"class="download">Descargá <b>Shhask!</b></div>
    <div class="container">
        <img src="{{asset('assets/raccoon-1.png')}}" style="width: 430px;" alt="mascota-shhask" class="raccoon1">
        <img src="{{asset('assets/google-play-badge.png')}}" alt="" class="playstore">
        <img src="{{asset('assets/raccoon-2.png')}}" alt="mascota-shhask2" class="raccoon2">
    </div>
</body>
</html>
