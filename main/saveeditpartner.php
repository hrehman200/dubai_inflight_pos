<?php
// configuration
include('../connect.php');

// new data
$id = $_POST['memi'];
$a = $_POST['name'];
$b = $_POST['address'];
$c = $_POST['contact'];
$d = $_POST['cperson'];
$e = $_POST['note'];
$email        = $_POST['email'];
$category     = $_POST['category'];
$payment_term = $_POST['payment_term'];
$discount     = $_POST['discount'];
// query
$sql = "UPDATE partners 
        SET partner_name=?, partner_address=?, partner_contact=?, contact_person=?, note=?, email=?, category=?, payment_term=?, discount=?
		WHERE partner_id=?";
$q = $db->prepare($sql);
$q->execute(array($a,$b,$c,$d,$e,$email,$category,$payment_term,$discount,$id));
header("location: partners.php");

?>