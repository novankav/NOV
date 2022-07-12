<?php 
session_start();

$_SESSION["iduser"];
$_SESSION["username"];

unset($_SESSION["iduser"]);
unset($_SESSION["username"]);

session_unset();
session_destroy();

header("location:../index.php");

 ?>