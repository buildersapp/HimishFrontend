function validateStep2() {

    const hiddenStartDate = document.getElementById('hiddenStartDate').value.trim();
    if (hiddenStartDate === "") {
        $.toastr.error('Please select Date.', {position: 'top-center', time: 5000});
        return false;
    }

    const boostPlanSelect = document.getElementById('boostPlanSelect').value.trim();
    if (boostPlanSelect === "") {
        $.toastr.error('Please select plan.', {position: 'top-center', time: 5000});
        return false;
    }

    const boostQuantityInput = document.getElementById('boostQuantityInput').value.trim();
    if (Number(boostQuantityInput) === 0) {
        $.toastr.error('Duration must be at least one unit.', {position: 'top-center', time: 5000});
        return false;
    }

    return true;
}

function validateForm() {
    return true;
}


function nextStep(step, setField = null) {
    if (step === 2 && !validateStep2()) return;

    document.querySelectorAll(".form-step").forEach((el) => (el.style.display = "none"));
    document.getElementById("step" + step).style.display = "block";
    updateStepsUI(step);
}

function prevStep(step) {
    nextStep(step);
}

function updateStepsUI(activeStep) {
    document.querySelectorAll(".single-step").forEach((step, index) => {
        if (index + 1 <= activeStep) step.classList.add("step-active");
        else step.classList.remove("step-active");
    });
}