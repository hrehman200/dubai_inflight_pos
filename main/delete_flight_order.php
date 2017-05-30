<?php
include('../connect.php');

$transaction_id = $_GET['transaction_id'];

$result = $db->prepare("DELETE FROM sales_order WHERE transaction_id= :transaction_id");
$result->bindParam(':transaction_id', $transaction_id);
$result->execute();

$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$str_query = parse_url($url, PHP_URL_QUERY);

$str_query = str_replace('transaction_id='.$transaction_id, "", $str_query);

$location = sprintf("location: flight_picker.php?".$str_query);

header($location);
?>