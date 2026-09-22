{{-- BFINZ website — landing page (Coming Soon). --}}
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BFINZ — Coming Soon</title>

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">
    <!-- Bootstrap -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css"
        rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,300..700;1,300..700&family=Figtree:ital,wght@0,300..900;1,300..900&family=Handlee&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=League+Spartan:wght@100..900&family=Lora:ital,wght@0,400..700;1,400..700&family=Merienda:wght@300..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Sans+Tamil:wght@100..900&family=Onest:wght@100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Poetsen+One&family=Quicksand:wght@300..700&family=Red+Hat+Display:ital,wght@0,300..900;1,300..900&family=Sora:wght@100..800&family=Spline+Sans:wght@300..700&family=Wix+Madefor+Display:wght@400..800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #ffcc00;
            --secondary: #ff9500;
            --dark: #05070b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #05070b;
            color: #fff;
            overflow: hidden;
        }

        a {
            text-decoration: none;
        }

        /* =========================
           HERO SECTION
        ==========================*/

        .hero-section {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        /* =========================
           BACKGROUND
        ==========================*/

        .hero-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;

            /* animation:
                zoomBg 16s ease-in-out infinite alternate; */
        }

        .overlay {
            position: absolute;
            inset: 0;

            background:
                linear-gradient(to right,
                    rgba(0, 0, 0, 0.22),
                    rgba(0, 0, 0, 0.28)),

                linear-gradient(to top,
                    rgba(0, 0, 0, 0.52),
                    transparent);

            z-index: 1;
        }

        @keyframes zoomBg {

            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.08);
            }

        }

        /* =========================
           FLOATING GOLD COINS
        ==========================*/

        .coin {
            position: absolute;
            top: -100px;

            color: #ffcc00;

            opacity: 0.12;

            animation:
                fall linear infinite;

            z-index: 2;
        }

        .coin i {
            font-size: 50px;
        }

        .coin:nth-child(1) {
            left: 8%;
            animation-duration: 12s;
        }

        .coin:nth-child(2) {
            left: 18%;
            animation-duration: 18s;
            animation-delay: 2s;
        }

        .coin:nth-child(3) {
            left: 32%;
            animation-duration: 15s;
        }

        .coin:nth-child(4) {
            left: 48%;
            animation-duration: 20s;
            animation-delay: 3s;
        }

        .coin:nth-child(5) {
            left: 62%;
            animation-duration: 14s;
        }

        .coin:nth-child(6) {
            left: 75%;
            animation-duration: 17s;
            animation-delay: 1s;
        }

        .coin:nth-child(7) {
            left: 88%;
            animation-duration: 13s;
        }

        @keyframes fall {

            from {
                transform:
                    translateY(-120px) rotate(0deg);
            }

            to {
                transform:
                    translateY(120vh) rotate(360deg);
            }

        }

        /* =========================
           GLOW
        ==========================*/

        .glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            z-index: 1;
        }

        .glow-1 {
            width: 320px;
            height: 320px;
            background: #ffcc00;
            top: -100px;
            left: -100px;
        }

        .glow-2 {
            width: 280px;
            height: 280px;
            background: #ff9500;
            bottom: -100px;
            right: -100px;
        }

        /* =========================
           HEADER
        ==========================*/

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 99;
            padding: 26px 0;
        }

        .header-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo {
            font-family: "Instrument Sans", sans-serif;
            ;

            font-size: 44px;
            font-weight: 800;

            letter-spacing: -2px;

            background:
                linear-gradient(135deg,
                    #ffcc00,
                    #ff9500);

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* =========================
           CONTENT
        ==========================*/

        .content-wrapper {
            position: relative;
            z-index: 5;

            height: 100vh;

            display: flex;
            align-items: center;
            justify-content: start;

            text-align: start;
        }

        .sub-title {
            color: rgba(255, 255, 255, 0.7);

            letter-spacing: 4px;

            text-transform: uppercase;

            margin-bottom: 22px;

            font-size: 14px;

            animation: fadeUp 1s ease;
        }

        .main-title {
            position: relative;

            font-family: "Instrument Sans", sans-serif;
            ;

            font-size: 130px;
            line-height: 0.9;

            font-weight: 800;

            margin-bottom: 26px;

            animation: fadeUp 1.1s ease;
        }

        /* SHADOW TEXT */

        .main-title::after {

            content: "COMING SOON";

            position: absolute;

            bottom: -290px;
            left: 50%;
            transform: translate(-50%, -50%);

            width: 100%;

            color: rgba(255, 255, 255, 0.03);

            z-index: -1;
        }

        .main-title span {

            background:
                linear-gradient(135deg,
                    #ffcc00,
                    #ff9500);

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .description {
            max-width: 700px;
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 0px;

            color: rgba(255, 255, 255, 0.72);

            animation: fadeUp 1.2s ease;
        }

        /* =========================
           ROCKET
        ==========================*/

        .rocket-wrapper {
            margin-top: 40px;

            animation:
                rocketFloat 3s ease-in-out infinite alternate;
        }

        .rocket {
            font-size: 90px;

            display: inline-block;

            filter:
                drop-shadow(0 0 30px rgba(255, 204, 0, 0.35));
        }

        @keyframes rocketFloat {

            from {
                transform:
                    translateY(0px) rotate(-5deg);
            }

            to {
                transform:
                    translateY(-18px) rotate(5deg);
            }

        }

        /* =========================
           ANIMATION
        ==========================*/

        @keyframes fadeUp {

            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        /* =========================
           RESPONSIVE
        ==========================*/

        @media(max-width:991px) {

            .main-title {
                font-size: 100px;
            }

        }

        @media(max-width:767px) {

            body {
                overflow: auto;
            }

            .logo {
                font-size: 34px;
            }

            .sub-title {
                font-size: 12px;
                letter-spacing: 2px;
            }

            .main-title {
                font-size: 72px;
                line-height: 0.95;
            }

            .main-title::after {
                top: 10px;
                left: 10px;
            }

            .description {
                font-size: 15px;
                max-width: 100%;
            }

            .rocket {
                font-size: 70px;
            }

        }

        @media(max-width:480px) {

            .main-title {
                font-size: 52px;
            }

            .description {
                font-size: 14px;
            }

            .rocket {
                font-size: 58px;
            }

            .launch-badge {
                font-size: 10px !important;
            }

        }

        .header-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .launch-badge {
            padding: 12px 20px;
            border-radius: 60px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(14px);
            font-size: 14px;
            color: #fff;
        }

        /* =========================
   ROCKET LOADER
==========================*/

        .rocket-loader {
            position: fixed;
            inset: 0;

            background: #05070b;

            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;

            z-index: 999999;
        }

        /* =========================
   COUNTDOWN
==========================*/

        .countdown {
            position: absolute;
            top: 22%;
            left: 50%;

            transform: translateX(-50%);

            width: 120px;
            height: 120px;
        }

        .countdown span {
            position: absolute;

            inset: 0;

            font-size: 110px;
            font-weight: 800;

            font-family: 'Syne', sans-serif;

            color: #ffcc00;

            text-align: center;

            opacity: 0;

            text-shadow:
                0 0 30px rgba(255, 204, 0, 0.5);
        }

        /* =========================
   ROCKET
==========================*/

        .rocket-wrap {
            position: relative;
            width: 100px;
            height: 220px;
        }

        .rocket {
            position: absolute;

            width: 100px;
            height: 170px;

            background: linear-gradient(180deg,
                    #ffffff,
                    #dfe6e9);

            border-radius: 50px 50px 20px 20px;

            left: 50%;

            transform: translateX(-50%);

            overflow: hidden;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.35);
        }

        /* TOP */

        .rocket::before {
            content: '';

            position: absolute;

            top: -45px;
            left: 0;

            width: 100%;
            height: 90px;

            background: #ff9500;

            border-radius: 50%;
        }

        /* WINDOW */

        .rocket-window {
            position: absolute;

            width: 34px;
            height: 34px;

            border-radius: 50%;

            background: #9AECDB;

            border: 5px solid #ff9500;

            top: 58px;
            left: 50%;

            transform: translateX(-50%);
        }

        /* WINGS */

        .rocket-wing {
            position: absolute;

            width: 42px;
            height: 65px;

            background: #ff9500;

            bottom: 15px;

            z-index: -1;
        }

        .left-wing {
            left: -26px;

            border-radius:
                100% 0 0 0;

            transform: rotate(-28deg);
        }

        .right-wing {
            right: -26px;

            border-radius:
                0 100% 0 0;

            transform: rotate(28deg);
        }

        /* FIRE */

        .rocket-fire {
            position: absolute;

            width: 28px;
            height: 28px;

            background: #ff9500;

            bottom: -20px;
            left: 50%;

            transform:
                translateX(-50%) rotate(45deg);

            border-radius:
                80% 0 55% 50%;
        }

        .rocket-fire::after {
            content: '';

            position: absolute;

            inset: 5px;

            background: #ffcc00;

            border-radius:
                80% 0 55% 50%;
        }

        /* =========================
   SMOKE
==========================*/

        .smoke {
            position: absolute;

            width: 22px;
            height: 22px;

            background: #ffffff;

            border-radius: 50%;

            opacity: 0.4;

            filter: blur(2px);

            bottom: 15px;
        }

        .smoke-left {
            left: -45px;
        }

        .smoke-right {
            right: -45px;
        }

        /* =========================
   REMOVE LOADER
==========================*/

        .rocket-loader.hide {
            opacity: 0;
            visibility: hidden;

            transition: 1s ease;
        }
    </style>

</head>

<body>

    <!-- =========================
     ROCKET LOADER
==========================-->

    <!-- <div class="rocket-loader">

        <div class="countdown">

            <span>3</span>
            <span>2</span>
            <span>1</span>

        </div>

        <div class="rocket-wrap">

            <div class="rocket">

                <div class="rocket-window"></div>

                <div class="rocket-wing left-wing"></div>
                <div class="rocket-wing right-wing"></div>

                <div class="rocket-fire"></div>

            </div>


            <div class="smoke smoke-left"></div>
            <div class="smoke smoke-right"></div>

        </div>

    </div> -->

    <section class="hero-section">

        <!-- BG -->

        <img src="{{ asset('assets/img/bg-img.png') }}" alt=""
            class="hero-bg">

        <div class="overlay"></div>

        <!-- FLOATING COINS -->

        <div class="coin"><i class="ri-coin-line"></i></div>
        <div class="coin"><i class="ri-exchange-dollar-line"></i></div>
        <div class="coin"><i class="ri-stock-line"></i></div>
        <div class="coin"><i class="ri-coins-line"></i></div>
        <div class="coin"><i class="ri-money-dollar-circle-line"></i></div>
        <div class="coin"><i class="ri-funds-line"></i></div>
        <div class="coin"><i class="ri-safe-2-line"></i></div>

        <!-- GLOW -->

        <div class="glow glow-1"></div>
        <div class="glow glow-2"></div>

        <!-- HEADER -->

        <header>

            <div class="container">

                <div class="header-wrap">

                    <div class="logo">
                        BFINZ
                    </div>

                    <div class="launch-badge">
                        BFINZ App Launching Soon
                    </div>

                </div>

            </div>

        </header>
        <!-- CONTENT -->

        <div class="content-wrapper">

            <div class="container">

                <div class="sub-title">
                    [ FINANCE / GOLD RATE / INVESTMENT ]
                </div>

                <h1 class="main-title">

                    COMING <br>

                    <span>SOON</span>

                </h1>

                <p class="description">

                    BFINZ is building a smarter financial platform
                    for gold rates, investment tracking,
                    and next-generation financial insights.

                </p>


            </div>

        </div>

    </section>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>

    <script>
        const tl = gsap.timeline();

        // Countdown

        tl.to(".countdown span:nth-child(1)", {
                opacity: 1,
                scale: 1.2,
                duration: 0.5
            })

            .to(".countdown span:nth-child(1)", {
                opacity: 0,
                scale: 0.5,
                duration: 0.4
            })

            .to(".countdown span:nth-child(2)", {
                opacity: 1,
                scale: 1.2,
                duration: 0.5
            })

            .to(".countdown span:nth-child(2)", {
                opacity: 0,
                scale: 0.5,
                duration: 0.4
            })

            .to(".countdown span:nth-child(3)", {
                opacity: 1,
                scale: 1.2,
                duration: 0.5
            })

            .to(".countdown span:nth-child(3)", {
                opacity: 0,
                scale: 0.5,
                duration: 0.4
            })

            // Rocket Shake

            .to(".rocket", {
                x: "+=4",
                repeat: 10,
                yoyo: true,
                duration: 0.05
            })

            // Fire Pulse

            .to(".rocket-fire", {
                scale: 1.5,
                repeat: 8,
                yoyo: true,
                duration: 0.08
            }, "-=0.8")

            // Smoke

            .to(".smoke-left", {
                x: -40,
                y: 30,
                scale: 2,
                opacity: 0,
                duration: 1
            }, "-=0.8")

            .to(".smoke-right", {
                x: 40,
                y: 30,
                scale: 2,
                opacity: 0,
                duration: 1
            }, "-=1")

            // Rocket Launch

            .to(".rocket-wrap", {
                y: -window.innerHeight - 300,
                duration: 2,
                ease: "power4.in"
            })

            // Remove Loader

            .to(".rocket-loader", {
                opacity: 0,
                duration: 0.8,
                onComplete: () => {

                    // document
                    //     .querySelector(".rocket-loader")
                    //     .remove();

                }
            });
    </script>

</body>

</html>