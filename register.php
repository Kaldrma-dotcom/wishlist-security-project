<?php
require "includes/auth.php";
require "includes/csrf.php";
require "includes/db.php";
require "includes/layout.php";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === "" || $password === "") {
        $error = "Username and password are required.";
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $error = "Username must be 3–50 characters (letters, numbers, underscore).";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            header("Location: login.php");
            exit;
        }
    }
}

page_start('Register');
?>
    <h2>Register</h2>
    <div class="card">
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <label>Username <input type="text" name="username" required minlength="3" maxlength="50" autocomplete="username"></label>
            <label>Password (min 8 characters) <input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
<?php page_end(); ?>
