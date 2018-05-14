<?php
include_once('../connect.php');

$url       = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$str_query = parse_url($url, PHP_URL_QUERY);

$flight_purchase_id = $_GET['flight_purchase_id'];
$booking_id         = $_GET['booking_id'];
$from_flight_purchase_id = $_GET['fromFlightPurchaseId'];

if ($flight_purchase_id > 0) {
    deleteFlightPurchase($flight_purchase_id);
    $str_query = str_replace('flight_purchase_id=' . $flight_purchase_id, "", $str_query);

} else if ($booking_id > 0) {
    deleteFlightBooking($booking_id);
    $str_query = str_replace('booking_id=' . $booking_id, "", $str_query);
}

if(isset($_SESSION['CUSTOMER_ID']) || ENV == PRODUCTION) {
    $location = sprintf("store.php?%s", $str_query);
} else {
    $location = sprintf("flight_picker.php?%s", $str_query);
}

?>
<script type="text/javascript">
    window.location = '<?=$location?>';
</script>
