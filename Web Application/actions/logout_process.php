<?php
session_start();
session_unset(); 
session_destroy();

if(isset($_SESSION['expiry_status']))
{
    $_SESSION['status'] = "Session Expired";
}
else
{
    $_SESSION['status'] = "Logged out successfully";
}
header('Location: ../public/login.php');
exit();
?>
