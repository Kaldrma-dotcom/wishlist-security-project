<?php
require "includes/auth.php";
clear_session_cookie();
header("Location: login.php");
exit;
