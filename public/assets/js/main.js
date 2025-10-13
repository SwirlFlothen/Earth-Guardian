// Main JS for EarthGuardian
// Handles navbar burger toggle and small UI helpers
document.addEventListener('DOMContentLoaded', function(){
    // Nav burger
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('.nav-links');
    const navLinks = document.querySelectorAll('.nav-links li');

    if (burger && nav) {
        burger.addEventListener('click', () => {
            nav.classList.toggle('nav-active');
            navLinks.forEach((link, index) => {
                if (link.style.animation) {
                    link.style.animation = '';
                } else {
                    link.style.animation = `navLinkFade 0.5s ease forwards ${index / 7 + 0.3}s`;
                }
            });
            burger.classList.toggle('toggle');
        });
    }

    // Animate progress bars found on the page
    const bars = document.querySelectorAll('.progress-bar');
    bars.forEach(bar => {
        // If width already set inline, animate from 0
        const inlineStyle = bar.getAttribute('style');
        if (inlineStyle && inlineStyle.includes('width')) {
            // capture percentage text (e.g. width: 62%;)
            const match = inlineStyle.match(/width:\s*([0-9]+)%/);
            if (match) {
                bar.style.width = '0%';
                requestAnimationFrame(() => {
                    bar.style.width = match[1] + '%';
                });
            }
        }
    });

});
