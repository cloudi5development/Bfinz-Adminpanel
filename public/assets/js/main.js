const words = [
    "Transformation",
    "Analytics",
    "Operations",
    "Advisory",
    "Growth",
    "Performance"
];

let wordIndex = 0;
let charIndex = 0;
let isDeleting = false;

const typingElement = document.getElementById("typing-text");

function typeEffect() {

    const currentWord = words[wordIndex];

    if (!isDeleting) {

        typingElement.textContent =
            currentWord.substring(0, charIndex + 1);

        charIndex++;

        if (charIndex === currentWord.length) {

            isDeleting = true;

            setTimeout(typeEffect, 1400);

            return;
        }

    } else {

        typingElement.textContent =
            currentWord.substring(0, charIndex - 1);

        charIndex--;

        if (charIndex === 0) {

            isDeleting = false;

            wordIndex = (wordIndex + 1) % words.length;
        }
    }

    setTimeout(typeEffect, isDeleting ? 60 : 120);
}

typeEffect();


window.addEventListener("load", () => {

    const loader = document.querySelector(".page-loader");

    setTimeout(() => {

        loader.classList.add("loaded");

        setTimeout(() => {

            loader.remove();

        }, 1800);

    }, 2000);

});

// Launch Confetti Blast

function launchBlast() {

    const colors = [
        '#ff0000', // Red
        '#ffa500', // Orange
        '#ffff00', // Yellow
        '#008000', // Green
        '#0000ff', // Blue
        '#4b0082', // Indigo
        '#ee82ee'
    ];

    // LEFT SIDE

    confetti({
        particleCount: 60,
        angle: 65,
        spread: 70,
        startVelocity: 55,
        origin: {
            x: 0,
            y: 1
        },
        colors: colors,
        scalar: 1,
        gravity: 1,
        ticks: 220
    });

    // RIGHT SIDE

    confetti({
        particleCount: 80,
        angle: 115,
        spread: 70,
        startVelocity: 55,
        origin: {
            x: 1,
            y: 1
        },
        colors: colors,
        scalar: 1,
        gravity: 1,
        ticks: 220
    });

}

// AFTER LOADER OPENS

window.addEventListener("load", () => {

    const loader =
        document.querySelector(".page-loader");

    setTimeout(() => {

        loader.classList.add("loaded");

        // Trigger Confetti

        setTimeout(() => {

            launchBlast();

        }, 1200);

        // Remove Loader

        setTimeout(() => {

            loader.remove();

        }, 2200);

    }, 2000);

});