/**
 * Student Admission Portal - Question 1
 * Client-side Form Validation Script
 */

document.addEventListener("DOMContentLoaded", () => {
    // -------------------------------------------------------------------------
    // 1. DOM Element References
    // -------------------------------------------------------------------------
    const registrationForm = document.getElementById("registrationForm");
    const successBanner = document.getElementById("successBanner");
    const successHeading = document.getElementById("successHeading");
    const successMessage = document.getElementById("successMessage");
    const btnReset = document.getElementById("btnReset");

    // Input fields
    const studentName = document.getElementById("studentName");
    const rollNumber = document.getElementById("rollNumber");
    const email = document.getElementById("email");
    const course = document.getElementById("course");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const documentFile = document.getElementById("documentFile");

    // Error message elements
    const nameError = document.getElementById("nameError");
    const rollError = document.getElementById("rollError");
    const emailError = document.getElementById("emailError");
    const courseError = document.getElementById("courseError");
    const passwordError = document.getElementById("passwordError");
    const confirmPasswordError = document.getElementById("confirmPasswordError");
    const fileError = document.getElementById("fileError");

    // -------------------------------------------------------------------------
    // 2. Helper Functions for UI Feedback
    // -------------------------------------------------------------------------

    /**
     * Marks an input as invalid and displays an error message.
     * @param {HTMLElement} inputElement - Form input field
     * @param {HTMLElement} errorElement - Corresponding error span
     * @param {string} message - Validation message
     */
    function showError(inputElement, errorElement, message) {
        inputElement.classList.add("is-invalid");
        inputElement.classList.remove("is-valid");
        errorElement.textContent = message;
    }

    /**
     * Clears error state and marks an input as valid.
     * @param {HTMLElement} inputElement - Form input field
     * @param {HTMLElement} errorElement - Corresponding error span
     */
    function clearError(inputElement, errorElement) {
        inputElement.classList.remove("is-invalid");
        inputElement.classList.add("is-valid");
        errorElement.textContent = "";
    }

    /**
     * Resets all validation classes and error texts.
     */
    function resetAllValidation() {
        const inputs = [studentName, rollNumber, email, course, password, confirmPassword, documentFile];
        const errorSpans = [nameError, rollError, emailError, courseError, passwordError, confirmPasswordError, fileError];

        inputs.forEach(input => {
            input.classList.remove("is-invalid", "is-valid");
        });

        errorSpans.forEach(span => {
            span.textContent = "";
        });

        successBanner.style.display = "none";
    }

    // -------------------------------------------------------------------------
    // 3. Validation Logic Functions
    // -------------------------------------------------------------------------

    // Validate Student Name (not empty, not just whitespace)
    function validateName() {
        const val = studentName.value.trim();
        if (val === "") {
            showError(studentName, nameError, "Student name is required.");
            return false;
        }
        if (val.length < 2) {
            showError(studentName, nameError, "Name must be at least 2 characters.");
            return false;
        }
        clearError(studentName, nameError);
        return true;
    }

    // Validate Roll Number (not empty)
    function validateRollNumber() {
        const val = rollNumber.value.trim();
        if (val === "") {
            showError(rollNumber, rollError, "Roll number / Register number is required.");
            return false;
        }
        clearError(rollNumber, rollError);
        return true;
    }

    // Validate Email Address (format check)
    function validateEmail() {
        const val = email.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (val === "") {
            showError(email, emailError, "Email address is required.");
            return false;
        }
        if (!emailRegex.test(val)) {
            showError(email, emailError, "Please enter a valid email address (e.g., student@domain.com).");
            return false;
        }
        clearError(email, emailError);
        return true;
    }

    // Validate Course Selection (must select an option)
    function validateCourse() {
        const val = course.value;
        if (!val || val === "") {
            showError(course, courseError, "Please select a course / department.");
            return false;
        }
        clearError(course, courseError);
        return true;
    }

    // Validate Password (min 8 chars, uppercase, lowercase, number, special char)
    function validatePassword() {
        const val = password.value;

        if (val === "") {
            showError(password, passwordError, "Password is required.");
            return false;
        }
        if (val.length < 8) {
            showError(password, passwordError, "Password must be at least 8 characters long.");
            return false;
        }

        const hasUpperCase = /[A-Z]/.test(val);
        const hasLowerCase = /[a-z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val);

        if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar) {
            showError(
                password, 
                passwordError, 
                "Password must contain uppercase, lowercase, a number, and a special character."
            );
            return false;
        }

        clearError(password, passwordError);
        return true;
    }

    // Validate Confirm Password (must match Password)
    function validateConfirmPassword() {
        const passVal = password.value;
        const confirmVal = confirmPassword.value;

        if (confirmVal === "") {
            showError(confirmPassword, confirmPasswordError, "Please confirm your password.");
            return false;
        }
        if (confirmVal !== passVal) {
            showError(confirmPassword, confirmPasswordError, "Passwords do not match.");
            return false;
        }
        clearError(confirmPassword, confirmPasswordError);
        return true;
    }

    // Validate File Upload (selected, JPG/JPEG/PNG/PDF, max 2MB)
    function validateFile() {
        const files = documentFile.files;

        // 1. Must be selected
        if (!files || files.length === 0) {
            showError(documentFile, fileError, "Please upload a document or photograph.");
            return false;
        }

        const file = files[0];
        const allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
        const fileExtension = file.name.split(".").pop().toLowerCase();

        // 2. Check file type
        if (!allowedExtensions.includes(fileExtension)) {
            showError(
                documentFile, 
                fileError, 
                "Invalid file format! Only JPG, JPEG, PNG, and PDF files are allowed."
            );
            return false;
        }

        // 3. Check file size (2 MB = 2 * 1024 * 1024 bytes)
        const maxSizeBytes = 2 * 1024 * 1024;
        if (file.size > maxSizeBytes) {
            showError(
                documentFile, 
                fileError, 
                "File size exceeds 2 MB limit! Current size: " + (file.size / (1024 * 1024)).toFixed(2) + " MB."
            );
            return false;
        }

        clearError(documentFile, fileError);
        return true;
    }

    // -------------------------------------------------------------------------
    // 4. Realtime Validation Listeners (Input & Change Events)
    // -------------------------------------------------------------------------
    studentName.addEventListener("input", () => {
        if (studentName.classList.contains("is-invalid")) validateName();
    });

    rollNumber.addEventListener("input", () => {
        if (rollNumber.classList.contains("is-invalid")) validateRollNumber();
    });

    email.addEventListener("input", () => {
        if (email.classList.contains("is-invalid")) validateEmail();
    });

    course.addEventListener("change", () => {
        if (course.classList.contains("is-invalid")) validateCourse();
    });

    password.addEventListener("input", () => {
        if (password.classList.contains("is-invalid")) validatePassword();
        // Also re-validate confirm password if it already has content
        if (confirmPassword.value.length > 0) validateConfirmPassword();
    });

    confirmPassword.addEventListener("input", () => {
        if (confirmPassword.classList.contains("is-invalid")) validateConfirmPassword();
    });

    documentFile.addEventListener("change", () => {
        validateFile();
    });

    // -------------------------------------------------------------------------
    // 5. Reset Button Event Listener
    // -------------------------------------------------------------------------
    btnReset.addEventListener("click", () => {
        // Small timeout allows form native reset to complete before wiping error classes
        setTimeout(() => {
            resetAllValidation();
        }, 10);
    });

    // -------------------------------------------------------------------------
    // 6. Form Submission Handling
    // -------------------------------------------------------------------------
    registrationForm.addEventListener("submit", (event) => {
        // Prevent default submission / page reload
        event.preventDefault();

        // Run all field validations
        const isNameValid = validateName();
        const isRollValid = validateRollNumber();
        const isEmailValid = validateEmail();
        const isCourseValid = validateCourse();
        const isPassValid = validatePassword();
        const isConfirmPassValid = validateConfirmPassword();
        const isFileValid = validateFile();

        // Check if all criteria are satisfied
        const isFormValid = isNameValid &&
                            isRollValid &&
                            isEmailValid &&
                            isCourseValid &&
                            isPassValid &&
                            isConfirmPassValid &&
                            isFileValid;

        if (isFormValid) {
            // Display prominent success banner with student registration details
            successHeading.textContent = "Registration Successful!";
            successMessage.innerHTML = `
                Welcome <strong>${studentName.value.trim()}</strong> (Roll No: <strong>${rollNumber.value.trim()}</strong>)!<br>
                Your application for <strong>${course.value}</strong> has passed all client-side validation checks.
            `;
            successBanner.style.display = "flex";

            // Scroll smoothly to top of card so user immediately sees the success feedback
            successBanner.scrollIntoView({ behavior: "smooth", block: "nearest" });

            // Log details for viva / teacher testing inspection
            console.log("=== Student Registration Successful ===");
            console.log("Name:", studentName.value.trim());
            console.log("Roll Number:", rollNumber.value.trim());
            console.log("Email:", email.value.trim());
            console.log("Course:", course.value);
            console.log("File Name:", documentFile.files[0].name);
            console.log("File Size:", (documentFile.files[0].size / 1024).toFixed(2), "KB");
        } else {
            // Hide success banner if validation fails
            successBanner.style.display = "none";

            // Focus on first invalid input field for improved UX
            const firstInvalid = document.querySelector(".form-control.is-invalid");
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});
