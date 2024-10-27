function validateForm() {
    var prelim = parseFloat(document.getElementById("prelim").value);
    var midterm = parseFloat(document.getElementById("midterm").value);
    var ffg = parseFloat(document.getElementById("ffg").value);

    if (prelim < 1.0 || prelim > 5.0 || midterm < 1.0 || midterm > 5.0 || ffg < 1.0 || ffg > 5.0) {
        alert("Error: Invalid input. Please enter a value between 1.0 and 5.0.");
        return false;
    } else {
        return true;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var form = document.querySelector("form");
    form.addEventListener("submit", function(event) {
        if (!validateForm()) {
            event.preventDefault();
        }
    });
});