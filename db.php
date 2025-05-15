<?php
$host='localhost';
$username='root';
$password='';
$db='restaurant';

$conn=mysqli_connect($host,$username,$password,$db);

if(!$conn){
    die("Database Connection Failed ". mysqli_connect_error());
}
echo "Connected Successfully";