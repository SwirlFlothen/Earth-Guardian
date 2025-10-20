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

// Quiz interactions: highlight selected answers and update progress
document.addEventListener('DOMContentLoaded', function(){
    const quizForm = document.querySelector('form[action^="take_quiz.php"]');
    if (!quizForm) return;

    const progressBar = document.querySelector('.quiz-card .progress-bar');
    const radioInputs = quizForm.querySelectorAll('input[type="radio"]');

    function updateSelectedStates() {
        // clear previous selected classes
        quizForm.querySelectorAll('.answer-label').forEach(label => label.classList.remove('selected'));

        // set selected class on labels whose radio is checked
        radioInputs.forEach(radio => {
            if (radio.checked) {
                const label = radio.closest('label');
                if (label) label.classList.add('selected');
            }
        });

        // update progress
        const questions = new Set(Array.from(radioInputs).map(r => r.name));
        let answered = 0;
        questions.forEach(qname => {
            const checked = quizForm.querySelector('input[name="' + qname + '"]:checked');
            if (checked) answered++;
        });
        const total = questions.size || 1;
        const pct = Math.round((answered / total) * 100);
        if (progressBar) progressBar.style.width = pct + '%';
    }

    // initial update in case of prefilled values
    updateSelectedStates();

    radioInputs.forEach(radio => {
        radio.addEventListener('change', updateSelectedStates);
    });
});

// Stepper navigation for quiz (one question at a time)
document.addEventListener('DOMContentLoaded', function(){
    const steps = Array.from(document.querySelectorAll('.step'));
    if (!steps.length) return;

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.querySelector('.quiz-card .progress-bar');

    let current = 0;

    function showStep(index) {
        steps.forEach((s, i) => {
            if (i === index) {
                s.style.display = 'block';
                s.setAttribute('data-active', 'true');
            } else {
                s.style.display = 'none';
                s.removeAttribute('data-active');
            }
        });
        current = index;
        prevBtn.disabled = (current === 0);
        nextBtn.style.display = (current === steps.length - 1) ? 'none' : 'inline-block';
        submitBtn.style.display = (current === steps.length - 1) ? 'inline-block' : 'none';
        // update progress
        const pct = Math.round(((current) / (steps.length - 1)) * 100);
        if (progressBar) progressBar.style.width = pct + '%';
    }

    showStep(0);

    prevBtn.addEventListener('click', () => {
        if (current > 0) showStep(current - 1);
    });

    nextBtn.addEventListener('click', () => {
        if (current < steps.length - 1) showStep(current + 1);
    });

    // allow clicking an answer to auto-advance after short delay
    document.querySelectorAll('.answer-label input[type="radio"]').forEach(r => {
        r.addEventListener('change', () => {
            // small delay so selection is visible
            setTimeout(() => {
                if (current < steps.length - 1) showStep(current + 1);
            }, 220);
        });
    });
});
