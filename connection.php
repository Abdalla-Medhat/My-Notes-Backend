<?php
  /* include necessary functions */
  include "funs.php";
  
//all headers
//here we declare who can access our API server
header("Access-Control-Allow-Origin: *");
//allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Access-Control-Allow-Origin");
//allow specific methods
header("Access-Control-Allow-Methods: POST, OPTIONS , GET");

checkAuthenticate() ; 

//database source name
$dsn= "mysql:host=example;dbname=example;charset=utf8mb4";
$user="example";
$pass="example";
//To deal with Arabic characters 
$option = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"); 
try{
    //instance from PDO
    $con = new PDO($dsn,$user,$pass,$option);
    $con->exec("SET NAMES utf8mb4");
    $con->exec("SET CHARACTER SET utf8mb4");

    //dedicate how PDO handle errors
    //When an error occurs, PDO throws an exception
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){
    echo $e->getMessage();
}   
?>