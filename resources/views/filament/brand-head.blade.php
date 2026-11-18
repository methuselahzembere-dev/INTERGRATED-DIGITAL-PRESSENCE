<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
<style>
    :root {
        --zp-green: #12a53b;
        --zp-yellow: #ffd200;
        --zp-red: #d62027;
        --zp-black: #111111;
        --zp-serif: 'Source Serif 4', Georgia, 'Times New Roman', serif;
        --zp-muted: var(--gray-700);
    }
    .dark { --zp-muted: var(--gray-300); }

    /* One serif voice for the brand, page headings and the motto. */
    .fi-header-heading,
    .fi-simple-header-heading {
        font-family: var(--zp-serif);
        font-weight: 400;
        letter-spacing: -0.02em;
    }

    /* ---- Circular emblem ----
       Source logo is 1200x630. The circle shows a 700px square centred on the
       emblem (trees + monolith); the banner above and the motto/stripe below are
       clipped away so the seal reads at any size. */
    .zp-brand { display: flex; align-items: center; gap: 0.75rem; --zp-seal: 2.5rem; }
    .zp-seal {
        position: relative;
        flex: none;
        display: block;
        width: var(--zp-seal);
        aspect-ratio: 1;
        border-radius: 50%;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 0 0 2px var(--zp-yellow), 0 0 0 3.5px var(--zp-green);
        transition: transform 0.2s ease;
    }
    .zp-seal img {
        position: absolute;
        width: 171.43%;
        max-width: none;
        height: auto;
        left: -32.14%;
        top: 6.43%;
        clip-path: inset(17.5% 0 18.3% 0);
    }
    a:hover > .zp-brand .zp-seal { transform: scale(1.04); }

    .zp-brand-text { display: flex; flex-direction: column; line-height: 1.15; }
    .zp-brand-name { font-family: var(--zp-serif); font-weight: 600; font-size: 1.05rem; letter-spacing: -0.01em; }
    .zp-brand-sub { font-size: 0.7rem; font-weight: 400; color: var(--zp-muted); }
    .zp-brand-motto { display: none; }

    /* ---- Login: large, stacked, centred ---- */
    .fi-simple-layout .zp-brand { flex-direction: column; text-align: center; gap: 1rem; --zp-seal: 7rem; }
    .fi-simple-layout .zp-brand-text { align-items: center; gap: 0.2rem; }
    .fi-simple-layout .zp-brand-name { font-size: 1.75rem; font-weight: 400; letter-spacing: -0.02em; }
    .fi-simple-layout .zp-brand-sub { font-size: 0.875rem; }
    .fi-simple-layout .zp-brand-motto { display: block; margin-top: 0.5rem; }

    /* ---- Motto: plain serif, no quotes, a stripe taken from the logo's own flag band ---- */
    .zp-motto,
    .fi-simple-layout .zp-brand-motto {
        font-family: var(--zp-serif);
        font-size: 1.125rem;
        font-weight: 400;
        letter-spacing: 0.01em;
        color: var(--zp-muted);
    }
    .zp-motto { display: inline-block; }
    .zp-motto::after,
    .fi-simple-layout .zp-brand-motto::after {
        content: '';
        display: block;
        width: 5.25rem;
        height: 3px;
        margin-top: 0.5rem;
        border-radius: 2px;
        background: linear-gradient(90deg,
            var(--zp-green) 0 14.28%, var(--zp-yellow) 0 28.57%, var(--zp-red) 0 42.85%,
            var(--zp-black) 0 57.14%,
            var(--zp-red) 0 71.42%, var(--zp-yellow) 0 85.71%, var(--zp-green) 0);
        box-shadow: 0 0 0 1px rgba(128, 128, 128, 0.25);
    }
    .fi-simple-layout .zp-brand-motto::after { margin-inline: auto; }

    @media (prefers-reduced-motion: reduce) {
        .zp-seal { transition: none; }
        a:hover > .zp-brand .zp-seal { transform: none; }
    }
</style>
