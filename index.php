<?php
require "includes/auth.php";

if (isset($_SESSION['user_id'])) {
    header('Location: ' . APP_BASE . '/watchlist/index.php');
} else {
    header('Location: ' . APP_BASE . '/login.php');
}
exit;
