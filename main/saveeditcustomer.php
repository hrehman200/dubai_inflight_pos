<?php
// configuration
include('../connect.php');

// new data
$id = $_POST['memi'];
$a = $_POST['name'];
$b = $_POST['address'];
$c = $_POST['contact'];
$d = $_POST['memno'];
$e = $_POST['prod_name'];
$f = $_POST['note'];
$g = $_POST['date'];

$user_credit_time = $_POST['user_credit_time'];
$user_credit_cash = $_POST['user_credit_cash'];
$purchased_date = $_POST['purchased_date'];


// query
$sql = "UPDATE customer 
        SET customer_name=?, address=?, contact=?, membership_number=?, prod_name=?, note=?, expected_date=?, credit_time=?,  credit_cash=?,  purchased_date=?
		WHERE customer_id=?";
$q = $db->prepare($sql);
$q->execute(array($a,$b,$c,$d,$e,$f,$g, $user_credit_time, $user_credit_cash, $purchased_date,$id));
header("location: customer.php");

?>