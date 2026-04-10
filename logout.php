<?php
session_start();

// Fshi të gjitha variablat e sesionit
$_SESSION = array();

// Fshi cookie-n e sesionit
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Shkatërro sesionin
session_destroy();

// Ridrejto në faqen kryesore
header('Location: index.php');
exit();
?>