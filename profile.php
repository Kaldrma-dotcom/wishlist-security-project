<?php
require "includes/auth.php";
require_login();
require "includes/csrf.php";
require "includes/db.php";
require "includes/layout.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        clear_session_cookie();
        header("Location: " . APP_BASE . "/register.php?deleted=1");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $newUsername = trim($_POST['username'] ?? '');
        $newPassword = $_POST['password'] ?? '';

        if ($newUsername === "") {
            $error = "Username cannot be empty.";
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $newUsername)) {
            $error = "Username must be 3–50 characters (letters, numbers, underscore).";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$newUsername, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = "That username is already taken.";
            } else {
                if ($newPassword !== "") {
                    if (strlen($newPassword) < 8) {
                        $error = "New password must be at least 8 characters.";
                    } else {
                        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, password_hash = ? WHERE id = ?");
                        $stmt->execute([$newUsername, $hash, $_SESSION['user_id']]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $stmt->execute([$newUsername, $_SESSION['user_id']]);
                }

                if (!$error) {
                    $_SESSION['username'] = $newUsername;
                    $success = "Profile updated successfully.";
                }
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

page_start('My Profile', APP_BASE . '/assets/confirm.js');
?>
    <h2>My Profile</h2>
    <p class="nav"><a href="watchlist/index.php">Watchlist</a> | <a href="logout.php">Logout</a></p>

    <div class="card">
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

        <h3>Update Profile</h3>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_profile">
            <label>Username <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required minlength="3" maxlength="50"></label>
            <label>New Password (leave blank to keep current) <input type="password" name="password" minlength="8" autocomplete="new-password"></label>
            <button type="submit">Save Changes</button>
        </form>
    </div>

    <div class="card">
        <h3>Delete account</h3>
        <p class="muted">This permanently deletes your account and watchlist.</p>
        <form method="POST" data-confirm="This will permanently delete your account and watchlist. Are you sure?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_account">
            <button type="submit" class="danger">Delete My Account</button>
        </form>
    </div>
<?php page_end(); ?>
