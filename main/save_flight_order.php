<?php
session_start();
include_once('../connect.php');

$invoice            = trim($_POST['invoice']);
if($invoice == '') {
    echo '<h3>No invoice number (RS-.....) set. Go back to select flight package.</h3>';
    exit();
}

$flight_offer_id    = $_POST['flightOffer'];
$flight_time        = $_POST['flightDate'] . " " . $_POST['flightTime'] . ":00";
$offer_duration     = $_POST['offerDuration'];
$flight_duration    = $_POST['flightDuration'];
$flight_purchase_id = $_POST['flightPurchaseId'];
$customer_id        = $_POST['customerId'];
if(is_null($customer_id) || $customer_id == 0) {
    echo '<h3>No customer selected for this order. Please go back and select customer.</h3>';
    exit();
}

$creditDuration = $_POST['creditDuration'];
$useCredit      = $_POST['useCredit'];

$from_flight_purchase_id = $_POST['fromFlightPurchaseId'];
$is_class_session        = $_POST['chkClassSession'] == 1;
$class_people            = (int)$_POST['txtClassPeople'];

// make sure class-people set to at least 1
$query = $db->prepare('SELECT offer_name FROM flight_offers WHERE id = ? LIMIT 1');
$query->execute(array($flight_offer_id));
$row = $query->fetch();
if(stripos($row['offer_name'], 'class session') !== false && $class_people == 0) {
    $class_people = 1;
}

if ($_POST['useBalance'] == 1 && $_POST['useCredit'] == 0) {
    // insert balance use
    $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id, 1, 0, $class_people);
    insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration, $from_flight_purchase_id);
    updateCustomerFlightBalance($customer_id, $from_flight_purchase_id, $flight_duration);
} else if ($_POST['useCredit'] == 1 && $_POST['useBalance'] == 0) {
    $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id, 2, 0, $class_people);
    insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);
    updateCustomerCredits($customer_id, $flight_duration);
} else {

    if ($flight_purchase_id > 0) {

        // if we are deducting from credit (from same purchase)
        $fp = getFlightPurchase($flight_purchase_id);
        if($fp['deduct_from_balance'] == 2) {
            updateCustomerCredits($customer_id, $flight_duration);
        }

        insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);

    } else {
        $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id, 0, 0, $class_people);
        insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);
    }

    $query = $db->prepare('SELECT id FROM flight_purchases WHERE invoice_id = ?');
    $query->execute([$invoice]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    $flight_purchase_ids = array_map(function($item) {return $item['id']; }, $rows);

    $query = $db->prepare("SELECT SUM(duration) AS booked_duration FROM flight_bookings WHERE flight_purchase_id IN (
      ".implode(',', $flight_purchase_ids)."
    )");
    $query->execute();
    $row             = $query->fetch();
    $booked_duration = $row['booked_duration'];

    $query = $db->prepare('SELECT SUM(fo.duration) AS offer_duration 
      FROM flight_purchases fp
      INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
      WHERE fp.id IN ('.implode(',', $flight_purchase_ids).')');
    $query->execute();
    $row             = $query->fetch();
    $offer_duration = $row['offer_duration'];

    $minutes = $offer_duration - $booked_duration;

    if($minutes == 0) {
        updateCustomerFlightBalance($customer_id, $flight_purchase_id, $minutes, false, true);
    } else {
        $query = $db->prepare('SELECT id FROM flight_purchases WHERE invoice_id = ?');
        $query->execute([$invoice]);
        while($row = $query->fetch()) {
            updateCustomerFlightBalance($customer_id, $row['id'], $minutes);
        }
    }
}

$location = sprintf("location: flight_picker.php?id=%s&invoice=%s&pkg_id=%s&offer_id=%s&customer_id=%s&customer_name=%s&date=%s&t=%s&pkg_name=%s", $flight_purchase_id, $invoice, $_POST['pkg_id'], $_POST['flightOffer'], $_POST['customerId'], $_POST['customer'], $_POST['flightDate'], $_POST['giveaway_token'], $_POST['pkg_name']);

header($location);


?>
