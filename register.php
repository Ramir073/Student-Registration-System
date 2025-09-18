<?php
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $password === '' || $password_confirm === '') {
        $message = 'Please fill in all fields.';
    } elseif ($password !== $password_confirm) {
        $message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } else {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $message = 'Username already taken.';
        } else {
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert->execute([$username, $password_hash]);
            $message = 'Registration successful. You can now <a href="login.php">log in</a>.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Registration</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 400px; margin: 40px auto; padding: 0 20px; background: #f9f9f9; color: #333; }
    h1 { text-align: center; color: #2c3e50; }
    form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    label { display: block; margin-top: 10px; font-weight: bold; }
    input[type="text"], input[type="password"] {
        width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
    }
    button {
        margin-top: 15px; width: 100%; background: #2980b9; color: white; border: none; padding: 12px; font-size: 1rem; border-radius: 4px; cursor: pointer;
        transition: background-color 0.3s;
    }
    button:hover { background: #1c5980; }
    .message {
        margin: 20px 0; padding: 10px; border-radius: 4px;
    }
    .error { background: #e74c3c; color: white; }
    .success { background: #27ae60; color: white; }
    a { color: #2980b9; text-decoration: none; }
</style>
</head>
<body>
    <h1>User Registration</h1>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username <span style="color:#e74c3c;">*</span></label>
        <input type="text" id="username" name="username" required />

        <label for="password">Password <span style="color:#e74c3c;">*</span></label>
        <input type="password" id="password" name="password" required minlength="6" />

        <label for="password_confirm">Confirm Password <span style="color:#e74c3c;">*</span></label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="6" />

        <button type="submit">Register</button>
    </form>

    <p style="text-align:center; margin-top: 15px;">
        Already have an account? <a href="login.php">Log in here</a>.
    </p>
</body>
</html>