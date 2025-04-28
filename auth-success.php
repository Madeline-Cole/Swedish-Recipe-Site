<?php include 'partials/nav.php'; ?>
<?php
$message = $_GET['msg'] ?? 'You have successfully completed the action.';
$redirectTo = $_GET['redirect'] ?? 'login.php'; // default redirect
$delay = intval($_GET['delay'] ?? 4); // in seconds
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success - Swedish Recipes</title>
    <link rel="stylesheet" href="css/auth.css">
    <style>
    .success-page {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f8ff;
        text-align: center;
        padding: 40px 20px;
    }

    .success-container {
        background: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        max-width: 450px;
        width: 100%;
    }

    .success-container h1 {
        color: #28a745;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    .success-container p {
        font-size: 1.1rem;
        color: #555;
        margin-bottom: 20px;
    }

    .success-container a {
        display: inline-block;
        padding: 10px 20px;
        background: #006AA7;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: background 0.3s ease;
    }

    .success-container a:hover {
        background: #005b94;
    }
    </style>
</head>
<body>
    <div class="success-page">
        <div class="success-container">
            <h1>🎉 Success!</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="<?php echo htmlspecialchars($redirectTo); ?>">Continue</a>
        </div>
    </div>
    <script src="js/script.js"></script>
    <script>
        setTimeout(() => {
            window.location.href = "<?php echo htmlspecialchars($redirectTo); ?>";
        }, <?php echo $delay * 1000; ?>);
    </script>
</body>
</html>