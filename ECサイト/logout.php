<?php
if (isset($_COOKIE["PHPSESSID"])) {
    setcookie("PHPSESSID", '', time() - 1800, '/');
}
session_destroy();
header('Location:./login.php');
