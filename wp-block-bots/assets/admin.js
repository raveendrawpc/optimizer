document.addEventListener("DOMContentLoaded", function () {
    let inputField = document.getElementById("blocked_bots");

    if (inputField) {
        inputField.addEventListener("input", function () {
            this.value = this.value.replace(/[^a-zA-Z0-9, ]/g, ""); // Allow only letters, numbers, and commas
        });
    }
});
