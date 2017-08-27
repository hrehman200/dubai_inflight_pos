<?php
session_start();
include('../connect.php');

$invoice            = $_POST['invoice'];
$flight_offer_id    = $_POST['flightOffer'];
$flight_time        = $_POST['flightDate'] . " " . $_POST['flightTime'] . ":00";
$offer_duration     = $_POST['offerDuration'];
$flight_duration    = $_POST['flightDuration'];
$flight_purchase_id = $_POST['flightPurchaseId'];
$customer_id        = $_POST['customerId'];

$creditDuration = $_POST['creditDuration'];
$useCredit      = $_POST['useCredit'];

$from_flight_purchase_id = $_POST['fromFlightPurchaseId'];
$is_class_session        = $_POST['chkClassSession'] == 1;
$class_people            = $_POST['txtClassPeople'];

//print_r($creditDuration);
// print_r($useCredit);
//print_r($_POST['useBalance']);

//exit();


if ($_POST['useBalance'] == 1 && $_POST['useCredit'] == 0) {
    // insert balance use
    $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id, 1, 0, $class_people);
    insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);
    updateCustomerFlightBalance($customer_id, $from_flight_purchase_id, $flight_duration);
} else if ($_POST['useCredit'] == 1 && $_POST['useBalance'] == 0) {
    $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id, 2, 0, $class_people);
    insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);
    deductFromCreditTime($customer_id, $flight_offer_id, $flight_duration, $creditDuration);
} else {

    if ($flight_purchase_id > 0) {
        insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);

    } else {
        $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id, 0, 0, $class_people);
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
