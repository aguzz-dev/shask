<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Shhask: tu buzón de preguntas anónimas. Compartilo en tu story de Instagram y recibí lo que nunca te dirían de frente. Disponible en Google Play.">
    <meta name="keywords" content="mensajes anónimos, preguntas anónimas, Shhask, Instagram story, Google Play, app social">
    <meta property="og:title" content="Shhask - Preguntas anónimas, sin filtro">
    <meta property="og:description" content="Armá tu buzón, pegalo en tu story y dejá que te pregunten lo que nunca te dirían de frente. Gratis en Google Play.">
    <meta property="og:image" content="{{ asset('assets/shhask-logo-sticker.png') }}">
    <meta property="og:url" content="www.shhask.com">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Shhask - Preguntas anónimas, sin filtro">
    <meta name="twitter:description" content="Armá tu buzón, pegalo en tu story y dejá que te pregunten lo que nunca te dirían de frente.">
    <title>Shhask — preguntas anónimas, sin filtro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Londrina+Solid:wght@900&display=swap" rel="stylesheet">
<style>
  :root {
    --cream: #FBF4EC;
    --cream-card: #FEFEF2;
    --ink: #2b2b2b;
    --ink-soft: #676262;
    --orange: #FF6A13;
    --violet: #8667F2;
    --violet-deep: #270B8A;
    --violet-soft: #AC98F1;
    --peach: #FFF1E6;
    --blue-mist: #E9EFFF;
    --white: #ffffff;
    --border-thin: 1.4px;
    --border-thick: 4.5px;
    --tilt: 2.2deg;
    --container: 1140px;
  }

  * { box-sizing: border-box; }
  html { scroll-behavior: smooth; }

  body {
    margin: 0;
    background: var(--cream);
    color: var(--ink);
    font-family: 'Hanken Grotesk', -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
    position: relative;
  }

  h1, h2, h3 { font-family: 'Londrina Solid', cursive; font-weight: 900; margin: 0; text-wrap: balance; }
  p { margin: 0; }
  a { color: inherit; text-decoration: none; }
  img { max-width: 100%; display: block; }
  button { font-family: inherit; border: none; cursor: pointer; }

  .wrap { max-width: var(--container); margin: 0 auto; padding: 0 24px; position: relative; z-index: 2; }

  /* Ancla el mesh del hero a la altura real del hero (nav + header), no a
     todo el body — si no, "bottom:-160px" de un blob termina cerca del
     footer en vez de cerca del pie del hero. */
  .hero-shell { position: relative; }

  /* ══ atmosphere: drifting gradient mesh, no flat background ══ */
  .mesh { position: absolute; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
  .mesh span {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: .55;
    animation: drift 18s ease-in-out infinite;
    /* Offset de parallax inyectado por JS al hacer scroll — en margin-top
       (no transform) para no pisar la animación drift, que sí anima transform. */
    margin-top: var(--py, 0px);
  }
  .mesh .b1 { width: 520px; height: 520px; background: var(--violet-soft); top: -180px; left: -120px; animation-delay: 0s; }
  .mesh .b2 { width: 460px; height: 460px; background: var(--peach); top: -80px; right: -140px; animation-delay: 2s; }
  .mesh .b3 { width: 420px; height: 420px; background: var(--blue-mist); bottom: -160px; left: 30%; animation-delay: 4s; }
  .mesh .b4 { width: 360px; height: 360px; background: var(--orange); opacity: .18; top: 40%; right: 10%; animation-delay: 1s; }
  @keyframes drift {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -25px) scale(1.06); }
    66% { transform: translate(-24px, 20px) scale(0.97); }
  }

  /* ══ sparkle + confetti (invented decorative marks) ══ */
  .sparkle {
    position: absolute;
    width: 22px; height: 22px;
    background: var(--orange);
    clip-path: polygon(50% 0%, 61% 39%, 100% 50%, 61% 61%, 50% 100%, 39% 61%, 0% 50%, 39% 39%);
    animation: twinkle 3.2s ease-in-out infinite;
  }
  .sparkle.violet { background: var(--violet); }
  .sparkle.ink { background: var(--ink); }
  @keyframes twinkle {
    0%, 100% { transform: scale(0.8) rotate(0deg); opacity: .55; }
    50% { transform: scale(1.15) rotate(20deg); opacity: 1; }
  }

  .qmark {
    position: absolute;
    font-family: 'Londrina Solid', cursive;
    color: var(--white);
    -webkit-text-stroke: 2px var(--ink);
    animation: bob 5s ease-in-out infinite;
  }
  @keyframes bob {
    0%, 100% { transform: translateY(0) rotate(var(--r, 0deg)); }
    50% { transform: translateY(-14px) rotate(var(--r, 0deg)); }
  }

  @media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
    .reveal { opacity: 1 !important; transform: none !important; }
  }

  .reveal { opacity: 0; transform: translateY(26px); transition: opacity .7s ease, transform .7s ease; }
  .reveal.in { opacity: 1; transform: translateY(0); }

  .eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--ink); color: var(--cream-card);
    font-weight: 700; font-size: 12px; letter-spacing: .09em; text-transform: uppercase;
    padding: 8px 16px 8px 14px; border-radius: 999px;
  }
  .eyebrow .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--orange); }

  .signature { border-top: var(--border-thin) solid var(--ink); border-left: var(--border-thin) solid var(--ink); border-right: var(--border-thick) solid var(--ink); border-bottom: var(--border-thick) solid var(--ink); }

  .outline-word {
    color: var(--orange);
    -webkit-text-stroke: 2.5px var(--ink);
    display: inline-block;
  }

  /* Botón real de la app (AuthButton/CenterCreateFab): relleno sólido +
     borde asimétrico (fino arriba/izq, grueso abajo/der) + sombra dura sin
     blur desplazada — no el "3D tipo Duolingo" de un botón genérico. Al
     presionar, el botón se desplaza exactamente lo que mide la sombra y
     esta desaparece: queda "empujado" contra el borde, como si lo hundieras. */
  .btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--ink); color: var(--cream-card);
    font-weight: 700; font-size: 15.5px;
    padding: 15px 28px; border-radius: 999px;
    border-top: var(--border-thin) solid var(--ink);
    border-left: var(--border-thin) solid var(--ink);
    border-right: var(--border-thick) solid var(--ink);
    border-bottom: var(--border-thick) solid var(--ink);
    box-shadow: 3px 4px 0 var(--ink);
    transition: transform .14s ease, box-shadow .14s ease;
  }
  .btn:hover { transform: translate(-1px, -2px); box-shadow: 4px 6px 0 var(--ink); }
  .btn:active { transform: translate(3px, 4px); box-shadow: 0 0 0 var(--ink); }
  .btn svg { width: 16px; height: 16px; }
  .btn.orange { background: var(--orange); color: var(--white); }
  .btn.orange:hover { box-shadow: 4px 6px 0 var(--ink); }

  /* ══ nav ══ */
  .nav { display: flex; align-items: center; justify-content: space-between; padding: 26px 0 0; }
  .nav img { height: 20px; }
  .btn.small { padding: 11px 20px; font-size: 14px; box-shadow: 2px 3px 0 var(--ink); }
  .btn.small:hover { transform: translate(-1px, -2px); box-shadow: 3px 4px 0 var(--ink); }
  .btn.small:active { transform: translate(2px, 3px); box-shadow: 0 0 0 var(--ink); }

  /* ══ hero ══ */
  .hero { position: relative; padding: 70px 0 60px; display: grid; grid-template-columns: 1.05fr .95fr; gap: 30px; align-items: center; }
  .hero-copy > * + * { margin-top: 24px; }
  .hero h1 { font-size: clamp(2.7rem, 5.8vw, 5.1rem); line-height: .98; color: var(--ink); }
  .hero-sub { max-width: 44ch; font-size: clamp(1.02rem, 1.4vw, 1.18rem); line-height: 1.55; color: var(--ink-soft); }
  .hero-cta { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
  .hero-cta small { color: var(--ink-soft); font-size: 13px; font-weight: 600; }

  .hero-stage { position: relative; height: 460px; display: flex; align-items: center; justify-content: center; }
  .sticker-wrap {
    position: relative;
    width: min(360px, 84%);
    animation: wobble 7s ease-in-out infinite;
  }
  .sticker-badge {
    width: 100%;
    display: block;
    filter: drop-shadow(0 24px 34px rgba(43,43,43,.22));
  }
  /* Barrido de brillo diagonal, como una calco recién pegada bajo la luz.
     inset:0 (no -20%) porque el brillo ahora está enmascarado con el propio
     PNG como máscara de alfa — el pseudo-elemento tiene que coincidir
     exactamente con el cuadro de la imagen para que la máscara calce.
     El recorrido diagonal lo da el background-size de 260% + la animación
     de background-position, no el tamaño del cuadro. */
  .sticker-wrap::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(115deg, transparent 35%, rgba(255,255,255,.7) 50%, transparent 65%);
    background-size: 260% 260%;
    background-position: -80% -80%;
    animation: shine 5s ease-in-out infinite;
    animation-delay: 1.4s;
    mix-blend-mode: screen;
    pointer-events: none;
    -webkit-mask-image: var(--logo-mask);
    mask-image: var(--logo-mask);
    -webkit-mask-size: 100% 100%;
    mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-position: center;
  }
  @keyframes wobble {
    0%, 100% { transform: rotate(-6deg) translateY(0); }
    25% { transform: rotate(-3deg) translateY(-10px); }
    50% { transform: rotate(-7deg) translateY(2px); }
    75% { transform: rotate(-4deg) translateY(-6px); }
  }
  @keyframes shine {
    0%, 30% { background-position: -80% -80%; }
    55%, 100% { background-position: 180% 180%; }
  }
  .hero-stage .bubble {
    position: absolute;
    background: var(--cream-card);
    border-radius: 20px 20px 20px 4px;
    padding: 12px 16px;
    font-weight: 700;
    font-size: 14px;
    max-width: 168px;
  }
  .hero-stage .bubble.b1 { top: 4%; left: -4%; transform: rotate(-6deg); }
  .hero-stage .bubble.b2 { bottom: 10%; right: -6%; transform: rotate(5deg); }

  /* ══ strip of invented shapes ══ */
  .strip { display: flex; justify-content: center; align-items: center; gap: 26px; padding: 10px 0 70px; flex-wrap: wrap; }
  .strip .blob {
    width: 58px; height: 58px;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Londrina Solid', cursive;
    font-size: 22px;
    color: var(--white);
  }
  .strip .blob:nth-child(1) { background: var(--orange); border-radius: 42% 58% 63% 37% / 45% 41% 59% 55%; transform: rotate(-8deg); }
  .strip .blob:nth-child(2) { background: var(--violet); border-radius: 58% 42% 39% 61% / 55% 61% 39% 45%; transform: rotate(6deg); }
  .strip .blob:nth-child(3) { background: var(--ink); border-radius: 50%; transform: rotate(0deg); }
  .strip .blob:nth-child(4) { background: var(--violet-soft); border-radius: 63% 37% 42% 58% / 41% 55% 45% 59%; transform: rotate(-4deg); }
  .strip .blob:nth-child(5) { background: var(--orange); border-radius: 39% 61% 58% 42% / 61% 45% 55% 39%; transform: rotate(10deg); }

  /* ══ section shell ══ */
  section { position: relative; padding: 84px 0; }
  .section-head { max-width: 620px; margin: 0 0 48px; }
  .section-head h2 { font-size: clamp(2rem, 3.4vw, 2.9rem); color: var(--ink); }
  .section-head p { margin-top: 14px; color: var(--ink-soft); font-size: 1.05rem; line-height: 1.55; }

  /* ══ how it works ══ */
  .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }
  .step {
    background: var(--cream-card); border-radius: 24px; padding: 32px 26px 34px; position: relative;
    transition: opacity .6s cubic-bezier(.22,1.4,.36,1), transform .6s cubic-bezier(.22,1.4,.36,1);
  }
  .step:nth-child(1) { transform: rotate(calc(var(--tilt) * -1)); }
  .step:nth-child(2) { transform: rotate(var(--tilt)); transition-delay: .1s; }
  .step:nth-child(3) { transform: rotate(calc(var(--tilt) * -1)); transition-delay: .2s; }
  .step:not(.in) { opacity: 0; transform: translateY(30px) scale(.85) rotate(0deg) !important; }
  .step:hover { transform: rotate(0deg) translateY(-6px) !important; }
  .step-icon { width: 58px; height: 58px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; transition: transform .3s ease; }
  .step:hover .step-icon { transform: rotate(-8deg) scale(1.08); }
  .step-icon svg { width: 28px; height: 28px; }
  .step:nth-child(1) .step-icon { background: var(--orange); }
  .step:nth-child(2) .step-icon { background: var(--violet); }
  .step:nth-child(3) .step-icon { background: var(--ink); }
  .step .num {
    position: absolute; top: -14px; right: 22px;
    font-family: 'Londrina Solid', cursive; font-size: 15px;
    background: var(--ink); color: var(--cream-card);
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
  }
  .step h3 { font-size: 1.42rem; margin-bottom: 10px; }
  .step p { color: var(--ink-soft); font-size: .98rem; line-height: 1.5; }

  /* ══ phone mockup (invented, not a real screenshot) ══ */
  .inbox-band { position: relative; }
  .inbox-inner { display: flex; align-items: center; justify-content: space-between; gap: 48px; }
  .inbox-copy { max-width: 400px; }
  .inbox-copy h2 { font-size: clamp(1.95rem, 3.2vw, 2.7rem); }
  .inbox-copy p { margin-top: 14px; color: var(--ink-soft); font-size: 1.03rem; line-height: 1.55; }

  .phone {
    width: 272px;
    background: var(--ink);
    border-radius: 42px;
    padding: 14px;
    box-shadow: 0 30px 50px rgba(43,43,43,.28);
    transform: rotate(-3deg);
  }
  .phone-screen { background: var(--cream-card); border-radius: 30px; padding: 20px 16px 24px; min-height: 460px; position: relative; overflow: hidden; }
  .phone-notch { width: 70px; height: 8px; background: var(--ink); border-radius: 6px; margin: 0 auto 18px; }
  .phone-head { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
  .phone-head .avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: var(--orange); color: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Londrina Solid', cursive; font-size: 16px;
  }
  .phone-head strong { font-size: 14px; }
  .phone-head span { display: block; font-size: 11px; color: var(--ink-soft); }
  .phone-head .live { margin-left: auto; width: 8px; height: 8px; border-radius: 50%; background: #3ecf6b; }

  .msg {
    border-radius: 16px 16px 16px 4px; padding: 12px 14px; font-size: 13px; font-weight: 600; line-height: 1.4; margin-bottom: 12px; max-width: 88%;
    opacity: 0; transform: translateY(14px) scale(.92);
    transition: opacity .5s cubic-bezier(.2,.9,.3,1.4), transform .5s cubic-bezier(.2,.9,.3,1.4);
  }
  /* Las preguntas "llegan" una por una una vez que el teléfono entra en
     pantalla — nth-of-type cuenta TODOS los div hijos de .phone-screen
     (notch=1, head=2), así que el primer mensaje es el 3ro. */
  .phone.in .msg { opacity: 1; }
  .phone-screen > .msg:nth-of-type(3) { transition-delay: .15s; }
  .phone-screen > .msg:nth-of-type(4) { transition-delay: .45s; }
  .phone-screen > .msg:nth-of-type(5) { transition-delay: .75s; }
  .phone-screen > .msg:nth-of-type(6) { transition-delay: 1.05s; }
  .msg.m1 { background: var(--peach); transform: rotate(-1.2deg) translateY(14px) scale(.92); }
  .msg.m2 { background: var(--violet-soft); color: var(--ink); margin-left: auto; transform: rotate(1.4deg) translateY(14px) scale(.92); }
  .msg.m3 { background: var(--blue-mist); transform: rotate(-.8deg) translateY(14px) scale(.92); }
  .phone.in .msg.m1 { transform: rotate(-1.2deg); }
  .phone.in .msg.m2 { transform: rotate(1.4deg); }
  .phone.in .msg.m3 { transform: rotate(-.8deg); }
  .msg .tag { display: inline-block; background: var(--ink); color: var(--cream-card); font-size: 9px; font-weight: 800; letter-spacing: .05em; padding: 2px 7px; border-radius: 999px; margin-bottom: 6px; }
  .phone-head .live { animation: pulse-live 1.8s ease-in-out infinite; }
  @keyframes pulse-live {
    0%, 100% { box-shadow: 0 0 0 0 rgba(62,207,107,.5); }
    50% { box-shadow: 0 0 0 5px rgba(62,207,107,0); }
  }

  /* ══ marketplace-style teaser, invented tiles ══ */
  .mkt { display: grid; grid-template-columns: .95fr 1.05fr; gap: 48px; align-items: center; }
  .mkt-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
  .mkt-tile {
    aspect-ratio: 1; border-radius: 20px; display: flex; align-items: center; justify-content: center;
    font-family: 'Londrina Solid', cursive; font-size: 30px; color: var(--white);
    transition: opacity .55s cubic-bezier(.22,1.4,.36,1), transform .55s cubic-bezier(.22,1.4,.36,1);
  }
  .mkt-grid:not(.in) .mkt-tile { opacity: 0; transform: translateY(24px) rotate(0deg) scale(.7) !important; }
  .mkt-tile:hover { transform: scale(1.08) rotate(0deg) !important; }
  .mkt-tile:nth-child(1) { background: var(--orange); transform: rotate(-4deg); transition-delay: 0s; }
  .mkt-tile:nth-child(2) { background: var(--violet); transform: rotate(3deg) translateY(10px); transition-delay: .06s; }
  .mkt-tile:nth-child(3) { background: var(--ink); transform: rotate(-3deg); transition-delay: .12s; }
  .mkt-tile:nth-child(4) { background: var(--violet-soft); transform: rotate(4deg) translateY(6px); transition-delay: .18s; }
  .mkt-tile:nth-child(5) { background: var(--cream-card); color: var(--ink); border-radius: 20px; transform: rotate(-2deg) translateY(-8px); transition-delay: .24s; }
  .mkt-tile:nth-child(6) { background: var(--orange); transform: rotate(2deg); transition-delay: .3s; }
  .mkt-copy h2 { font-size: clamp(1.95rem, 3.2vw, 2.7rem); }
  .mkt-copy p { margin-top: 16px; color: var(--ink-soft); font-size: 1.05rem; line-height: 1.6; max-width: 42ch; }

  /* ══ final cta: solid color break ══ */
  .cta-band { position: relative; background: var(--ink); color: var(--cream-card); text-align: center; overflow: hidden; }
  .cta-band .mesh .b1 { background: var(--violet); opacity: .35; }
  .cta-band .mesh .b2 { background: var(--orange); opacity: .25; }
  .cta-band .mesh .b3 { background: var(--violet-soft); opacity: .2; }
  .cta-band h2 { color: var(--cream-card); font-size: clamp(2.2rem, 5vw, 3.7rem); line-height: 1.02; }
  .cta-band p { margin: 18px auto 0; max-width: 46ch; color: #cfc9c1; font-size: 1.05rem; }
  .cta-band .btn.orange { margin-top: 34px; padding: 18px 34px; font-size: 17px; }
  .cta-band img.logo { height: 26px; margin: 0 auto 26px; }

  /* ══ footer ══ */
  footer { padding: 46px 0 40px; }
  .foot-inner { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; }
  .foot-inner img { height: 15px; opacity: .8; }
  .foot-links { display: flex; gap: 26px; flex-wrap: wrap; }
  .foot-links a { font-size: 13.5px; color: var(--ink-soft); font-weight: 600; }
  .foot-links a:hover { color: var(--ink); }
  .foot-copy { font-size: 12.5px; color: var(--ink-soft); margin-top: 20px; }

  @media (max-width: 880px) {
    .hero { grid-template-columns: 1fr; }
    .hero-stage { order: -1; height: 340px; }
    .steps { grid-template-columns: 1fr; }
    .inbox-inner { flex-direction: column; text-align: center; }
    .mkt { grid-template-columns: 1fr; }
    .mkt-grid { order: -1; max-width: 340px; margin: 0 auto; }
  }
</style>
</head>
<body>

<div class="hero-shell">
<div class="mesh" aria-hidden="true">
  <span class="b1"></span><span class="b2"></span><span class="b3"></span><span class="b4"></span>
</div>

<div class="wrap nav reveal in">
  <img src="{{ asset('assets/shhask-logo-black.png') }}" alt="Shhask" />
  <a class="btn small" href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2">Descargar</a>
</div>

<header class="wrap hero">
  <div class="hero-copy">
    <span class="eyebrow reveal in" style="transition-delay:.05s"><span class="dot"></span>100% anónimo</span>
    <h1 class="reveal in" style="transition-delay:.12s">Preguntas anónimas.<br /><span class="outline-word">¡Sin filtro!</span></h1>
    <p class="hero-sub reveal in" style="transition-delay:.22s">Armá tu buzón, pegalo en tu story de Instagram, y dejá que te digan lo que nunca te dirían mirándote a los ojos.</p>
    <div class="hero-cta reveal in" style="transition-delay:.32s">
      <a class="btn orange" href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v13"/><path d="m6 11 6 6 6-6"/><path d="M5 21h14"/></svg>
        Descargar gratis
      </a>
      <small>Disponible en Google Play</small>
    </div>
  </div>
  <div class="hero-stage reveal in" style="transition-delay:.18s">
    <span class="sparkle" style="top:6%; left:10%; animation-delay:.2s"></span>
    <span class="sparkle violet" style="bottom:14%; left:4%; width:16px; height:16px; animation-delay:1.4s"></span>
    <span class="sparkle" style="top:12%; right:6%; width:18px; height:18px; animation-delay:.8s"></span>
    <span class="qmark" style="top:2%; right:20%; font-size:38px; --r:8deg; animation-delay:.4s">?</span>
    <span class="qmark" style="bottom:4%; right:2%; font-size:28px; --r:-10deg; animation-delay:1.6s">?</span>
    <div class="sticker-wrap" style="--logo-mask: url('{{ asset('assets/shhask-logo-sticker.png') }}')">
      <img class="sticker-badge" src="{{ asset('assets/shhask-logo-sticker.png') }}" alt="Shhask" />
    </div>
    <div class="bubble b1 signature">¿Quién te gusta? 👀</div>
    <div class="bubble b2 signature">Contame un secreto...</div>
  </div>
</header>
</div>

<div class="wrap strip">
  <div class="blob">?</div>
  <div class="blob">!</div>
  <div class="blob">✦</div>
  <div class="blob">?</div>
  <div class="blob">¡</div>
</div>

<section id="como-funciona" style="position:relative">
  <span class="sparkle violet" style="top:6%; left:6%; animation-delay:1s"></span>
  <span class="sparkle" style="bottom:10%; right:8%; width:16px; height:16px; animation-delay:2.2s"></span>
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow"><span class="dot"></span>Así funciona</span>
      <h2 style="margin-top:16px">Tres pasos. Cero drama.</h2>
      <p>Nada de configuraciones raras — armás tu buzón y en cinco minutos ya te están preguntando cosas que jamás te dirían de frente.</p>
    </div>
    <div class="steps">
      <div class="step reveal">
        <span class="num">1</span>
        <div class="step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
        <h3>Creá tu buzón</h3>
        <p>Elegí un nombre, un estilo y una pista para romper el hielo. Queda listo en el momento.</p>
      </div>
      <div class="step reveal">
        <span class="num">2</span>
        <div class="step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.5 6.8-3.9M8.6 13.5l6.8 3.9"/></svg></div>
        <h3>Compartilo en tu story</h3>
        <p>Pegás el link como sticker de Instagram y listo — tu buzón ya está esperando preguntas.</p>
      </div>
      <div class="step reveal">
        <span class="num">3</span>
        <div class="step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></div>
        <h3>Respondé sin miedo</h3>
        <p>Vos decidís qué contestar y qué no. El anonimato es de quien pregunta — el control es tuyo.</p>
      </div>
    </div>
  </div>
</section>

<section class="inbox-band">
  <div class="wrap inbox-inner">
    <div class="inbox-copy reveal">
      <span class="eyebrow"><span class="dot"></span>Tu bandeja</span>
      <h2 style="margin-top:16px">Así se siente<br />que te pregunten.</h2>
      <p>Preguntas nuevas cayendo en tiempo real, cada una de alguien que prefirió no dar la cara. Vos elegís a cuáles responder.</p>
    </div>
    <div class="phone reveal">
      <div class="phone-screen">
        <div class="phone-notch"></div>
        <div class="phone-head">
          <div class="avatar">?</div>
          <div><strong>Tu buzón</strong><span>3 preguntas nuevas</span></div>
          <div class="live"></div>
        </div>
        <div class="msg m1"><span class="tag">Anónimo</span><br />¿de quién estuviste enamorado más tiempo?</div>
        <div class="msg m2"><span class="tag">Vos</span><br />jaja no te la voy a hacer tan fácil 😏</div>
        <div class="msg m1"><span class="tag">Anónimo</span><br />contame algo que nunca contaste acá</div>
        <div class="msg m3"><span class="tag">Anónimo</span><br />¿qué es lo más random que hiciste este mes?</div>
      </div>
    </div>
  </div>
</section>

<section id="tienda" style="position:relative">
  <span class="sparkle" style="top:10%; right:12%; animation-delay:.6s"></span>
  <span class="sparkle ink" style="bottom:14%; right:30%; width:14px; height:14px; animation-delay:1.8s"></span>
  <div class="wrap mkt">
    <div class="mkt-grid reveal">
      <div class="mkt-tile">✦</div>
      <div class="mkt-tile">?</div>
      <div class="mkt-tile">!</div>
      <div class="mkt-tile">♥</div>
      <div class="mkt-tile">✎</div>
      <div class="mkt-tile">✦</div>
    </div>
    <div class="mkt-copy reveal">
      <span class="eyebrow"><span class="dot"></span>Tienda</span>
      <h2 style="margin-top:16px">Vestí tu buzón a tu manera.</h2>
      <p>Cada pregunta que recibís te da hype. Gastalo en stickers, fondos y diseños de la tienda para que tu buzón se vea único — el tuyo, no una plantilla más.</p>
    </div>
  </div>
</section>

<section class="cta-band">
  <div class="mesh" aria-hidden="true"><span class="b1"></span><span class="b2"></span><span class="b3"></span></div>
  <div class="wrap">
    <img class="logo reveal" src="{{ asset('assets/shhask-logo-white.png') }}" alt="Shhask" />
    <h2 class="reveal">¿Y si te preguntan<br />lo que nunca imaginaste?</h2>
    <p class="reveal">Descargalo, armá tu buzón y compartilo. Lo peor que puede pasar es que te conozcan un poco más.</p>
    <a class="btn orange reveal" href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v13"/><path d="m6 11 6 6 6-6"/><path d="M5 21h14"/></svg>
      Descargar gratis
    </a>
  </div>
</section>

<footer>
  <div class="wrap foot-inner">
    <img src="{{ asset('assets/shhask-logo-black.png') }}" alt="Shhask" />
    <div class="foot-links">
      <a href="/privacy-policy">Política de Privacidad</a>
      <a href="/terms-of-service">Términos de Servicio</a>
      <a href="/how-to-delete-user">Eliminar cuenta</a>
    </div>
  </div>
  <div class="wrap foot-copy">© 2026 Shhask. Todos los derechos reservados.</div>
</footer>

<script>
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var els = document.querySelectorAll('.reveal:not(.in)');
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: 0.15 });
    els.forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); });
  }

  // Parallax sutil de los blobs de fondo: cada uno se mueve a su propia
  // velocidad según el scroll, para que la atmósfera tenga profundidad
  // real en vez de quedar pegada como un fondo plano.
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var meshes = document.querySelectorAll('.mesh');
    var ticking = false;
    function applyParallax() {
      var y = window.scrollY;
      meshes.forEach(function (mesh) {
        var spans = mesh.querySelectorAll('span');
        spans.forEach(function (span, i) {
          var speed = 0.05 + (i % 4) * 0.035;
          span.style.setProperty('--py', (y * speed) + 'px');
        });
      });
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { requestAnimationFrame(applyParallax); ticking = true; }
    }, { passive: true });
  }
</script>
</body>
</html>
