<?php
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = 'Please enter both username and password.';
    } else {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $message = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Login</title>
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
    a { color: #2980b9; text-decoration: none; }
</style>
</head>
<body>
    <h1>User Login</h1>

    <?php if ($message): ?>
        <div class="message error">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username <span style="color:#e74c3c;">*</span></label>
        <input type="text" id="username" name="username" required />

        <label for="password">Password <span style="color:#e74c3c;">*</span></label>
        <input type="password" id="password" name="password" required />

        <button type="submit">Log In</button>
    </form>

    <p style="text-align:center; margin-top: 15px;">
        Don't have an account? <a href="register.php">Register here</a>.
    </p>
</body>
</html>