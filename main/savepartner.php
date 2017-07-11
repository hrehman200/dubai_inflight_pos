<?php
session_start();
include('../connect.php');
$a            = $_POST['name'];
$b            = $_POST['address'];
$c            = $_POST['contact'];
$d            = $_POST['cperson'];
$e            = $_POST['note'];
$email        = $_POST['email'];
$category     = $_POST['category'];
$payment_term = $_POST['payment_term'];
$discount     = $_POST['discount'];

// query
$sql = "INSERT INTO partners (partner_name,partner_address,partner_contact,contact_person,note,email,category,payment_term,discount)
VALUES (:a,:b,:c,:d,:e,:email,:category,:payment_term,:discount)";
$q   = $db->prepare($sql);
$q->execute(array(':a'            => $a, ':b' => $b, ':c' => $c, ':d' => $d, ':e' => $e,
                  ':email'        => $email,
                  ':category'     => $category,
                  ':payment_term' => $payment_term,
                  ':discount'     => $discount
));
header("location: partners.php");


?>