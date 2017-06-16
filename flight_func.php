<?php

/**
 * @param $flight_purchase_id
 */
function deleteFlightPurchase($flight_purchase_id) {
    global $db;

    deleteFlightBooking(null, $flight_purchase_id);

    $result = $db->prepare("DELETE FROM flight_purchases WHERE id= :flight_purchase_id");
    $result->bindParam(':flight_purchase_id', $flight_purchase_id);
    $result->execute();
}

/**
 * @param $booking_id
 * @param null $flight_purchase_id
 */
function deleteFlightBooking($booking_id, $flight_purchase_id = null) {
    global $db;
    if ($flight_purchase_id == null) {

        addBalance($booking_id);

        $result = $db->prepare("DELETE FROM flight_bookings WHERE id= :booking_id");
        $result->bindParam(':booking_id', $booking_id);
        $result->execute();

    } else {

        $result = $db->prepare('SELECT id FROM flight_bookings WHERE flight_purchase_id= :flight_purchase_id');
        $result->execute(array(
            ':flight_purchase_id' => $flight_purchase_id
        ));

        while($row = $result->fetch()) {
            addBalance($row['id']);
        }

        $result = $db->prepare("DELETE FROM flight_bookings WHERE flight_purchase_id= :flight_purchase_id");
        $result->bindParam(':flight_purchase_id', $flight_purchase_id);
        $result->execute();
    }
}

/**
 * @param $booking_id
 */
function addBalance($booking_id) {
    global $db;

    $query = $db->prepare('SELECT fp.customer_id, fp.flight_offer_id, fb.flight_purchase_id, fb.duration, fo.duration AS offer_minutes
      FROM flight_bookings fb
      INNER JOIN flight_purchases fp ON fb.flight_purchase_id = fp.id
      INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
      WHERE fb.id = :bookingId');
    $query->execute(array(
        ':bookingId' => $booking_id
    ));
    $row = $query->fetch();
    $customer_id = $row['customer_id'];
    $flight_offer_id = $row['flight_offer_id'];
    $balance = $row['duration'];
    $offer_minutes = $row['offer_minutes'];

    $query = $db->prepare('SELECT fc.flight_purchase_id, fc.minutes FROM flight_credits fc
      INNER JOIN flight_purchases fp ON fc.flight_purchase_id = fp.id
      WHERE fc.customer_id = :customer_id AND fp.flight_offer_id = :flightOfferId');
    $query->execute(array(
        ':customer_id' => $customer_id,
        ':flightOfferId' => $flight_offer_id
    ));
    $result = $query->fetchAll();

    foreach($result as $row) {

        if($balance <= $offer_minutes) {
            $balance_to_add_to_row = $balance;
        } else {
            $balance_to_add_to_row = $offer_minutes;
        }

        $query = $db->prepare('UPDATE flight_credits SET minutes = minutes + :balanceToAddToRow
          WHERE customer_id = :customerId AND flight_purchase_id = :flightPurchaseId');
        $query->execute(array(
            ':customerId' => $customer_id,
            ':flightPurchaseId' => $row['flight_purchase_id'],
            ':balanceToAddToRow' => $balance_to_add_to_row
        ));

        $balance -= $balance_to_add_to_row;
        if($balance <= 0) {
            break;
        }
    }
}

/**
 * Rearrays multiple file upload
 *
 * @param $file
 * @return array
 */
function reArrayFiles($file) {
    $file_ary   = array();
    $file_count = count($file['name']);
    $file_key   = array_keys($file);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_key as $val) {
            $file_ary[$i][$val] = $file[$val][$i];
        }
    }

    return $file_ary;
}