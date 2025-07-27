<?php
session_start();
include'cnct.php';
session_unset();
session_destroy();
$conn->close();
header("Location: ../index.php");
exit();
?>