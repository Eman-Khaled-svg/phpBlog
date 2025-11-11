<?php
$dsn="mysql:host=localhost;dbname=phpblog";
$user= "root";
$password="";

try{
   $conn= new PDO($dsn,$user,$password);
}catch(PDOException $e){
    die("connection is faild". $e->getMessage());
}
?>