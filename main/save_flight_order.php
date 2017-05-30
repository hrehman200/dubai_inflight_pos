<?php
session_start();
include('../connect.php');

$result   = $db->prepare("SELECT fo.*, fp.package_name FROM flight_offers fo
    INNER JOIN flight_packages fp ON fo.package_id = fp.id
    WHERE fo.id = :offerId");
$result->bindParam(':offerId', $_POST['flightOffer']);
$result->execute();

$price = '';
$code = '';
$gen = '';
$name = '';

for ($i = 0; $row = $result->fetch(); $i++) {
    $price = $row['price'];
    $code  = $row['code'];
    $gen   = $row['package_name'];
    $name  = $row['offer_name'];
}

$invoice = $_POST['invoice'];
$flight_offer_id = $_POST['flightOffer'];
$flight_date = $_POST['flightDate'];
$flight_time = $_POST['flightTime'];
$flight_duration = $_POST['flightDuration'];
$offer_duration = $_POST['offerDuration'];

// query
$sql = "INSERT INTO sales_order
  (invoice, qty, name, price, product_code, gen_name, date, flight_offer_id, flight_date, flight_time, flight_duration)
  VALUES
  (:invoice, :quantity, :name, :price, :code, :gen, :date, :flight_offer_id, :flight_date, :flight_time, :flight_duration)";
$q   = $db->prepare($sql);
$arr = array(
    ':invoice' => $invoice,
    ':quantity' => $offer_duration,
    ':name' => $name,
    ':price' => $price,
    ':code' => $code,
    ':gen' => $gen,
    ':date' => date('Y/m/d'),
    ':flight_offer_id' => $flight_offer_id,
    ':flight_date' => $flight_date,
    ':flight_time' => $flight_time,
    ':flight_duration' => $flight_duration,
);
$q->execute($arr);

$sales_order_id = $db->lastInsertId();

$location = sprintf("location: flight_picker.php?id=%s&invoice=%s&pkg_id=%s&offer_id=%s&customer_id=%s&date=%s", $sales_order_id, $invoice, $_POST['pkg_id'], $flight_offer_id, $_POST['customer'], $flight_date);

header($location);


?>