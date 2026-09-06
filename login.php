<?php
require "includes/auth.php";
require "includes/csrf.php";
require "includes/db.php";
require "includes/layout.php";
$error = "";

if (isset($_GET['timeout'])) {
    $error = "You were logged out due to inactivity. Please log in again.";
}

$MAX_ATTEMPTS = 5;
$LOCK_MINUTES = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
        $minutesLeft = ceil((strtotime($user['locked_until']) - time()) / 60);
        $error = "Account locked due to too many failed attempts. Try again in $minutesLeft minute(s).";
    } elseif ($user && password_verify($password, $user['password_hash'])) {
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: watchlist/index.php");
        exit;
    } else {
        if ($user) {
            $attempts = $user['failed_attempts'] + 1;
            if ($attempts >= $MAX_ATTEMPTS) {
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
                $stmt->execute([$attempts, $LOCK_MINUTES, $user['id']]);
                $error = "Too many failed attempts. Account locked for $LOCK_MINUTES minutes.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                $stmt->execute([$attempts, $user['id']]);
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}

page_start('Login');
?>
    <h2>Login</h2>
    <div class="card">
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <label>Username <input type="text" name="username" required autocomplete="username"></label>
            <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
            <button type="submit">Login</button>
        </form>
        <p>No account? <a href="register.php">Register</a></p>
    </div>
<?php page_end(); ?>
