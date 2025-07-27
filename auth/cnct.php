<?php

$host="localhost";
$username="mynsuxy1_admin";
$password="n#,h#VxrB{cz";
$dbname="mynsuxy1_main";

try{
    $conn= new mysqli($host,$username,$password,$dbname);
}
catch(Exception $e){
    
    header("Location: ../index.php");
    exit();
}

?>