<?php

$host="localhost";
$username="root";
$password="";
$dbname="skillup_academy";

try{
    $conn= new mysqli($host,$username,$password,$dbname);
}
catch(Exception $e){
    $conn->close();
    header("Location: ../index.php");
    exit();
}

?>