<?php
session_name("ExpenseTracker"); session_start();
session_unset();
session_destroy();
header("location: index.php");
exit;
?>
