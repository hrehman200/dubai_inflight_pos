<?php
// configuration
include_once('../connect.php');

// new data
$id           = $_POST['memi'];
$a            = $_POST['name'];
$b            = $_POST['address'];
$c            = $_POST['contact'];
$d            = $_POST['cperson'];
$e            = $_POST['note'];
$email        = $_POST['email'];
$category     = $_POST['category'];
$payment_term = $_POST['payment_term'];
// query
$sql = "UPDATE supliers 
        SET suplier_name=?, suplier_address=?, suplier_contact=?, contact_person=?, note=?, email=?, category=?, payment_term=?
		WHERE suplier_id=?";
$q   = $db->prepare($sql);
$q->execute(array($a, $b, $c, $d, $e, $email, $category, $payment_term, $id));
header("location: supplier.php");

?>