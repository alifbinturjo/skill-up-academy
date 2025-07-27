<?php
session_start();
include'cnct.php';

session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out</title>
</head>
<body>
    
</body>
</html>
<?php
$conn->close();
header("Location: ../index.php");
exit();
?>