# Student Admission Portal - Question 1: Student Registration

## 1. Purpose of the Module
This module is the first phase of the **Student Admission Portal** college Web Technology assignment. It provides a secure, intuitive, and attractive client-side Student Registration interface built strictly with pure HTML5, CSS3, and JavaScript without external frameworks or backend dependencies.

---

## 2. Technologies Used
- **HTML5**: Semantic document structure, labels, form controls, and input elements.
- **CSS3**: Responsive flexbox layout, custom styling, card centering, blue and purple gradient theme, subtle drop-shadows, and validation states.
- **JavaScript (ES6)**: Real-time and submit-time form validation, regex pattern matching, file format & size verification, dynamic DOM error display, and form reset handling.

---

## 3. How to Run It
This project runs completely offline and requires no npm installation, server setup, or internet access.

1. Open your file explorer and navigate to:
   ```
   web assign-2 ak/Q1_Registration/
   ```
2. Double-click **`index.html`** or right-click and choose **Open with -> Google Chrome / Microsoft Edge / Firefox**.
3. Alternatively, if using VS Code, right-click `index.html` and select **"Open with Live Server"**.

---

## 4. Features Implemented

### A. Form Fields
- **Student Full Name** (Text input)
- **Roll Number / Register Number** (Text input)
- **Email Address** (Email input)
- **Course Selection** (Dropdown with CSE, IT, AIDS, ECE, Mech)
- **Password** (Masked password input)
- **Confirm Password** (Masked password input)
- **File Upload** (Accepts photo/document)
- **Action Buttons**: "Register Now" (Submit) and "Reset Form" (Reset)

### B. Validation Rules
- **Non-Empty Fields**: All required fields must be filled.
- **Name Validation**: Cannot be empty or contain only whitespace; must be at least 2 characters.
- **Roll Number**: Cannot be empty or only spaces.
- **Email Format**: Validated against standard email pattern (`name@domain.com`).
- **Strong Password Rules**: Must contain at least 8 characters including at least one uppercase letter, one lowercase letter, one numeric digit, and one special symbol (`!@#$%^&*` etc.).
- **Password Match**: Confirm password field must match the password exactly.
- **File Constraints**:
  - File selection is mandatory.
  - Allowed file formats: **JPG, JPEG, PNG, PDF**.
  - Maximum allowable file size: **2 MB**.
- **Page Reload Prevention**: Form submission prevents standard HTTP submission (`event.preventDefault()`) and stays on the page to display real-time client-side feedback.
- **Realtime Feedback**: Errors clear dynamically as users correct invalid inputs.
- **Success Banner**: Clear banner with applicant summary displayed upon passing all validation checks.
