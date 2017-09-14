<?php
session_start();
include_once('../connect.php');
$a = $_POST['name'];
$b = $_POST['address'];
$c = $_POST['contact'];
$d = $_POST['memno'];
$e = $_POST['prod_name'];
$f = $_POST['note'];
$g = $_POST['date'];

$user_credit_time = $_POST['user_credit_time'];
$user_credit_cash= $_POST['user_credit_cash'];
$purchased_date = $_POST['purchased_date'];

// query
$sql = "INSERT INTO customer (customer_name, address, contact, membership_number, prod_name, note, expected_date, credit_time, credit_cash, purchased_date) 
		VALUES (:a, :b, :c, :d, :e, :f, :g, :credit_time,  :credit_cash,  :purchased_date )";

$q = $db->prepare($sql);
$q->execute(array(':a'=>$a,':b'=>$b,':c'=>$c,':d'=>$d,':e'=>$e,':f'=>$f, ':g'=>$g, ':credit_time'=>$user_credit_time, ':credit_cash'=>$user_credit_cash, ':purchased_date'=>$purchased_date));
header("location: customer.php");


// query
/*$sql = "INSERT INTO customer (customer_name,address,contact,membership_number,prod_name,note,expected_date) VALUES (:a,:b,:c,:d,:e,:f,:g)";
$q = $db->prepare($sql);
$q->execute(array(':a'=>$a,':b'=>$b,':c'=>$c,':d'=>$d,':e'=>$e,':f'=>$f,':g'=>$g));
header("location: customer.php");

*/
?>