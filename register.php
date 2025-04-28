<?php require_once __DIR__ . '/php/config.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Swedish Recipes</title>
  <link rel="stylesheet" href="css/auth.css">
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="auth-wrapper">
    <div class="auth-container">
      <h2>Register</h2>
      <form id="register-form" onsubmit="return validateTerms()">
    <label>Full Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <div class="password-wrapper">
        <input type="password" name="password" id="register-password" required>
        <span class="toggle-password" onclick="togglePassword('register-password', this)">👁️</span>
    </div>
    <small id="password-requirements">Password must be at least 12 characters, include 1 number, and 1 uppercase letter.</small>
    <div id="password-strength"></div>

    <div style="margin-top: 20px; text-align: left;">
        <input type="checkbox" id="agree-terms">
        <label for="agree-terms">I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a></label>
    </div>

    <button type="submit" class="btn">Register</button>
</form>

      <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>

  <div id="toast" class="toast"></div>
  
  <script src="js/auth.js"></script>
  <script src="js/script.js"></script>

  <script>
function validateTerms() {
    const checkbox = document.getElementById('agree-terms');
    if (!checkbox.checked) {
        showToast("You can't sign up until you agree to the Terms and Conditions");
        return false;
    }
    return true;
}

// If you don't already have showToast:
function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (input.type === 'password') {
      input.type = 'text';
      btn.textContent = '🙈';
    } else {
      input.type = 'password';
      btn.textContent = '👁️';
    }
  }

   // live strength meter
   const pwInput = document.getElementById('register-password');
  const pwReq   = document.getElementById('password-requirements');
  const pwStr   = document.getElementById('password-strength');
  if (pwInput) {
    pwInput.addEventListener('input', () => {
      const v = pwInput.value;
      let met = 0;
      if (v.length >= 12) met++;
      if (/[A-Z]/.test(v)) met++;
      if (/\d/.test(v))    met++;
      // render three segments
      pwStr.innerHTML = `
        <div class="strength-bar">
          <span class="${met>=1?'met':''}"></span>
          <span class="${met>=2?'met':''}"></span>
          <span class="${met>=3?'met':''}"></span>
        </div>
        <div class="strength-text">
          ${met===3 ? '✅ Strong password' : '❗ Needs improvement'}
        </div>`;
    });
  }
</script>
</body>
</html>