<?php require_once __DIR__ . '/php/config.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Swedish Recipes</title>
  <link rel="stylesheet" href="css/auth.css">
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="auth-wrapper">
    <div class="container">
      <h2>Login</h2>
      <form id="login-form">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <div class="password-wrapper">
          <input type="password" name="password" id="login-password" required>
          <span class="toggle-password" onclick="togglePassword('login-password', this)">👁️</span>
        </div>

        <button class="btn" type="submit">Login</button>
      </form>
      <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
  </div>

  <?php include 'partials/footer.php'; ?>

  <div id="toast" class="toast"></div>

  <script src="js/auth.js"></script>
  <script src="js/script.js"></script>
  
</body>
</html>
