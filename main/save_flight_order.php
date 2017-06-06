<?php
session_start();
include('../connect.php');

/**
 * @param $customer_id
 * @param $sales_order_id
 * @param $minutes
 */
function updateCustomerFlightBalance($customer_id, $flight_purchase_id, $minutes) {
    global $db;

    $query = $db->prepare('SELECT * FROM flight_credits
                WHERE customer_id = :customer_id AND flight_purchase_id = :flight_purchase_id');
    $query->execute(array(
        ':customer_id'        => $customer_id,
        ':flight_purchase_id' => $flight_purchase_id
    ));
    $row = $query->fetch(PDO::FETCH_ASSOC);

    if($row) {
        $sql = 'UPDATE flight_credits SET minutes = minutes - :minutes
                WHERE customer_id = :customer_id AND flight_purchase_id = :flight_purchase_id';

    } else {
        $sql   = "INSERT INTO flight_credits VALUES(:customer_id, :flight_purchase_id, :minutes)";
    }

    $query = $db->prepare($sql);
    $query->execute(array(
        ':customer_id'        => $customer_id,
        ':flight_purchase_id' => $flight_purchase_id,
        ':minutes'            => $minutes
    ));
}

/**
 * Deducts mentioned amount from flight_credits rows collectively
 * @param $customer_id
 * @param $balance
 */
function deductFromBalance($customer_id, $balance) {
    global $db;

    $query = $db->prepare('SELECT * FROM flight_credits
      WHERE customer_id = :customer_id');
    $query->execute(array(
        ':customer_id' => $customer_id
    ));
    $result = $query->fetchAll();

    foreach($result as $row) {

        if($row['minutes'] >= $balance) {
            $balance_to_deduct_from_row = $balance;
        } else {
            $balance_to_deduct_from_row = $row['minutes'];
        }

        $query = $db->prepare('UPDATE flight_credits SET minutes = minutes - :balanceToDeductFromRow
          WHERE customer_id = :customerId AND flight_purchase_id = :flightPurchaseId');
        $query->execute(array(
            ':customerId' => $customer_id,
            ':flightPurchaseId' => $row['flight_purchase_id'],
            ':balanceToDeductFromRow' => $balance_to_deduct_from_row
        ));

        $balance -= $balance_to_deduct_from_row;
        if($balance <= 0) {
            break;
        }
    }
}

/**
 * @param $flight_offer_id
 * @param $customer_id
 * @return string
 */
function insertFlightPurchase($invoice_id, $flight_offer_id, $customer_id) {
    global $db;

    $sql = "INSERT INTO flight_purchases(invoice_id, flight_offer_id, customer_id)
                VALUES (:invoice_id, :flight_offer_id, :customer_id)";
    $q   = $db->prepare($sql);
    $arr = array(
        ':invoice_id' => $invoice_id,
        ':flight_offer_id' => $flight_offer_id,
        ':customer_id' => $customer_id
    );
    $q->execute($arr);

    $flight_purchase_id = $db->lastInsertId();
    return $flight_purchase_id;
}

/**
 * @param $flight_purchase_id
 * @param $flight_time
 * @param $duration
 * @return string
 */
function insertFlightBooking($flight_purchase_id, $flight_time, $duration) {
    global $db;

    $sql = "INSERT INTO flight_bookings(flight_purchase_id, flight_time, duration)
                VALUES (:flight_purchase_id, :flight_time, :duration)";
    $q   = $db->prepare($sql);
    $arr = array(
        ':flight_purchase_id' => $flight_purchase_id,
        ':flight_time' => $flight_time,
        ':duration' => $duration
    );
    $q->execute($arr);

    $booking_id = $db->lastInsertId();
    return $booking_id;
}

$invoice = $_POST['invoice'];
$flight_offer_id = $_POST['flightOffer'];
$flight_time = $_POST['flightDate'] . " " . $_POST['flightTime'].":00";
$offer_duration = $_POST['offerDuration'];
$flight_duration = $_POST['flightDuration'];
$flight_purchase_id = $_POST['flightPurchaseId'];
$customer_id = $_POST['customerId'];

if($_POST['useBalance'] == 1) {
    // insert balance use
    $flight_offer_id = -1;
    $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id);
    insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);
    deductFromBalance($customer_id, $flight_duration);

} else {

    if ($flight_purchase_id > 0) {
        insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);

    } else {
        $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id);
        insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);
    }

    $query = $db->prepare("SELECT SUM(duration) AS booked_duration FROM flight_bookings WHERE flight_purchase_id=:flightPurchaseId");
    $query->execute(array(
        ':flightPurchaseId' => $flight_purchase_id
    ));
    $row             = $query->fetch();
    $booked_duration = $row['booked_duration'];

    $minutes = $offer_duration - $booked_duration;
    updateCustomerFlightBalance($customer_id, $flight_purchase_id, $minutes);
}

$location = sprintf("location: flight_picker.php?id=%s&invoice=%s&pkg_id=%s&offer_id=%s&customer_id=%s&customer_name=%s&date=%s", $flight_purchase_id, $invoice, $_POST['pkg_id'], $_POST['flightOffer'], $_POST['customerId'], $_POST['customer'], $_POST['flightDate']);

header($location);


?>