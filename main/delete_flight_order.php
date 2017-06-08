<?php
include('../connect.php');

$url       = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$str_query = parse_url($url, PHP_URL_QUERY);

$flight_purchase_id = $_GET['flight_purchase_id'];
$booking_id         = $_GET['booking_id'];

/**
 * @param $booking_id
 * @param null $flight_purchase_id
 */
function deleteFlightBooking($booking_id, $flight_purchase_id = null) {
    global $db;
    if($flight_purchase_id == null) {
        $result = $db->prepare("DELETE FROM flight_bookings WHERE id= :booking_id");
        $result->bindParam(':booking_id', $booking_id);
    } else {
        $result = $db->prepare("DELETE FROM flight_bookings WHERE flight_purchase_id= :flight_purchase_id");
        $result->bindParam(':flight_purchase_id', $flight_purchase_id);
    }
    $result->execute();
}

if($flight_purchase_id > 0) {
    $result = $db->prepare("DELETE FROM flight_purchases WHERE id= :flight_purchase_id");
    $result->bindParam(':flight_purchase_id', $flight_purchase_id);
    $result->execute();

    deleteFlightBooking(null, $flight_purchase_id);
    $str_query = str_replace('flight_purchase_id=' . $flight_purchase_id, "", $str_query);

} else if ($booking_id > 0) {

    deleteFlightBooking($booking_id);
    $str_query = str_replace('booking_id=' . $booking_id, "", $str_query);
}

$location = sprintf("flight_picker.php?%s", $str_query);

?>
<script type="text/javascript">
    window.location = '<?=$location?>';
</script>
