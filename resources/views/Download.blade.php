<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Shhask: tu buzón de preguntas anónimas. Compartilo en tu story de Instagram y recibí lo que nunca te dirían de frente. Disponible en Google Play.">
    <meta name="keywords" content="mensajes anónimos, preguntas anónimas, Shhask, Instagram story, Google Play, app social">
    <meta property="og:title" content="Shhask - Preguntas anónimas, sin filtro">
    <meta property="og:description" content="Armá tu buzón, pegalo en tu story y dejá que te pregunten lo que nunca te dirían de frente. Gratis en Google Play.">
    <meta property="og:image" content="{{ asset('assets/logo-sticker.webp') }}">
    <meta property="og:url" content="www.shhask.com">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Shhask - Preguntas anónimas, sin filtro">
    <meta name="twitter:description" content="Armá tu buzón, pegalo en tu story y dejá que te pregunten lo que nunca te dirían de frente.">
    <title>Shhask — preguntas anónimas, sin filtro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Londrina+Solid:wght@100;300;400;900&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<style>
  :root {
    /* Base — orange (tokens/colors.css) */
    --orange-50:#FFF1E7; --orange-100:#FFDCC4; --orange-300:#FF9757; --orange-500:#F85A00; --orange-600:#D44B00; --orange-700:#A73B00;
    /* Base — ink */
    --ink-900:#0D0B0A; --ink-800:#171311; --ink-600:#463B36; --ink-500:#6B5C55; --ink-400:#9A8A82; --ink-300:#C7BBB4; --ink-200:#E4DCD6; --ink-100:#F2ECE7;
    /* Base — paper */
    --paper:#FFFFFF; --paper-warm:#FFF8F2; --paper-dim:#FAF3ED;

    /* Semantic (tokens/colors.css) */
    --text-body:var(--ink-800); --text-strong:var(--ink-900); --text-muted:var(--ink-500); --text-faint:var(--ink-400);
    --text-on-accent:#FFFFFF; --line-ink:var(--ink-900); --line-accent:var(--orange-500); --surface-inverse:var(--ink-900);

    /* Typography (tokens/typography.css) */
    --font-display:"Londrina Solid","Hanken Grotesk",system-ui,sans-serif;
    --font-sans:"Hanken Grotesk",system-ui,-apple-system,"Segoe UI",sans-serif;
    --text-heading:24px; --text-subhead:19px; --text-body-lg:18px; --text-body-md:16px; --text-caption:13px; --text-micro:11px;
    --leading-tight:1.12; --leading-snug:1.3; --leading-body:1.5;
    --weight-medium:500; --weight-semibold:600; --weight-bold:700; --weight-black:800;
    --tracking-caps:0.09em;

    /* Shape (tokens/shape.css) */
    --radius-pill:999px; --radius-asym-sm:14px 4px 14px 4px; --radius-asym-md:22px 6px 22px 6px; --radius-asym-lg:34px 8px 34px 8px;
    --shadow-hard-sm:3px 3px 0 var(--ink-900); --shadow-hard-md:5px 6px 0 var(--ink-900); --shadow-hard-lg:8px 10px 0 var(--ink-900);
    --shadow-hard-accent:5px 6px 0 var(--orange-500);

    /* Motion (tokens/motion.css) — used by the Button component */
    --ease-pop:cubic-bezier(.34,1.56,.64,1); --ease-out:cubic-bezier(.22,.9,.3,1); --dur-fast:150ms;

    /* Patterns (tokens/patterns.css) */
    --pattern-dots:radial-gradient(circle at 50% 50%, currentColor 1.6px, transparent 1.7px) 0 0/14px 14px;
    --pattern-grid:linear-gradient(currentColor 1px, transparent 1px) 0 0/22px 22px, linear-gradient(90deg, currentColor 1px, transparent 1px) 0 0/22px 22px;
    --pattern-scallop:radial-gradient(circle at 50% 0, transparent 9px, currentColor 9px 10px, transparent 10px) 0 0/22px 14px;
    --pattern-waves:repeating-radial-gradient(circle at 0 50%, transparent 0 8px, currentColor 8px 9px) 0 0/24px 18px;
    --pattern-zigzag:repeating-linear-gradient(135deg, currentColor 0 2px, transparent 2px 10px), repeating-linear-gradient(45deg, currentColor 0 2px, transparent 2px 10px);
    --pattern-stars:radial-gradient(circle at 50% 50%, currentColor 1px, transparent 2px) 0 0/26px 26px;
    --pattern-noise:repeating-conic-gradient(currentColor 0 0.6deg, transparent 0.6deg 3deg) 0 0/7px 7px;
    --pattern-rings:repeating-radial-gradient(circle at 50% 50%, transparent 0 7px, currentColor 7px 8px) 0 0/34px 34px;

    /* Local to this page (matches the Claude Design source's own <style>) */
    --ease-out-strong:cubic-bezier(.23,1,.32,1);
  }

  * { box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  html, body { margin:0; padding:0; background:var(--paper-warm); }
  body { font-family:var(--font-sans); color:var(--text-body); -webkit-font-smoothing:antialiased; overflow-x:hidden; }
  a { color:inherit; text-decoration:none; }
  img { max-width:100%; display:block; }
  button { font-family:inherit; }

  @keyframes shh-marquee-rev { from { transform:translateX(-50%); } to { transform:translateX(0); } }
  @keyframes shh-bob { 0%,100% { transform:translateY(0) rotate(-3deg); } 50% { transform:translateY(-7px) rotate(-1deg); } }
  @keyframes shh-float-a { 0%,100% { transform:translateY(0) rotate(-3deg); } 50% { transform:translateY(-8px) rotate(-2deg); } }
  @keyframes shh-float-b { 0%,100% { transform:translateY(0) rotate(4deg); } 50% { transform:translateY(-7px) rotate(2deg); } }
  @keyframes shh-float-c { 0%,100% { transform:translateY(0) rotate(-1.5deg); } 50% { transform:translateY(-8px) rotate(0.5deg); } }
  @keyframes shh-drop { from { opacity:0; transform:translateY(-14px) scale(.94); } to { opacity:1; transform:translateY(0) scale(1); } }
  @keyframes shh-h1-a { from { opacity:0; transform:translateY(18px) rotate(-2.5deg); } to { opacity:1; transform:translateY(0) rotate(-2.5deg); } }
  @keyframes shh-h1-b { from { opacity:0; transform:translateY(18px) rotate(1.5deg); } to { opacity:1; transform:translateY(0) rotate(1.5deg); } }
  @keyframes shh-h1-c { from { opacity:0; transform:translateY(18px) rotate(-1.5deg); } to { opacity:1; transform:translateY(0) rotate(-1.5deg); } }
  [data-rail]::-webkit-scrollbar { display:none; }
  [data-reveal] { opacity:0; transition:opacity 420ms var(--ease-out-strong); transition-delay:var(--d, 0ms); }
  [data-reveal][data-revealed] { opacity:1; }
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:150ms !important; }
  }

  /* Button component (ported 1:1 from the design system's components/core/Button.jsx) */
  .btn {
    display:inline-flex; align-items:center; justify-content:center; gap:9px;
    font:var(--weight-bold) var(--text-body-md)/1 var(--font-sans); letter-spacing:.005em;
    padding:12px 20px; border-radius:var(--radius-asym-md);
    border:2px solid var(--ink-900); cursor:pointer;
    transition:transform var(--dur-fast) var(--ease-pop), box-shadow var(--dur-fast) var(--ease-out), background var(--dur-fast) var(--ease-out);
  }
  .btn--lg { padding:16px 28px; font-size:var(--text-body-lg); gap:10px; }
  .btn--md { padding:12px 20px; font-size:var(--text-body-md); gap:9px; }
  .btn--primary { background:var(--orange-500); color:var(--text-on-accent); box-shadow:var(--shadow-hard-md); }
  .btn--ink { background:var(--ink-900); color:var(--paper-warm); box-shadow:var(--shadow-hard-accent); }
  .btn:hover { transform:translate(-1px,-2px); }
  .btn:active { transform:translate(4px,5px); box-shadow:1px 1px 0 var(--ink-900) !important; }

  /* Icon component fallback — standard Lucide outline icons (the design system's Icon.jsx
     masks the same lucide-static SVGs from a CDN; inlined here to avoid a runtime dependency). */
  .icon { display:inline-block; flex:0 0 auto; }
  .icon svg { width:100%; height:100%; display:block; }

  /* Theme picker tabs */
  .theme-tab {
    flex:none; cursor:pointer; font-family:var(--font-sans); font-size:var(--text-caption); font-weight:700;
    letter-spacing:var(--tracking-caps); text-transform:uppercase; padding:9px 14px; border-radius:999px;
    white-space:nowrap; background:transparent; color:var(--ink-300); border:2px solid var(--ink-600);
    transition:background-color 180ms var(--ease-out-strong), border-color 180ms var(--ease-out-strong), color 180ms var(--ease-out-strong), transform 160ms var(--ease-out-strong);
  }
  .theme-tab.is-active { background:var(--orange-500); border-color:var(--orange-500); color:#FFFFFF; }
  .theme-tab:active { transform:scale(0.97); }

  /* Asset seed themes (tokens/themes.css) — the 6 style presets in the "vestí tu buzón" preview */
  #theme-preview { --asset-bg:var(--paper); --asset-bg-2:var(--paper-dim); --asset-ink:var(--ink-900); --asset-accent:var(--orange-500); --asset-on-accent:#FFFFFF; --asset-border-color:var(--ink-900); transition:background-color 260ms ease, border-color 260ms ease; }
  .theme-coquette { --asset-bg:#F8D7E3; --asset-bg-2:#FCEAE6; --asset-ink:#6B2038; --asset-accent:#E8608E; --asset-on-accent:#FFFFFF; --asset-border-color:#C74C74; }
  .theme-y2k { --asset-bg:#7FE3FF; --asset-bg-2:#FF4FD8; --asset-ink:#2A1B72; --asset-accent:#C9FF2E; --asset-on-accent:#2A1B72; --asset-border-color:#2A1B72; }
  .theme-grunge { --asset-bg:#0E0E10; --asset-bg-2:#1A1A1E; --asset-ink:#F2F2F0; --asset-accent:#B6FF2E; --asset-on-accent:#0E0E10; --asset-border-color:#B6FF2E; }
  .theme-cottagecore { --asset-bg:#B7C9A8; --asset-bg-2:#F3EEDF; --asset-ink:#4A3728; --asset-accent:#8A6B4A; --asset-on-accent:#F3EEDF; --asset-border-color:#4A3728; }
  .theme-clean-girl { --asset-bg:#EADFD1; --asset-bg-2:#DBCBB6; --asset-ink:#4A4038; --asset-accent:#A8927A; --asset-on-accent:#FFFFFF; --asset-border-color:#A8927A; }
  .theme-gothic { --asset-bg:#241A33; --asset-bg-2:#120C1B; --asset-ink:#EDE6F5; --asset-accent:#7C5CB0; --asset-on-accent:#120C1B; --asset-border-color:#7C5CB0; }

  /* Style rail cards (static — not theme-switchable, they're a showcase strip) */
  .style-card { scroll-snap-align:start; position:relative; overflow:hidden; flex:none; width:150px; aspect-ratio:9/16; border:2px solid var(--line-ink); border-radius:var(--radius-asym-md); box-shadow:var(--shadow-hard-md); padding:14px; display:flex; flex-direction:column; justify-content:flex-end; }
  .style-card span { position:relative; font-family:var(--font-display); font-size:26px; line-height:.9; transform:rotate(-2.5deg); }
  .style-card .pattern { position:absolute; inset:0; pointer-events:none; }

  /* FAQ accordion */
  .faq-item { border-bottom:2px solid var(--line-ink); }
  .faq-q { width:100%; background:none; border:0; padding:16px 2px; display:flex; align-items:center; justify-content:space-between; gap:14px; cursor:pointer; text-align:left; font-family:var(--font-sans); font-size:var(--text-subhead); font-weight:var(--weight-bold); color:var(--text-strong); transition:transform 160ms var(--ease-out-strong); }
  .faq-q:active { transform:scale(0.99); }
  .faq-sign { flex:none; font-family:var(--font-display); font-size:30px; line-height:1; color:var(--orange-500); }
  .faq-a { margin:0; padding:0 2px 18px; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--text-muted); max-width:44ch; display:none; }
  .faq-item.is-open .faq-a { display:block; }
</style>
</head>
<body>

<div style="background:var(--paper-warm); overflow-x:hidden; padding-bottom:84px">

  <div style="position:relative; background:var(--ink-900); border-bottom:3px solid var(--line-ink); overflow:hidden; padding:0 0 14px">
    <div style="position:relative; left:-6%; width:112%; margin-bottom:22px; background:var(--orange-500); border-top:2px solid var(--line-ink); border-bottom:2px solid var(--line-ink); overflow:hidden; padding:7px 0; transform:rotate(-2.2deg)">
      <div style="display:flex; width:max-content; animation:shh-marquee-rev 19s linear infinite; will-change:transform">
        <div style="display:flex; gap:18px; padding-right:18px; font-family:var(--font-display); font-size:22px; line-height:1; color:var(--ink-900); white-space:nowrap; text-transform:lowercase">
          <span>preguntas anónimas</span><span>·</span><span>preguntas anónimas</span><span>·</span><span>preguntas anónimas</span><span>·</span>
        </div>
        <div style="display:flex; gap:18px; padding-right:18px; font-family:var(--font-display); font-size:22px; line-height:1; color:var(--ink-900); white-space:nowrap; text-transform:lowercase">
          <span>preguntas anónimas</span><span>·</span><span>preguntas anónimas</span><span>·</span><span>preguntas anónimas</span><span>·</span>
        </div>
      </div>
    </div>
  </div>

  <header style="position:sticky; top:0; z-index:20; background:var(--ink-900); border-bottom:3px solid var(--line-ink)">
    <div style="max-width:540px; margin:0 auto; padding:12px 20px; display:flex; align-items:center; justify-content:space-between; gap:16px">
      <img src="{{ asset('assets/shhask-logo-white.png') }}" alt="Shhask" style="height:26px; width:auto; display:block">
      <nav style="display:flex; gap:16px; font-size:var(--text-caption); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase">
        <a href="#estilos" style="color:var(--orange-300)">Estilos</a>
        <a href="#privacidad" style="color:var(--orange-300)">Privacidad</a>
      </nav>
    </div>
  </header>

  <section style="position:relative; background:var(--ink-900); border-bottom:3px solid var(--line-ink); overflow:hidden">
  <div style="position:absolute; inset:0; color:var(--orange-500); opacity:.18; background:var(--pattern-grid); pointer-events:none"></div>
  <div style="position:relative; max-width:540px; margin:0 auto; padding:34px 20px 44px">
    <div style="display:inline-flex; align-items:center; gap:8px; border:2px solid var(--line-ink); border-radius:var(--radius-pill); padding:6px 14px; background:var(--paper); font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; box-shadow:3px 3px 0 var(--ink-900)">
      100% anónimo
    </div>

    <h1 style="margin:20px 0 0; font-family:var(--font-display); font-weight:400; font-size:clamp(66px,20vw,104px); line-height:0.82; letter-spacing:-0.01em; color:var(--paper-warm); text-transform:lowercase">
      <span style="display:block; transform:rotate(-2.5deg); animation:shh-h1-a 380ms var(--ease-out-strong) both">preguntas</span>
      <span style="display:block; color:var(--orange-500); transform:rotate(1.5deg); animation:shh-h1-b 380ms 70ms var(--ease-out-strong) both">anónimas</span>
      <span style="display:block; color:transparent; -webkit-text-stroke:2px var(--paper-warm); transform:rotate(-1.5deg); animation:shh-h1-c 380ms 140ms var(--ease-out-strong) both">sin filtro</span>
    </h1>

    <p style="margin:24px 0 0; font-size:var(--text-body-lg); line-height:var(--leading-body); color:var(--ink-300); max-width:32ch; text-wrap:pretty">
Armá tu buzón, compartilo en tu story y dejá que te digan lo que nunca te dirían de frente.
    </p>

    <div style="margin:26px 0 0; display:flex; flex-direction:column; gap:10px; align-items:flex-start">
      <a href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2" target="_blank" rel="noopener" class="btn btn--primary btn--lg" style="width:100%">Descargar gratis</a>
    </div>

    <div style="margin:40px 0 0; position:relative">
      <div style="position:absolute; top:-32px; right:-10px; width:92px; z-index:3; animation:shh-bob 4.5s ease-in-out infinite">
        <img src="{{ asset('assets/logo-sticker.webp') }}" alt="" style="width:100%; display:block">
      </div>
      <div style="background:var(--paper); border:3px solid var(--line-ink); border-radius:var(--radius-asym-lg); box-shadow:var(--shadow-hard-lg); padding:18px; transform:rotate(-1deg)">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding-bottom:12px; border-bottom:2px solid var(--line-ink)">
          <span style="font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--text-muted)">shhask.com/vos</span>
          <span style="display:inline-flex; align-items:center; gap:6px; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--text-on-accent); background:var(--orange-500); border:2px solid var(--line-ink); border-radius:var(--radius-pill); padding:3px 10px">
            <span id="feed-count" style="font-family:var(--font-display); font-size:15px; line-height:1">1</span> nuevas
          </span>
        </div>
        <div id="feed-list" style="display:flex; flex-direction:column; gap:10px; padding-top:14px; min-height:206px"></div>
      </div>
    </div>

  </div>
  </section>

  <section id="estilos" style="background:var(--surface-inverse); border-top:3px solid var(--line-ink); border-bottom:3px solid var(--line-ink)">
    <div style="max-width:540px; margin:0 auto; padding:48px 20px 52px">
      <h2 style="margin:0; font-family:var(--font-display); font-weight:400; font-size:clamp(48px,14vw,72px); line-height:0.85; color:var(--paper-warm); text-transform:lowercase">
        <span style="display:block; transform:rotate(-2.5deg)">el mismo mensaje.</span>
        <span style="display:block; color:var(--orange-500); transform:rotate(1.5deg)">tu estilo.</span>
      </h2>
      <p style="margin:18px 0 0; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--ink-300); max-width:36ch; text-wrap:pretty">
        Elegí fondo, stickers, tipografía y borde. El mensaje que subís a tu story se ve como vos, no como una plantilla.
      </p>

      <div id="theme-tabs" style="margin-top:24px; display:flex; flex-wrap:wrap; gap:8px"></div>

      <div id="theme-preview" class="theme-y2k" style="margin-top:24px; position:relative; display:flex; justify-content:center; padding:30px 16px 34px; background:var(--asset-bg-2); border:3px solid var(--asset-border-color); border-radius:var(--radius-asym-lg); box-shadow:var(--shadow-hard-lg); overflow:hidden">
        <div id="theme-pattern" style="position:absolute; inset:0; pointer-events:none; transition:opacity 150ms var(--ease-out-strong); color:var(--asset-ink)"></div>
        <img id="theme-sticker" src="" alt="" style="position:absolute; top:10px; right:-16px; width:82px; display:none">
        <div style="position:relative; width:236px; aspect-ratio:9/16; background:var(--asset-bg); border:3px solid var(--asset-border-color); transition:background-color 260ms ease, border-color 260ms ease, color 260ms ease; border-radius:var(--radius-asym-lg); box-shadow:var(--shadow-hard-lg); padding:18px; display:flex; flex-direction:column; justify-content:space-between; transform:rotate(-1.5deg)">
          <span style="font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--asset-ink); opacity:.75">@vos</span>
          <div>
            <p style="margin:0 0 10px; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--asset-ink); opacity:.7">alguien preguntó</p>
            <p style="margin:0; font-family:var(--font-display); font-size:30px; line-height:0.94; color:var(--asset-ink); text-transform:lowercase">¿quién te gusta?</p>
            <div style="margin-top:14px; background:var(--asset-accent); color:var(--asset-on-accent); border:2px solid var(--asset-border-color); border-radius:22px 22px 22px 6px; padding:8px 12px; display:inline-block; font-size:var(--text-micro); font-weight:700; letter-spacing:var(--tracking-caps); text-transform:uppercase">anónimo</div>
          </div>
          <div style="border:2px dashed var(--asset-border-color); border-radius:var(--radius-asym-sm); padding:8px; text-align:center; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--asset-ink); opacity:.8">pegá tu link acá</div>
        </div>

        <div style="position:absolute; top:26%; left:6px; width:142px; background:var(--asset-bg-2); color:var(--asset-ink); border:2px solid var(--asset-border-color); border-radius:22px 22px 22px 6px; padding:12px 14px; font-size:14px; font-weight:600; line-height:1.3; box-shadow:5px 6px 0 var(--asset-border-color); transform:rotate(-4deg); animation:shh-float-a 6s ease-in-out infinite">contame un secreto</div>
        <div style="position:absolute; bottom:7%; right:8px; width:158px; background:var(--asset-accent); color:var(--asset-on-accent); border:2px solid var(--asset-border-color); border-radius:22px 22px 22px 6px; padding:12px 14px; font-size:14px; font-weight:600; line-height:1.3; box-shadow:5px 6px 0 var(--asset-border-color); transform:rotate(4deg); animation:shh-float-b 7.2s ease-in-out infinite">¿qué pensaste de mí?</div>
      </div>

      <p style="margin:18px 0 0; text-align:center; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--ink-400)">tocá un tema para probarlo</p>
    </div>
  </section>

  <section style="position:relative; background:var(--orange-50); border-top:3px solid var(--line-ink); border-bottom:3px solid var(--line-ink)">
    <div style="position:absolute; inset:0; overflow:hidden; color:var(--orange-500); opacity:.12; background:var(--pattern-zigzag); pointer-events:none"></div>
    <span style="position:absolute; top:-17px; left:24px; z-index:3; background:var(--paper); border:2px solid var(--line-ink); border-radius:var(--radius-pill); box-shadow:3px 4px 0 var(--ink-900); padding:5px 16px; font-family:var(--font-display); font-size:22px; line-height:1.1; color:var(--ink-900); text-transform:lowercase; transform:rotate(-3deg)">así de simple</span>
    <div style="position:relative; max-width:540px; margin:0 auto; padding:44px 20px 48px">
      <p style="margin:0 0 10px; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--orange-700)">Así funciona</p>
      <h2 style="margin:0 0 24px; font-family:var(--font-display); font-weight:400; font-size:clamp(52px,15vw,80px); line-height:0.84; color:var(--text-strong); text-transform:lowercase; transform:rotate(-2.5deg)">tres pasos.<br>cero drama.</h2>

      <div style="display:flex; gap:16px; align-items:flex-start; padding:22px 0; border-top:2px dashed var(--line-accent)">
        <span style="flex:none; font-family:var(--font-display); font-size:60px; line-height:.78; color:var(--orange-600)">01</span>
        <div style="padding-top:4px">
          <h3 style="margin:0 0 4px; font-size:var(--text-heading); font-weight:var(--weight-black); color:var(--text-strong); line-height:var(--leading-tight)">Creá tu buzón</h3>
          <p style="margin:0; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--text-body)">Elegí un nombre, un estilo y una pista para romper el hielo. Queda listo en el momento.</p>
        </div>
      </div>
      <div style="display:flex; gap:16px; align-items:flex-start; padding:22px 0; border-top:2px dashed var(--line-accent)">
        <span style="flex:none; font-family:var(--font-display); font-size:60px; line-height:.78; color:var(--orange-600)">02</span>
        <div style="padding-top:4px">
          <h3 style="margin:0 0 4px; font-size:var(--text-heading); font-weight:var(--weight-black); color:var(--text-strong); line-height:var(--leading-tight)">Compartilo en tu story</h3>
          <p style="margin:0; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--text-body)">Pegás el link como sticker de Instagram y listo. Tu buzón ya está esperando preguntas.</p>
        </div>
      </div>
      <div style="display:flex; gap:16px; align-items:flex-start; padding:22px 0 4px; border-top:2px dashed var(--line-accent)">
        <span style="flex:none; font-family:var(--font-display); font-size:60px; line-height:.78; color:var(--orange-600)">03</span>
        <div style="padding-top:4px">
          <h3 style="margin:0 0 4px; font-size:var(--text-heading); font-weight:var(--weight-black); color:var(--text-strong); line-height:var(--leading-tight)">Mostrá lo que te dejaron</h3>
          <p style="margin:0; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--text-body)">Elegí qué mensaje subir a tu story y con qué estilo. El anonimato es de quien pregunta, el control es tuyo.</p>
        </div>
      </div>
    </div>
  </section>

  <section style="position:relative; background:var(--paper-warm); overflow:hidden">
    <div style="position:absolute; inset:0; color:var(--ink-900); opacity:.09; background:var(--pattern-stars); pointer-events:none"></div>
    <div style="position:relative; max-width:540px; margin:0 auto; padding:52px 20px 56px">
      <h2 style="margin:0; font-family:var(--font-display); font-weight:400; font-size:clamp(56px,16vw,88px); line-height:0.8; color:var(--text-strong); text-transform:lowercase; transform:rotate(-2.5deg)">cae solo.</h2>
      <p style="margin:16px 0 0; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--text-muted); max-width:34ch; text-wrap:pretty">Cada mensaje es de alguien que prefirió no dar la cara.</p>

      <div style="position:relative; min-height:412px; margin-top:26px">
        <div style="position:absolute; top:0; left:0; --d:0ms; width:74%; background:var(--paper); color:var(--text-strong); border:2px solid var(--line-ink); border-radius:22px 22px 22px 6px; box-shadow:var(--shadow-hard-md); padding:14px 16px; transform:rotate(-3deg); animation:shh-float-a 6.4s ease-in-out infinite" data-reveal>
          <p style="margin:0 0 4px; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--text-faint)">anónimo · hace 2 h</p>
          <p style="margin:0; font-size:17px; font-weight:600; line-height:1.3">¿de quién estuviste enamorado más tiempo?</p>
        </div>
        <div style="position:absolute; top:26%; right:0; --d:80ms; width:70%; background:var(--ink-900); color:var(--paper-warm); border:2px solid var(--line-ink); border-radius:22px 22px 6px 22px; box-shadow:5px 6px 0 var(--orange-500); padding:14px 16px; transform:rotate(3deg); animation:shh-float-b 7.4s ease-in-out infinite" data-reveal>
          <p style="margin:0; font-size:17px; font-weight:600; line-height:1.3">contame algo que nunca contaste acá</p>
        </div>
        <div style="position:absolute; top:53%; left:2%; --d:160ms; width:72%; background:var(--orange-500); color:var(--text-on-accent); border:2px solid var(--line-ink); border-radius:22px 22px 22px 6px; box-shadow:var(--shadow-hard-md); padding:14px 16px; transform:rotate(-1.5deg); animation:shh-float-c 6.8s ease-in-out infinite" data-reveal>
          <p style="margin:0; font-size:17px; font-weight:600; line-height:1.3">¿qué es lo más random que hiciste este mes?</p>
        </div>
        <div style="position:absolute; bottom:0; right:2%; --d:240ms; width:66%; background:var(--paper); color:var(--text-strong); border:2px solid var(--line-ink); border-radius:22px 22px 6px 22px; box-shadow:var(--shadow-hard-md); padding:14px 16px; transform:rotate(2.5deg); animation:shh-float-b 8.2s ease-in-out infinite" data-reveal>
          <p style="margin:0 0 4px; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--text-faint)">anónimo · recién</p>
          <p style="margin:0; font-size:17px; font-weight:600; line-height:1.3">¿quién te gusta? 👀</p>
        </div>
      </div>
    </div>
  </section>

  <section id="privacidad" style="position:relative; background:var(--ink-900); border-top:3px solid var(--line-ink); border-bottom:3px solid var(--line-ink); overflow:hidden">
    <div style="position:absolute; inset:0; color:var(--orange-500); opacity:.14; background:var(--pattern-rings); pointer-events:none"></div>
    <div style="position:relative; max-width:540px; margin:0 auto; padding:48px 20px 52px">
      <h2 style="margin:0 0 8px; font-family:var(--font-display); font-weight:400; font-size:clamp(44px,12vw,64px); line-height:0.84; color:var(--paper-warm); text-transform:lowercase; transform:rotate(-2.5deg)">nadie ve quién te escribe.</h2>

      <div data-reveal style="--d:80ms; max-width:300px; margin:22px 0 6px; background:var(--paper); border:2px dashed var(--orange-500); border-radius:var(--radius-asym-md); box-shadow:var(--shadow-hard-md); padding:14px 16px; transform:rotate(-1.2deg)">
        <p style="margin:0 0 6px; display:flex; align-items:center; gap:6px; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--orange-700)">
          <span class="icon" style="width:13px;height:13px;color:var(--orange-600)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
          </span>
          la pista que te dejó
        </p>
        <p style="margin:0; font-size:var(--text-body-md); font-weight:var(--weight-semibold); line-height:var(--leading-snug); color:var(--text-strong)">nos cruzamos siempre en el 152</p>
      </div>

      <div style="display:flex; gap:14px; align-items:flex-start; padding:22px 0; border-bottom:2px dashed var(--ink-600); color:var(--orange-500)">
        <span class="icon" style="width:26px;height:26px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
        </span>
        <p style="margin:0; font-size:19px; line-height:var(--leading-snug); color:var(--paper-warm)"><strong>No guardamos quién pregunta.</strong> <span style="color:var(--ink-300)">Ni nombre, ni cuenta, ni ubicación.</span></p>
      </div>
      <div style="display:flex; gap:14px; align-items:flex-start; padding:22px 0; border-bottom:2px dashed var(--ink-600); color:var(--orange-500)">
        <span class="icon" style="width:26px;height:26px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
        </span>
        <p style="margin:0; font-size:19px; line-height:var(--leading-snug); color:var(--paper-warm)"><strong>Borrás lo que no querés ver.</strong> <span style="color:var(--ink-300)">Cada mensaje se elimina de una.</span></p>
      </div>
      <div style="display:flex; gap:14px; align-items:flex-start; padding:22px 0 4px; color:var(--orange-500)">
        <span class="icon" style="width:26px;height:26px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <p style="margin:0; font-size:19px; line-height:var(--leading-snug); color:var(--paper-warm)"><strong>Cerrás el buzón cuando querés.</strong> <span style="color:var(--ink-300)">Y lo volvés a abrir cuando se te cante.</span></p>
      </div>

    </div>
  </section>

  <section style="background:var(--paper-warm); padding:48px 0 52px">
    <div style="max-width:540px; margin:0 auto; padding:0 20px">
      <h2 style="margin:0 0 14px; font-family:var(--font-display); font-weight:400; font-size:clamp(44px,12vw,64px); line-height:0.84; color:var(--text-strong); text-transform:lowercase; transform:rotate(-2.5deg)">vestí tu buzón.</h2>
      <p style="margin:0; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--text-muted); max-width:36ch; text-wrap:pretty">Fondos, stickers y bordes para que tu buzón se vea único. El tuyo, no una plantilla más.</p>
    </div>
    <div data-rail style="margin-top:24px; overflow-x:auto; scrollbar-width:none; scroll-snap-type:x proximity; padding:6px 0 18px; -webkit-overflow-scrolling:touch">
    <div style="display:flex; gap:14px; width:max-content; margin:0 auto; padding:0 20px">
      <div class="style-card" style="background:#F8D7E3; transform:rotate(-1.5deg)">
        <div class="pattern" style="color:#C74C74; opacity:.35; background:var(--pattern-scallop)"></div>
        <span style="color:#6B2038">coquette</span>
      </div>
      <div class="style-card" style="background:#7FE3FF; transform:rotate(1.5deg)">
        <div class="pattern" style="color:#2A1B72; opacity:.3; background:var(--pattern-grid)"></div>
        <img src="{{ asset('assets/y2k-fantasy-flower.webp') }}" alt="" style="position:absolute; top:10px; right:-16px; width:82px">
        <span style="color:#2A1B72">y2k</span>
      </div>
      <div class="style-card" style="background:#0E0E10; transform:rotate(-1deg)">
        <div class="pattern" style="color:#B6FF2E; opacity:.22; background:var(--pattern-noise)"></div>
        <span style="color:#B6FF2E">grunge</span>
      </div>
      <div class="style-card" style="background:#B7C9A8; transform:rotate(1.2deg)">
        <div class="pattern" style="color:#4A3728; opacity:.3; background:var(--pattern-waves)"></div>
        <span style="color:#4A3728">cottagecore</span>
      </div>
      <div class="style-card" style="background:#EADFD1; transform:rotate(-1.4deg)">
        <div class="pattern" style="color:#4A4038; opacity:.3; background:var(--pattern-dots)"></div>
        <span style="color:#4A4038">clean girl</span>
      </div>
      <div class="style-card" style="background:#241A33; transform:rotate(1.6deg)">
        <div class="pattern" style="opacity:.5; background:url('{{ asset('assets/patterns/gothic-damask-portrait.webp') }}') 0 0/150px auto repeat"></div>
        <span style="color:#C9B3E8">gothic</span>
      </div>
    </div>
    </div>
  </section>

  <section style="position:relative; overflow:hidden; background:var(--paper-dim); border-top:3px solid var(--line-ink)">
    <span aria-hidden="true" style="position:absolute; top:-46px; right:-16px; font-family:var(--font-display); font-size:280px; line-height:.7; color:var(--ink-900); opacity:.07; pointer-events:none; user-select:none">?</span>
    <div style="position:relative; max-width:540px; margin:0 auto; padding:48px 20px 52px">
      <h2 style="margin:0 0 22px; font-family:var(--font-display); font-weight:400; font-size:clamp(56px,16vw,88px); line-height:0.8; color:var(--text-strong); text-transform:lowercase; transform:rotate(-2.5deg)">dudas.</h2>
      <div id="faq-list" style="border-top:2px solid var(--line-ink)"></div>
    </div>
  </section>

  <section style="background:var(--surface-inverse); border-top:3px solid var(--line-ink)">
    <div style="max-width:540px; margin:0 auto; padding:52px 20px 56px; text-align:center">
      <img src="{{ asset('assets/shhask-logo-white.png') }}" alt="Shhask" style="height:30px; width:auto; display:block; margin:0 auto 22px">
      <h2 style="margin:0; font-family:var(--font-display); font-weight:400; font-size:clamp(44px,13vw,64px); line-height:0.85; color:var(--orange-500); text-transform:lowercase">
        <span style="display:block; transform:rotate(-2.5deg)">sumate</span>
        <span style="display:block; color:var(--paper-warm); transform:rotate(1.5deg)">a la joda</span>
      </h2>
      <p style="margin:20px auto 24px; font-size:var(--text-body-md); line-height:var(--leading-body); color:var(--ink-300); max-width:32ch; text-wrap:pretty">
        Descargalo, armá tu buzón y compartilo. Lo peor que puede pasar es que te conozcan un poco más.
      </p>
      <a href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2" target="_blank" rel="noopener" class="btn btn--primary btn--lg" style="width:100%">Descargar gratis</a>
    </div>
  </section>

  <footer style="background:var(--paper-warm)">
    <div style="max-width:540px; margin:0 auto; padding:34px 20px 40px; display:flex; flex-direction:column; gap:18px; align-items:flex-start">
      <img src="{{ asset('assets/logo-wordmark-ink.webp') }}" alt="Shhask" style="height:22px; width:auto; display:block; align-self:flex-start">
      <nav style="display:flex; flex-wrap:wrap; gap:8px 18px; font-size:var(--text-body-sm, 14px); font-weight:var(--weight-semibold)">
        <a href="/privacy-policy">Política de Privacidad</a>
        <a href="/terms-of-service">Términos de Servicio</a>
        <a href="/how-to-delete-user">Eliminar cuenta</a>
      </nav>
      <p style="margin:0; font-size:var(--text-caption); color:var(--text-faint)">© 2026 Shhask. Todos los derechos reservados.</p>
    </div>
  </footer>

  <div style="position:fixed; left:0; right:0; bottom:0; z-index:30; background:var(--orange-500); border-top:3px solid var(--line-ink)">
    <div style="max-width:540px; margin:0 auto; padding:12px 20px; display:flex; align-items:center; gap:14px">
      <span style="flex:1; font-family:var(--font-display); font-size:22px; line-height:1; color:var(--ink-900); text-transform:lowercase">gratis en google play</span>
      <a href="https://play.google.com/store/apps/details?id=com.mateine.quest_app_2" target="_blank" rel="noopener" class="btn btn--ink btn--md">Descargar gratis</a>
    </div>
  </div>
</div>

<script>
(function () {
  // Hero "feed" — cycles 1..4 anonymous messages, matching the design's pool + interval.
  var pool = [
    "¿Quién te gusta? 👀",
    "contame un secreto...",
    "¿qué pensaste de mí la primera vez?",
    "¿a quién extrañás y no lo decís?"
  ];
  var feedList = document.getElementById("feed-list");
  var feedCount = document.getElementById("feed-count");
  var n = 1;
  function renderFeed() {
    feedList.innerHTML = "";
    for (var i = 0; i < n; i++) {
      var card = document.createElement("div");
      card.style.cssText = "border:2px solid var(--line-ink); border-radius:var(--radius-asym-md); padding:12px 14px; background:var(--orange-50); animation:shh-drop 260ms var(--ease-pop, ease-out) both";
      card.innerHTML = '<p style="margin:0 0 4px; font-size:var(--text-micro); font-weight:var(--weight-bold); letter-spacing:var(--tracking-caps); text-transform:uppercase; color:var(--text-faint)">anónimo</p>' +
        '<p style="margin:0; font-size:var(--text-body-md); font-weight:var(--weight-semibold); line-height:var(--leading-snug); color:var(--text-strong)">' + pool[i] + "</p>";
      feedList.appendChild(card);
    }
    var note = document.createElement("p");
    note.style.cssText = "margin:auto 0 0; font-size:var(--text-micro); color:var(--text-faint); letter-spacing:var(--tracking-caps); text-transform:uppercase";
    note.textContent = "demo · así se llena tu buzón";
    feedList.appendChild(note);
    feedCount.textContent = n;
  }
  renderFeed();
  setInterval(function () {
    n = n >= pool.length ? 1 : n + 1;
    renderFeed();
  }, 2200);

  // Theme picker — 6 asset seed themes, swaps the preview card's palette + pattern + sticker.
  var themes = [
    { label: "coquette", cls: "theme-coquette", pattern: "var(--pattern-scallop)", opacity: .3 },
    { label: "y2k", cls: "theme-y2k", pattern: "var(--pattern-grid)", opacity: .3, sticker: "{{ asset('assets/y2k-fantasy-flower.webp') }}" },
    { label: "grunge", cls: "theme-grunge", pattern: "var(--pattern-noise)", opacity: .22, sticker: "{{ asset('assets/y2k-eye-face.webp') }}" },
    { label: "cottagecore", cls: "theme-cottagecore", pattern: "var(--pattern-waves)", opacity: .28 },
    { label: "clean girl", cls: "theme-clean-girl", pattern: "var(--pattern-dots)", opacity: .3 },
    { label: "gothic", cls: "theme-gothic", pattern: "url('{{ asset('assets/patterns/gothic-damask-portrait.webp') }}') 0 0/180px auto repeat", opacity: .5, sticker: "{{ asset('assets/y2k-eye-face.webp') }}" }
  ];
  var tabsEl = document.getElementById("theme-tabs");
  var previewEl = document.getElementById("theme-preview");
  var patternEl = document.getElementById("theme-pattern");
  var stickerEl = document.getElementById("theme-sticker");
  var activeTheme = 1;

  themes.forEach(function (t, i) {
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "theme-tab" + (i === activeTheme ? " is-active" : "");
    btn.textContent = t.label;
    btn.addEventListener("click", function () { selectTheme(i); });
    tabsEl.appendChild(btn);
  });

  function selectTheme(i) {
    if (i === activeTheme) return;
    activeTheme = i;
    Array.prototype.forEach.call(tabsEl.children, function (btn, idx) {
      btn.classList.toggle("is-active", idx === i);
    });
    previewEl.className = themes[i].cls;
    patternEl.style.opacity = themes[i].opacity;
    patternEl.style.background = themes[i].pattern;
    if (themes[i].sticker) {
      stickerEl.src = themes[i].sticker;
      stickerEl.style.display = "block";
    } else {
      stickerEl.style.display = "none";
    }
  }
  selectTheme(1); // y2k is the default preview, matches the design's initial state

  // FAQ accordion — single item open at a time.
  var faqs = [
    { q: "¿De verdad es anónimo?", a: "Sí. Quien te escribe no queda registrado: ni nombre, ni cuenta, ni ubicación. Vos ves el mensaje, nada más." },
    { q: "¿Puedo saber quién me escribió?", a: "No, y esa es la idea. Lo único que podés ver es la pista que la persona decida dejarte." },
    { q: "¿Cuánto cuesta?", a: "Nada. Se descarga gratis en Google Play y el buzón es gratis para siempre." },
    { q: "¿Cómo lo comparto en Instagram?", a: "Copiás tu link y lo pegás como sticker en tu story. Quien lo toque entra directo a tu buzón." },
    { q: "¿Puedo borrar mi cuenta?", a: "Sí, desde la app o desde el link de eliminar cuenta acá abajo. Se va todo." }
  ];
  var faqList = document.getElementById("faq-list");
  var openIndex = 0;
  var faqItems = [];

  faqs.forEach(function (f, i) {
    var item = document.createElement("div");
    item.className = "faq-item" + (i === openIndex ? " is-open" : "");
    item.innerHTML = '<button type="button" class="faq-q"><span>' + f.q + '</span><span class="faq-sign">' + (i === openIndex ? "-" : "+") + '</span></button>' +
      '<p class="faq-a">' + f.a + "</p>";
    item.querySelector(".faq-q").addEventListener("click", function () {
      var wasOpen = openIndex === i;
      openIndex = wasOpen ? -1 : i;
      faqItems.forEach(function (el, idx) {
        el.classList.toggle("is-open", idx === openIndex);
        el.querySelector(".faq-sign").textContent = idx === openIndex ? "-" : "+";
      });
    });
    faqList.appendChild(item);
    faqItems.push(item);
  });

  // Reveal-on-scroll for [data-reveal] elements.
  if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches && "IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.setAttribute("data-revealed", ""); io.unobserve(e.target); }
      });
    }, { threshold: 0.25, rootMargin: "0px 0px -8% 0px" });
    document.querySelectorAll("[data-reveal]").forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll("[data-reveal]").forEach(function (el) { el.setAttribute("data-revealed", ""); });
  }
})();
</script>
</body>
</html>
