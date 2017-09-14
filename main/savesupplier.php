<?php
session_start();
include_once('../connect.php');
$a            = $_POST['name'];
$b            = $_POST['address'];
$c            = $_POST['contact'];
$d            = $_POST['cperson'];
$e            = $_POST['note'];
$email        = $_POST['email'];
$category     = $_POST['category'];
$payment_term = $_POST['payment_term'];
// query
$sql = "INSERT INTO supliers (suplier_name,suplier_address,suplier_contact,contact_person,note,email,category,payment_term)
  VALUES (:a,:b,:c,:d,:e,:email,:category,:payment_term)";
$q   = $db->prepare($sql);
$q->execute(array(
    ':a'            => $a,
    ':b'            => $b,
    ':c'            => $c,
    ':d'            => $d,
    ':e'            => $e,
    ':email'        => $email,
    ':category'     => $category,
    ':payment_term' => $payment_term
));
header("location: supplier.php");


?>