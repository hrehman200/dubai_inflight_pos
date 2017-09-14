<?php
// configuration
include_once('../connect.php');

// new data
$id = $_POST['memi'];
$a = $_POST['name'];
$b = $_POST['address'];
$c = $_POST['contact'];
$d = $_POST['cperson'];
$e = $_POST['note'];
// query
$sql = "UPDATE partners 
        SET partner_name=?, partner_address=?, partner_contact=?, contact_person=?, note=?
		WHERE partner_id=?";
$q = $db->prepare($sql);
$q->execute(array($a,$b,$c,$d,$e,$id));
header("location: partners.php");

?>