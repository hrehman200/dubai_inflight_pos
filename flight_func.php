<?php

/**
 * @param $customer_id
 * @param $flight_purchase_id
 * @param $minutes
 * @param bool $add_minutes
 * @param bool $reset_balance If true, don't add/subtract minutes, just reset it to provided $minutes
 */
function updateCustomerFlightBalance($customer_id, $flight_purchase_id, $minutes, $add_minutes = false, $reset_balance = false) {
    global $db;

    $query = $db->prepare('SELECT * FROM flight_credits
        WHERE customer_id = :customer_id AND flight_purchase_id = :flight_purchase_id');
    $query->execute(array(
        ':customer_id'        => $customer_id,
        ':flight_purchase_id' => $flight_purchase_id
    ));
    $row = $query->fetch(PDO::FETCH_ASSOC);

    if ($row) {

        $operator = ($add_minutes) ? '+' : '-';
        if ($reset_balance) {
            $str_minutes = ':minutes';
        } else {
            $str_minutes = 'minutes' . $operator . ':minutes';
        }

        $sql = 'UPDATE flight_credits SET minutes = ' . $str_minutes . '
        WHERE customer_id = :customer_id AND flight_purchase_id = :flight_purchase_id';

    } else {
        $sql = "INSERT INTO flight_credits VALUES(:customer_id, :flight_purchase_id, :minutes)";
    }

    $query = $db->prepare($sql);
    $query->execute(array(
        ':customer_id'        => $customer_id,
        ':flight_purchase_id' => $flight_purchase_id,
        ':minutes'            => $minutes
    ));
}

/**
 * Deducts mentioned amount from flight_credits rows (for same offer) collectively
 * @param $customer_id
 * @param $balance
 */
function deductFromBalance($customer_id, $flight_offer_id, $balance) {
    global $db;

    $query = $db->prepare('SELECT fc.flight_purchase_id, fc.minutes FROM flight_credits fc
        INNER JOIN flight_purchases fp ON fc.flight_purchase_id = fp.id
        WHERE fc.customer_id = :customer_id AND fp.flight_offer_id = :flightOfferId');
    $query->execute(array(
        ':customer_id'   => $customer_id,
        ':flightOfferId' => $flight_offer_id
    ));
    $result = $query->fetchAll();

    foreach ($result as $row) {

        if ($row['minutes'] >= $balance) {
            $balance_to_deduct_from_row = $balance;
        } else {
            $balance_to_deduct_from_row = $row['minutes'];
        }

        $query = $db->prepare('UPDATE flight_credits SET minutes = minutes - :balanceToDeductFromRow
        WHERE customer_id = :customerId AND flight_purchase_id = :flightPurchaseId');
        $query->execute(array(
            ':customerId'             => $customer_id,
            ':flightPurchaseId'       => $row['flight_purchase_id'],
            ':balanceToDeductFromRow' => $balance_to_deduct_from_row
        ));

        $balance -= $balance_to_deduct_from_row;
        if ($balance <= 0) {
            break;
        }
    }
}

/**
 * @param $customer_id
 * @param $balance
 */
function deductFromCreditTime($customer_id, $flight_offer_id, $balance, $creditDuration) {
    global $db;

    $query = $db->prepare("UPDATE customer SET credit_time  = credit_time - :balanceToDeductFromRow WHERE customer_id = :customer_id");
    $query->execute(array(
        ':customer_id'            => $customer_id,
        ':balanceToDeductFromRow' => $balance
    ));
}

/**
 * @param $invoice_id
 * @param $flight_offer_id
 * @param $customer_id
 * @param int $use_balance
 * @param int $status
 * @param int $class_people
 * @return string
 */
function insertFlightPurchase($invoice_id, $flight_offer_id, $customer_id, $use_balance = 0, $status = 0, $class_people = 0) {
    global $db;

    $sql = "INSERT INTO flight_purchases(invoice_id, flight_offer_id, customer_id, deduct_from_balance, status, class_people)
        VALUES (:invoice_id, :flight_offer_id, :customer_id, :use_balance, :status, :class_people)";
    $q   = $db->prepare($sql);
    $arr = array(
        ':invoice_id'      => $invoice_id,
        ':flight_offer_id' => $flight_offer_id,
        ':customer_id'     => $customer_id,
        ':use_balance'     => $use_balance,
        ':status'          => $status,
        ':class_people'    => $class_people
    );
    $q->execute($arr);

    $flight_purchase_id = $db->lastInsertId();

    return $flight_purchase_id;
}

/**
 * @param $flight_purchase_id
 * @param $flight_time
 * @param $duration
 * @param int $from_flight_purchase_id
 * @return string
 */
function insertFlightBooking($flight_purchase_id, $flight_time, $duration, $from_flight_purchase_id = 0) {
    global $db;

    $sql = "INSERT INTO
            flight_bookings(flight_purchase_id, from_flight_purchase_id, flight_time, duration)
            VALUES (:flight_purchase_id, :from_flight_purchase_id, :flight_time, :duration)";
    $q   = $db->prepare($sql);
    $arr = array(
        ':flight_purchase_id'      => $flight_purchase_id,
        ':flight_time'             => $flight_time,
        ':duration'                => $duration,
        ':from_flight_purchase_id' => $from_flight_purchase_id
    );
    $q->execute($arr);

    $booking_id = $db->lastInsertId();

    return $booking_id;
}

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

        while ($row = $result->fetch()) {
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

    $query = $db->prepare('SELECT fp.customer_id, fp.flight_offer_id, fp.deduct_from_balance, fb.flight_purchase_id, fb.from_flight_purchase_id, fb.duration, fo.duration AS offer_minutes
      FROM flight_bookings fb
      INNER JOIN flight_purchases fp ON fb.flight_purchase_id = fp.id
      INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
      WHERE fb.id = :bookingId AND fp.deduct_from_balance > 0');
    $query->execute(array(
        ':bookingId' => $booking_id
    ));
    $row = $query->fetch();
    if (!$row) {
        return;
    }

    $customer_id             = $row['customer_id'];
    $from_flight_purchase_id = $row['from_flight_purchase_id'];
    $balance                 = $row['duration'];

    if ($row['deduct_from_balance'] == 1) {
        updateCustomerFlightBalance($customer_id, $from_flight_purchase_id, $balance, true);

    } else if ($row['deduct_from_balance'] == 2) {
        updateCustomerCredits($customer_id, $balance, true);

    }
}

/**
 * @param $customer_id
 * @param $credits
 * @param bool $add
 */
function updateCustomerCredits($customer_id, $credits, $add = false) {
    global $db;

    $operator = ($add) ? '+' : '-';
    $sql      = sprintf("UPDATE customer SET credit_time = credit_time %s :credits WHERE customer_id = :customerId", $operator);

    $query = $db->prepare($sql);
    $query->execute(array(
        ':customerId' => $customer_id,
        ':credits'    => $credits
    ));
}

/**
 * @param $from_customer
 * @param $to_customer
 * @param $balance
 * @param $offer_id
 * @param $from_flight_purchase_id
 */
function transferBalanceFromCustomerAtoB($from_customer, $to_customer, $balance, $offer_id, $from_flight_purchase_id) {
    global $db;

    // check if to_customer already has the offer for which he is receiving balance
    $query = $db->prepare('SELECT id FROM flight_purchases
      WHERE flight_offer_id= :offerId AND customer_id= :customerId
      ORDER BY created DESC
      LIMIT 1');

    $query->execute(array(
        ':offerId'    => $offer_id,
        ':customerId' => $to_customer
    ));

    $row = $query->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $flight_purchase_id = $row['id'];
    } else {
        $invoice            = 'RS-' . rand(pow(10, 8 - 1), pow(10, 8) - 1);
        $flight_purchase_id = insertFlightPurchase($invoice, $offer_id, $to_customer, 1, 1);
    }
    updateCustomerFlightBalance($to_customer, $flight_purchase_id, $balance, true);

    // deduct balance from from_customer
    updateCustomerFlightBalance($from_customer, $from_flight_purchase_id, $balance);
}

/**
 * If user buys a flight order, but deletes flight time and wants to schedule flight time in future, we need to
 * save his flight balance for future use.
 *
 * @param $invoice_id
 */
function adjustBalanceForDeletedFlightBookings($invoice_id) {
    global $db;

    // get those purchases which don't have bookings
    $query = $db->prepare("SELECT fp.id, fp.customer_id, fo.duration
      FROM flight_purchases fp
      INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
      LEFT JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
      WHERE fp.invoice_id = :invoiceId AND fb.flight_purchase_id IS NULL");

    $query->execute(array(
        ':invoiceId' => $invoice_id
    ));

    while ($row = $query->fetch()) {
        updateCustomerFlightBalance($row['customer_id'], $row['id'], $row['duration'], true, true);
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