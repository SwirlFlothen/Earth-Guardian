document.addEventListener('DOMContentLoaded', function() {
    const quizForm = document.getElementById('quizForm');
    const steps = document.querySelectorAll('.step');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.querySelector('.progress-bar');
    let currentStep = 0;
    const totalSteps = steps.length;

    // Initialize progress bar
    function updateProgressBar() {
        const progress = ((currentStep + 1) / totalSteps) * 100;
        progressBar.style.width = `${progress}%`;
    }

    // Show current step
    function showStep(stepIndex) {
        steps.forEach((step, index) => {
            step.style.display = index === stepIndex ? 'block' : 'none';
            step.setAttribute('data-active', index === stepIndex);
        });

        prevBtn.disabled = stepIndex === 0;
        nextBtn.style.display = stepIndex === totalSteps - 1 ? 'none' : 'block';
        submitBtn.style.display = stepIndex === totalSteps - 1 ? 'block' : 'none';

        updateProgressBar();
    }

    // Navigation event handlers
    prevBtn.addEventListener('click', () => {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    });

    nextBtn.addEventListener('click', () => {
        const currentStepElement = steps[currentStep];
        const radioInputs = currentStepElement.querySelectorAll('input[type="radio"]');
        let answered = false;

        radioInputs.forEach(input => {
            if (input.checked) {
                answered = true;
            }
        });

        if (!answered) {
            alert('Please select an answer before proceeding.');
            return;
        }

        if (currentStep < totalSteps - 1) {
            currentStep++;
            showStep(currentStep);
        }
    });

    // Handle answer selection animation
    document.querySelectorAll('.answer-label').forEach(label => {
        label.addEventListener('click', function() {
            const currentStepElement = steps[currentStep];
            currentStepElement.querySelectorAll('.answer-label').forEach(l => {
                l.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });

    // Form submission validation
    quizForm.addEventListener('submit', function(e) {
        const currentStepElement = steps[currentStep];
        const radioInputs = currentStepElement.querySelectorAll('input[type="radio"]');
        let answered = false;

        radioInputs.forEach(input => {
            if (input.checked) {
                answered = true;
            }
        });

        if (!answered) {
            e.preventDefault();
            alert('Please answer all questions before submitting.');
        }
    });

    // Initialize first step
    showStep(currentStep);
});