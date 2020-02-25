<?php

/**
 * Two types of things being sold
 */
define('TYPE_MERCHANDISE', 'Merchandise');
define('TYPE_SERVICE', 'Service');

define('FLAT_DISCOUNT', 0);    // percent
define('FLIGHT_PACKAGE_TYPE_INTERNAL', 1);

define('GIVEAWAY_APPROVAL_PENDING', 0);
define('GIVEAWAY_APPROVAL_APPROVED', 1);
define('GIVEAWAY_APPROVAL_DISAPPROVED', 2);
define('GIVEAWAY_APPROVAL_USED', 3);

define('ROLE_OPERATOR', 'Operator');
define('ROLE_CASHIER', 'cashier');
define('ROLE_ACCOUNT', 'account');
define('ROLE_MANAGEMENT', 'Management');
define('ROLE_PROCUREMENT', 'Procurement');

define('SESS_MOCK_ROLE', 'sess_mock_role');

define('PRESIDENTIAL_GUARD', 'Presidential Guard');
define('NAVY_SEAL', 'Navy Seal');
define('MILITARY_INDIVIDUALS', 'Military Individuals');

$_ROLE_ALLOWED_PAGES = [
    ROLE_MANAGEMENT => ['index', 'Businessplan', 'supplier', 'partners'],
    ROLE_CASHIER => ['index', 'sales', 'products', 'customer', 'partners', 'salesreport', 'flight_packages', 'flight_picker'],
    ROLE_ACCOUNT => ['index', 'salesreport', 'collection_other', 'revenue_liability', 'accountreceivables', 'select_customer', 'products', 'customer', 'supplier', 'partners', 'purchaseslist'],
];

$offer_to_groupon_map = [
    134 => 46,
    135 => 47,
    136 => 48
];

$_FTF_DISCOUNTS = ['Alpha', 'Discovery Way', 'Arooha', 'Desert Gate', 'JustDo', 'Highway', 'Groupon', 'Cobone', 'Emirates Airline'];

function getDiscountsOf($rnl_parent = null, $with_rnl_parents = false)
{
    global $db;

    if ($with_rnl_parents) {
        $query = $db->prepare('SELECT DISTINCT(rnl_parent) AS discount_name FROM discounts WHERE rnl_parent != "" 
          UNION
          SELECT DISTINCT(category) AS discount_name FROM discounts WHERE rnl_parent != "" ');
        $query->execute();
    } else {
        if ($rnl_parent == null) {
            $query = $db->prepare('SELECT DISTINCT(rnl_parent) AS discount_name FROM discounts WHERE rnl_parent != "" ');
            $query->execute();
        } else {
            $query = $db->prepare('SELECT DISTINCT(category) AS discount_name FROM discounts WHERE rnl_parent = ? ');
            $query->execute([$rnl_parent]);
        }
    }
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    return array_map(function ($item) {
        return $item['discount_name'];
    }, $rows);
}

function getDiscountsOfParent($parent = '')
{
    global $db;
    $query = $db->prepare('SELECT DISTINCT(category) AS discount_name FROM discounts WHERE parent = ? AND status = 1');
    $query->execute([$parent]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    return array_map(function ($item) {
        return $item['discount_name'];
    }, $rows);
}

/**
 * @param $customer_id
 * @param $flight_purchase_id
 * @param $minutes
 * @param bool $add_minutes
 * @param bool $reset_balance If true, don't add/subtract minutes, just reset it to provided $minutes
 */
function updateCustomerFlightBalance($customer_id, $flight_purchase_id, $minutes, $add_minutes = false, $reset_balance = false)
{
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
        $sql = "INSERT INTO flight_credits(customer_id, flight_purchase_id, minutes) 
          VALUES(:customer_id, :flight_purchase_id, :minutes)";
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
function deductFromBalance($customer_id, $flight_offer_id, $balance)
{
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
 * @param $invoice_id
 * @param $flight_offer_id
 * @param $customer_id
 * @param int $use_balance
 * @param int $status
 * @param int $class_people
 * @return string
 */
function insertFlightPurchase($invoice_id, $flight_offer_id, $customer_id, $use_balance = 0, $status = 0, $class_people = 0)
{
    global $db;

    $vat_code_id = getVatCodeId(TYPE_SERVICE);
    $flight_offer = getFlightOffer($flight_offer_id);

    $columns = "invoice_id, flight_offer_id, customer_id, deduct_from_balance, status, class_people, discount, price";
    $values = ":invoice_id, :flight_offer_id, :customer_id, :use_balance, :status, :class_people, :discount, :price";

    $arr = array(
        ':invoice_id'      => $invoice_id,
        ':flight_offer_id' => $flight_offer_id,
        ':customer_id'     => $customer_id,
        ':use_balance'     => $use_balance,
        ':status'          => $status,
        ':class_people'    => $class_people,
        ':discount'        => FLAT_DISCOUNT,
        ':price'           => $flight_offer['price']
    );

    if ($use_balance != 1) {
        $columns .= ",vat_code_id";
        $values .= ",:vatCodeId";
        $arr[':vatCodeId'] = $vat_code_id;
    }

    $sql = "INSERT INTO flight_purchases({$columns})
        VALUES ({$values})";

    $q   = $db->prepare($sql);
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
function insertFlightBooking($flight_purchase_id, $flight_time, $duration, $from_flight_purchase_id = 0)
{
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
function deleteFlightPurchase($flight_purchase_id)
{
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
function deleteFlightBooking($booking_id, $flight_purchase_id = null)
{
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
function addBalance($booking_id)
{
    global $db;

    $query = $db->prepare('SELECT fp.customer_id, fp.flight_offer_id, fp.deduct_from_balance, fb.flight_purchase_id, fb.from_flight_purchase_id, fb.duration, fo.duration AS offer_minutes
      FROM flight_bookings fb
      INNER JOIN flight_purchases fp ON fb.flight_purchase_id = fp.id
      INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
      WHERE fb.id = :bookingId');
    $query->execute(array(
        ':bookingId' => $booking_id
    ));
    $row = $query->fetch();
    if (!$row) {
        return;
    }

    $customer_id             = $row['customer_id'];
    $flight_purchase_id      = $row['flight_purchase_id'];
    $from_flight_purchase_id = $row['from_flight_purchase_id'];
    $balance                 = $row['duration'];

    if ($row['deduct_from_balance'] == 0) {
        updateCustomerFlightBalance($customer_id, $flight_purchase_id, $balance, true);
    } else if ($row['deduct_from_balance'] == 1) {
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
function updateCustomerCredits($customer_id, $credits, $add = false)
{
    global $db;

    $per_minute_cost = getPerMinuteCostForCustomer($customer_id);

    $operator = ($add) ? '+' : '-';
    $sql      = sprintf("UPDATE customer 
      SET 
          credit_time = credit_time %s :credits,
          credit_cash = credit_cash %s :perMinuteCost
      WHERE customer_id = :customerId", $operator, $operator);

    $query = $db->prepare($sql);
    $query->execute(array(
        ':customerId' => $customer_id,
        ':credits'    => $credits,
        ':perMinuteCost' => $per_minute_cost * $credits
    ));
}

/**
 * @param $from_customer
 * @param $to_customer
 * @param $balance
 * @param $offer_id
 * @param $from_flight_purchase_id
 */
function transferBalanceFromCustomerAtoB($from_customer, $to_customer, $balance, $offer_id, $from_flight_purchase_id)
{
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
        $invoice            = 'RS-' . createRandomPassword();
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
function adjustBalanceForDeletedFlightBookings($invoice_id)
{
    global $db;

    // get those purchases which don't have bookings
    $query = $db->prepare("SELECT fp.id, fp.customer_id, fp.deduct_from_balance, fb.duration
      FROM flight_purchases fp
      INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
      LEFT JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
      WHERE fp.invoice_id = :invoiceId AND fb.flight_purchase_id IS NULL");

    $query->execute(array(
        ':invoiceId' => $invoice_id
    ));

    while ($row = $query->fetch()) {
        if ($row['deduct_from_balance'] == 1) {
            updateCustomerFlightBalance($row['customer_id'], $row['id'], $row['duration'], true, true);
        } else if ($row['deduct_from_balance'] == 2) {
            updateCustomerCredits($row['customer_id'], $row['duration'], true);
        }
    }
}

/**
 * @param string $prefix
 * @param int $length
 * @param bool $lowercase
 * @return string
 */
function createRandomPassword($prefix = 'RS-', $length = 7, $lowercase = false)
{
    if ($lowercase) {
        $chars = "abc1def2ghi3jkl4mno5pqr6stu7vwx8y9z0";
    } else {
        $chars = "ABC1DEF2GHI3JKL4MNO5PQR6STU7VWX8Y9Z0";
    }
    srand((float) microtime() * 1000000);
    $i    = 0;
    $pass = '';
    while ($i <= $length) {
        $num = rand() % strlen($chars);
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    if (!$lowercase) {
        if (checkInvoiceNum($prefix . $pass)) {
            return createRandomPassword();
        }
    }

    return $pass;
}

/**
 * @param $invoice_no
 * @return bool
 */
function checkInvoiceNum($invoice_no)
{
    global $db;

    $query = $db->prepare("SELECT transaction_id FROM sales WHERE invoice_number = :invoiceNo");
    $query->execute(array(
        ':invoiceNo' => $invoice_no
    ));
    $exists_in_sales = ($query->rowCount() > 0);

    $query = $db->prepare("SELECT transaction_id FROM sales_order WHERE invoice = :invoiceNo");
    $query->execute(array(
        ':invoiceNo' => $invoice_no
    ));
    $exists_in_sales_order = ($query->rowCount() > 0);

    $query = $db->prepare("SELECT id FROM flight_purchases WHERE invoice_id = :invoiceNo");
    $query->execute(array(
        ':invoiceNo' => $invoice_no
    ));
    $exists_in_flight_purchases = ($query->rowCount() > 0);

    return ($exists_in_sales || $exists_in_sales_order || $exists_in_flight_purchases);
}

/**
 * Rearrays multiple file upload
 *
 * @param $file
 * @return array
 */
function reArrayFiles($file)
{
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

/**
 * @param $type
 * @return mixed
 */
function getVatCodeId($type)
{
    global $db;
    $query = $db->prepare('SELECT id FROM vat_codes WHERE type=? AND active=1 ORDER BY modified ASC LIMIT 1');
    $query->execute(array('Merchandise'));
    $row = $query->fetch();
    $vat_code_id = $row['id'];
    return $vat_code_id;
}

/**
 * @param $flight_offer_id
 * @return bool|mixed
 */
function getFlightOffer($flight_offer_id)
{
    global $db;
    $query = $db->prepare('SELECT * FROM flight_offers WHERE id=? LIMIT 1');
    $query->execute([$flight_offer_id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);
    return $row;
}

/**
 * @param $flight_purchase_id
 * @return bool|mixed
 */
function getFlightPurchase($flight_purchase_id)
{
    global $db;
    $query = $db->prepare('SELECT * FROM flight_purchases WHERE id=? LIMIT 1');
    $query->execute([$flight_purchase_id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);
    return $row;
}

/**
 * @param $discounted_amount
 * @param $invoice_no
 * @param $saving_flight
 * @return array
 */
function getVatDetailsForDiscountedAmountAndInvoice($discounted_amount, $invoice_no, $saving_flight)
{
    global $db;

    if ($saving_flight) {
        $query = $db->prepare('SELECT DISTINCT vc.id, vc.percent FROM vat_codes vc
                                          INNER JOIN flight_purchases fp ON vc.id = fp.vat_code_id
                                          WHERE fp.invoice_id = ?');
    } else {
        $query = $db->prepare('SELECT DISTINCT vc.id, vc.percent FROM vat_codes vc
                                          INNER JOIN sales_order so ON vc.id = so.vat_code_id
                                          WHERE so.invoice = ?');
    }
    $query->execute(array($invoice_no));
    $total_vat = 0;
    $arr_vat_percents = [];
    while ($row = $query->fetch()) {
        $arr_vat_percents[] = $row['percent'];
        $total_vat += round($discounted_amount * $row['percent'] / 100, 2);
    }

    return array($total_vat, $arr_vat_percents);
}

/**
 * @param $flight_purchase_id
 * @return mixed
 */
function getRemainingMinutesOfFlightPurchase($flight_purchase_id)
{
    global $db;

    $query = $db->prepare('SELECT minutes FROM flight_credits WHERE flight_purchase_id = ?');
    $query->execute(array($flight_purchase_id));
    $row = $query->fetch();
    return $row['minutes'];
}

/**
 * @param $flight_purchase_id
 * @return mixed
 */
function getPerMinuteCostOfPurchasedPackage($flight_purchase_id)
{
    global $db;

    $query = $db->prepare('SELECT
          fo.duration, fp.discount, fp.price
          FROM flight_purchases fp
          INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
          WHERE fp.id = ?');
    $query->execute([$flight_purchase_id]);
    $row = $query->fetch();

    if ($row['duration'] > 0) {
        $per_minute_cost = $row['price'] / $row['duration'];
        $discounted_per_minute_cost = $per_minute_cost - ($row['discount'] * $per_minute_cost / 100);
        return round($discounted_per_minute_cost, 2);
    }
    return 0;
}

/**
 * Per minute cost of customer's remaining credit minutes which was entered manually by operators
 *
 * @param $customer_id
 * @return mixed
 */
function getPerMinuteCostForCustomer($customer_id, $start_date = false, $end_date = false)
{
    global $db;

    if (!$start_date) {
        $query = $db->prepare('SELECT per_minute_cost FROM customer WHERE customer_id = ?');
        $query->execute([$customer_id]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['per_minute_cost'];
    } else {
        $row = getCustomerYearlyPurchase($customer_id, $start_date, $end_date);
        return $row['per_minute_cost'];
    }
}

/**
 * Navy Seal buys in bulk for one year. Each month there are certain minutes they can fly on a certain per-minute-rate
 *
 * @param $customer_id
 * @param $start_date
 * @param $end_date
 * @return bool|mixed
 */
function getCustomerYearlyPurchase($customer_id, $start_date, $end_date)
{
    global $db;

    $sql = 'SELECT * FROM customer_yearly_purchases WHERE customer_id = ? AND start_date <= ?';
    $params = [$customer_id, $start_date];
    $query = $db->prepare($sql);

    if ($end_date != null) {
        $sql .= 'AND end_date <= ? ORDER BY start_date DESC';
        $param[] = $end_date;
    }

    $query->execute($params);
    $row = $query->fetch(PDO::FETCH_ASSOC);
    return $row;
}

/**
 * @param $invoice_id
 * @return bool
 */
function getPurchaseType($invoice_id)
{
    global $db;

    $query = $db->prepare('SELECT LOWER(package_name) AS package_name, type FROM flight_packages fpkg
      INNER JOIN flight_offers fo ON fo.package_id = fpkg.id
      INNER JOIN flight_purchases fp ON fp.flight_offer_id = fo.id
      WHERE fp.invoice_id = ? LIMIT 1');
    $query->execute([$invoice_id]);
    $row = $query->fetch();
    return $row;
}

/**
 * @param $email
 * @param $subject
 * @param $body
 * @param bool $include_info_address
 * @return mixed
 */
function sendEmail($email, $subject, $body, $include_info_address = false, $from = 'info@inflightdubai.com')
{

    $mailin = new Mailin('https://api.sendinblue.com/v2.0', MAILIN_API_KEY);

    $arr_bcc = array(
        "hrehman200@gmail.com" => "bcc whom!",
    );
    if ($include_info_address) {
        $arr_bcc['info@inflightdubai.com'] = "Info";
    }

    $data = array(
        "to" => array($email => "to whom!"),
        "bcc" => $arr_bcc,
        "from" => array($from),
        "subject" => $subject,
        "html" => $body,
        "headers" => array("Content-Type" => "text/html; charset=iso-8859-1")
    );

    $response = $mailin->send_email($data);
    return $response;
}

/**
 * @param $entity
 * @return int
 */
function getParentEntityId($entity)
{
    global $db;

    $query = $db->prepare('SELECT id FROM business_plan_entities WHERE name = ?');
    $query->execute([$entity]);
    if ($query->rowCount() > 0) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['id'];
    } else {
        $query = $db->prepare("INSERT INTO business_plan_entities(parent_id, name) VALUES(?, ?)");
        $query->execute([0, $entity]);
        return $db->lastInsertId();
    }
}

/**
 * @param $entity
 * @param $parent_entity_id
 * @param $gl_code
 * @return int
 */
function getBusinessEntityId($entity, $parent_entity_id, $gl_code)
{
    global $db;

    $query = $db->prepare('SELECT id FROM business_plan_entities WHERE name = ?');
    $query->execute([$entity]);
    if ($query->rowCount() > 0) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['id'];
    } else {
        $query = $db->prepare("INSERT INTO business_plan_entities(parent_id, name, gl_code) VALUES(?, ?, ?)");
        $query->execute([$parent_entity_id, $entity, $gl_code]);
        return $db->lastInsertId();
    }
}

/**
 * @param $entity_id
 * @param $year
 * @param $month
 * @return float|bool
 */
function getBusinessEntityActualValue($entity_id, $year, $month)
{
    global $db;
    $query = $db->prepare('SELECT actual FROM business_plan_yearly WHERE business_plan_entity_id = ? AND month = ? AND year = ?');
    $query->execute([$entity_id, $month, $year]);
    $start_end_dates = getStartEndDateFromMonthYear($year, $month);
    // if row exists and its a row of THE PAST (i.e. we don't need to update it)
    $end_timestamp = strtotime($start_end_dates['end']);
    $now_timestamp = time();
    if ($query->rowCount() > 0 && $end_timestamp < $now_timestamp) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row['actual'];
    }
    return false;
}

/**
 * @param $entity_id
 * @param $year
 * @param $month
 * @param $estimated_value float The value that is injected when we parse the yearly csv
 * @param $actual_value float The value that is calculated and injected when we open business plan report
 */
function updateBusinessEntityValue($entity_id, $year, $month, $estimated_value, $actual_value = 0.0)
{
    global $db;

    $query = $db->prepare('SELECT id FROM business_plan_yearly WHERE business_plan_entity_id = ? AND month = ? AND year = ?');
    $query->execute([$entity_id, $month, $year]);
    if ($query->rowCount() > 0) {

        $arr = [$entity_id, $month, $year];
        $set_string = '';
        if ($estimated_value != null) {
            $set_string = 'value = ?';
            array_unshift($arr, $estimated_value);
        } else if ($actual_value != 0) {
            $set_string .= 'actual = ?';
            array_unshift($arr, $actual_value);
        }

        if ($set_string == '') {
            return;
        }

        $query = $db->prepare('UPDATE business_plan_yearly SET ' . $set_string . ' 
           WHERE business_plan_entity_id = ? AND month = ? AND year = ?');
        $query->execute($arr);
    } else {
        $query = $db->prepare("INSERT INTO business_plan_yearly(business_plan_entity_id, month, year, value, actual) 
          VALUES(?, ?, ?, ?, ?)");
        $query->execute([$entity_id, $month, $year, $estimated_value, $actual_value]);
    }
}

/**
 * @param $arr
 * @return mixed
 */
function sumTwoRows($arr)
{
    if (count($arr) > 1) {
        for ($i = 1; $i < count($arr); $i++) {
            $arr[0]['paid'] += $arr[$i]['paid'];
            $arr[0]['minutes_used'] += $arr[$i]['minutes_used'];
            $arr[0]['total_minutes'] += $arr[$i]['total_minutes'];
        }

        array_splice($arr, 1);
    }
    return $arr;
}

/**
 * @param array $arr
 * @param callable $key_selector
 * @return array
 */
function array_group_by(array $arr, callable $key_selector)
{
    $result = array();
    foreach ($arr as $i) {
        $key = call_user_func($key_selector, $i);
        $result[$key][] = $i;
    }
    return $result;
}

/**
 * If search date is 15 May, purchase date is 15 May but credit-flight was taken on 16 May then
 * credit-used should not be shown on 15 May
 *
 * @param $flight_time
 * @param $date1
 * @param $date2
 * @return bool
 */
function isTimeInsideSearchedDate($flight_time, $date1, $date2)
{
    try {
        $date = (new DateTime($flight_time))->format('Y-m-d');
        return (strtotime($date) >= strtotime($date1) && strtotime($date) <= strtotime($date2));
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Performing sales.date is inside time-range OR booking.flight_time inside time range search at once hang up
 * the system, thats why two separate queries are needed.
 *
 * @param $package_name
 * @param bool $sale_date_check
 * @return string
 */
function getQuery($package_name, $sale_date_check = true)
{

    if ($package_name == 'Skydivers' || $package_name == 'FTF' || $package_name == 'RF - Repeat Flights' || $package_name == 'UP-Sale') {
        $join_with_discount = 'LEFT JOIN discounts d ON fp1.discount_id = d.id OR fp1.discount_id = 0';
    } else {
        $join_with_discount = 'INNER JOIN discounts d ON fp1.discount_id = d.id';
    }

    // if FTF OR one of the discounts of FTF
    $package_check = '';
    if ($package_name == 'FTF' /*|| in_array($package_name, $_FTF_DISCOUNTS)*/) {
        $package_check = " fpkg.package_name LIKE 'FTF%'";
    } else if ($package_name == 'RF - Repeat Flights') {
        $package_check = " fpkg.package_name LIKE 'RF - Repeat Flights%'";
    } else if ($package_name == 'UP-Sale') {
        $package_check = " fp1.flight_offer_id IN (84, 97, 98, 99, 100, 101, 102, 103, 104, 105, 116)";
    } else if (!in_array($package_name, getDiscountsOf(null, true))) {
        $package_check = " (fpkg.id IN (6, 8)";
        if (strpos($package_name, 'Military') === 0) { // we need to check whether for RF, military discount is given, in which case RF will come in Military
            $package_check .= " OR fpkg.package_name LIKE 'RF - Repeat Flights%'
                OR fpkg.package_name LIKE 'FTF%' ";
        }
        $package_check .= ')';
    }

    if ($package_check != '') {
        $package_check .= ' AND ';
    }

    if ($sale_date_check) {
        $date_check = 's1.date >= :startDate AND s1.date <= :endDate';
    } else {
        $date_check = 'DATE(fb1.flight_time) >= :startDate AND DATE(fb1.flight_time) <= :endDate';
    }

    $sql = sprintf("SELECT
                            fp1.id AS flight_purchase_id,
                            fp1.deduct_from_balance,
                            fb1.id,
                            fb1.from_flight_purchase_id,
                            fb1.flight_time,
                            IFNULL(fb1.flight_time, NOW()+10) <= NOW() AS flight_taken,
                            s1.invoice_number,
                            s1.customer_id,
                            s1.date,
                            d.category,
                            CASE WHEN(
                                (s1.mode_of_payment = 'credit_time' OR s1.mode_of_payment_1 = 'credit_time') AND fp1.deduct_from_balance = 2
                            ) THEN (fb1.duration * c.per_minute_cost) ELSE s1.amount
                            END AS paid,
                            CASE WHEN(
                                s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time' AND fp1.deduct_from_balance = 0
                            ) THEN fb1.duration ELSE 0
                            END AS minutes_used,
                            CASE WHEN(
                                s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time' AND fp1.deduct_from_balance = 0 
                            ) THEN fo1.duration ELSE 0
                            END AS total_minutes,
                            CASE WHEN(
                                s1.mode_of_payment = 'credit_time' OR s1.mode_of_payment_1 = 'credit_time' OR fp1.deduct_from_balance > 0
                            ) THEN fb1.duration ELSE 0
                            END AS credit_used,
                            s1.mode_of_payment
                        FROM
                            sales s1
                        INNER JOIN flight_purchases fp1 ON
                            s1.invoice_number = fp1.invoice_id
                        INNER JOIN flight_offers fo1 ON
                            fp1.flight_offer_id = fo1.id
                        INNER JOIN flight_packages fpkg ON
                            fo1.package_id = fpkg.id
                        LEFT JOIN flight_bookings fb1 ON
                            fb1.flight_purchase_id = fp1.id
                        INNER JOIN customer c ON
                            fp1.customer_id = c.customer_id
                        %s
                        WHERE
                            %s 
                            (
                                s1.mode_of_payment IN(
                                    'Cash',
                                    'Card',
                                    'Online',
                                    'Account',
                                    'credit_time',
                                    'credit_cash'
                                ) OR s1.mode_of_payment_1 IN(
                                    'Cash',
                                    'Card',
                                    'Online',
                                    'Account',
                                    'credit_time',
                                    'credit_cash'
                                )
                            ) AND(
                                %s
                            ) AND(
                                (customer_name != 'FDR' AND customer_name != 'MAINTENANCE' AND customer_name != 'inflight staff flying' AND customer_name != 'Training Inflight') OR customer_name IS NULL
                            ) ", $join_with_discount, $package_check, $date_check);

    if ($package_name == 'Skydivers' || $package_name == 'FTF' || $package_name == 'RF - Repeat Flights' || $package_name == 'UP-Sale') {

        $ftf_discount_check = '';
        foreach (getDiscountsOf(null, true) as $ftf_discount) {
            $ftf_discount_check .= "AND d.category NOT LIKE '" . $ftf_discount . "%'";
        }

        $military_disounts = getDiscountsOfParent('Military');
        $military_discount_check = sprintf("AND d.category NOT IN ('%s')", implode("','", $military_disounts));

        $sql .= $military_discount_check .
            "AND d.category NOT LIKE 'Navy Seal%'
                 AND d.category NOT LIKE 'Groupon%'
                 AND d.category NOT LIKE 'Military%'
                 " . $ftf_discount_check;
    } else if (in_array($package_name, getDiscountsOf(null, true))) {
        $sql .= "AND (d.category LIKE '{$package_name}%' OR d.rnl_parent LIKE '{$package_name}%')";
    } else if ($package_name == NAVY_SEAL) {
        $sql .= "AND d.category LIKE 'Navy Seal%'";
    } else if ($package_name == 'Military') { // so that military discounts given to RF can be included in Military
        $sql .= "AND (
                    (
                        (fpkg.package_name LIKE 'RF - Repeat Flights%' AND d.category IN ('Presidential Guard', 'Military', 'Sky god%'))
                        OR 
                        (fpkg.package_name NOT LIKE 'RF - Repeat Flights%' AND d.category IN ('Military'))
                    ) OR (
                        (fpkg.package_name LIKE 'FTF%' AND d.category IN ('Presidential Guard', 'Military', 'Sky god%'))
                        OR 
                        (fpkg.package_name NOT LIKE 'FTF%' AND d.category IN ('Military'))
                    )
                )";
    } else {
        $sql .= "AND d.category IN ('" . $package_name . "')";
    }

    /*if($package_name == 'FTF') {
        $sql .= " AND fpkg.package_name LIKE 'FTF%'";
    }*/

    $sql .= " GROUP BY fp1.id, fb1.id";
    return $sql;
}

/**
 * @param $package_name
 * @param $start_date
 * @param $end_date
 * @return array
 */
function getDataAndAggregate($package_name, $start_date, $end_date)
{
    global $db;

    $sql_w_sale_date = getQuery($package_name);
    $result = $db->prepare($sql_w_sale_date);
    $result->execute(array(
        ':startDate' => $start_date,
        ':endDate'   => $end_date
    ));
    $arr2 = $result->fetchAll(PDO::FETCH_ASSOC);

    $sql_w_flight_date = getQuery($package_name, false);
    $result = $db->prepare($sql_w_flight_date);
    $result->execute(array(
        ':startDate' => $start_date,
        ':endDate'   => $end_date
    ));
    $arr_flight = $result->fetchAll(PDO::FETCH_ASSOC);

    $arr_diff = array_map(
        'unserialize',
        array_diff(array_map('serialize', $arr_flight), array_map('serialize', $arr2))
    );

    $arr2 = array_merge($arr2, $arr_diff);

    $arr_flight_purchase_ids = array_map(function ($v) {
        return $v['flight_purchase_id'];
    }, $arr2);
    $arr_flight_purchase_ids = array_unique($arr_flight_purchase_ids);

    // since navy seal new package started from May
    if ($package_name == NAVY_SEAL && strtotime($start_date) >= strtotime('2018-05-01')) {
        // TODO: so far there is only one Navy Seal customer and no chance of increase
        if (count($arr2) > 0) {
            $customer_yearly_purchase = getCustomerYearlyPurchase($arr2[0]['customer_id'], $start_date, $end_date);
            $total_minutes = $customer_yearly_purchase['per_month_minutes'];
            $paid = $customer_yearly_purchase['per_minute_cost'] * $total_minutes;
        }
    } else {
        $arr_paid = array_group_by($arr2, function ($v) {
            return $v['invoice_number'];
        });
        $paid = array_reduce($arr_paid, function ($carry, $item) use ($start_date, $end_date, $package_name) {
            if (isTimeInsideSearchedDate($item[0]['date'], $start_date, $end_date) && !is_null($item[0]['credit_used']) && $item[0]['credit_used'] == 0) {
                $carry += $item[0]['paid'];
            }
            return $carry;
        });

        $arr_total_minutes = array_group_by($arr2, function ($v) {
            return $v['flight_purchase_id'];
        });
        $total_minutes = array_reduce($arr_total_minutes, function ($carry, $item) use ($start_date, $end_date) {
            if (isTimeInsideSearchedDate($item[0]['date'], $start_date, $end_date)) {
                $carry += $item[0]['total_minutes'];
            }
            return $carry;
        });
    }

    $arr_minutes_used = array_group_by($arr2, function ($v) {
        return $v['id'];
    });
    $purchased_minutes_used = 0;
    $total_credit_cost = 0;
    $total_purchased_cost = 0;
    // cost of minutes used from purchased minutes (that are already in search)
    $total_credit_from_purchased_cost = 0;

    $minutes_used = array_reduce($arr_minutes_used, function ($carry, $item)
    use (&$purchased_minutes_used, &$total_credit_cost, &$total_purchased_cost, &$total_credit_from_purchased_cost, $arr_flight_purchase_ids, $package_name, $start_date, $end_date) {
        if ($item[0]['flight_taken'] == 1) {
            if ($item[0]['from_flight_purchase_id'] > 0 || $item[0]['deduct_from_balance'] == 2) {
                if (isTimeInsideSearchedDate($item[0]['flight_time'], $start_date, $end_date)) {
                    $carry += $item[0]['credit_used'];

                    if ($item[0]['deduct_from_balance'] == 2) {
                        if ($package_name == NAVY_SEAL) {
                            $credit_cost_per_minute = getPerMinuteCostForCustomer($item[0]['customer_id'], $start_date, $end_date);
                        } else {
                            $credit_cost_per_minute = getPerMinuteCostForCustomer($item[0]['customer_id']);
                        }
                    } else {
                        $credit_cost_per_minute = getPerMinuteCostOfPurchasedPackage($item[0]['from_flight_purchase_id']);
                    }

                    // if credit used is from the flight-purchase that is included in selected time range
                    if (in_array($item[0]['from_flight_purchase_id'], $arr_flight_purchase_ids)) {
                        $purchased_minutes_used += $item[0]['credit_used'];
                        $total_credit_from_purchased_cost += $credit_cost_per_minute * $item[0]['credit_used'];
                    } else {
                        $total_credit_cost += $credit_cost_per_minute * $item[0]['credit_used'];
                    }
                }
            } else if (isTimeInsideSearchedDate($item[0]['flight_time'], $start_date, $end_date)) {
                $carry += $item[0]['minutes_used'];
                $purchased_minutes_used += $item[0]['minutes_used'];

                // special case, customer booked via online on 31st May but came to fly on 1st Jun
                // this section is problematic
                $date_purchased = $item[0]['date'];
                $date_flown = substr($item[0]['flight_time'], 0, strpos($item[0]['flight_time'], ' '));
                if ($item[0]['mode_of_payment'] == 'Online' && $date_purchased != $date_flown) {
                    $credit_cost_per_minute = getPerMinuteCostOfPurchasedPackage($item[0]['flight_purchase_id']);
                    $total_credit_cost += $credit_cost_per_minute * $item[0]['minutes_used'];
                } else {
                    $per_minute_cost = getPerMinuteCostOfPurchasedPackage($item[0]['flight_purchase_id']);
                    $total_purchased_cost += $per_minute_cost * $item[0]['minutes_used'];
                }
            }
        }
        return $carry;
    });

    $credit_used = array_reduce($arr_minutes_used, function ($carry, $item) use ($start_date, $end_date) {
        if (isTimeInsideSearchedDate($item[0]['flight_time'], $start_date, $end_date)) {
            $carry += $item[0]['credit_used'];
        }
        return $carry;
    });

    // renaming for display
    if ($package_name ==  'RF - Repeat Flights') {
        $package_name = 'Repeat Flight';
    }

    if ($package_name == 'Sky god%') {
        $package_name = 'US Navy';
    }

    $aed_value = $total_purchased_cost + $total_credit_cost + $total_credit_from_purchased_cost;

    $arr2 = [[
        'package_name' => $package_name,
        'paid' => $paid,
        'total_minutes' => $total_minutes,
        'minutes_used' => $minutes_used,
        'credit_used' => $credit_used,
        'purchased_minutes_used' => $purchased_minutes_used,
        'aed_value' => $aed_value,
        'avg_per_min' => ($minutes_used > 0) ? ($aed_value) / $minutes_used : 0
    ]];

    return $arr2;
}

/**
 * @param $product_name
 * @param $date1
 * @param $date2
 * @return mixed
 */
function getMerchandiseRevenue($product_name, $date1, $date2)
{
    global $db;

    if ($product_name == TYPE_MERCHANDISE) {
        $query = $db->prepare('SELECT SUM(so.amount - (so.discount * so.amount / 100)) AS paid
                            FROM sales s
                            INNER JOIN sales_order so ON s.invoice_number = so.invoice
                            INNER JOIN products p ON so.product = p.product_id 
                            WHERE 
                            (p.product_name NOT LIKE "%Video%" AND p.product_name NOT LIKE "%Helmet Rent%" AND p.product_name NOT LIKE "Rental Helmet Full Day")
                            AND p.gen_name = ?
                            AND (s.date >= ? AND s.date <= ?)');
        $query->execute([TYPE_MERCHANDISE, $date1, $date2]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
    } else if ($product_name == 'Helmet Rent') {
        $query = $db->prepare('SELECT SUM(so.amount - (so.discount * so.amount / 100)) AS paid
                            FROM sales s
                            INNER JOIN sales_order so ON s.invoice_number = so.invoice
                            INNER JOIN products p ON so.product = p.product_id 
                            WHERE 
                            (p.product_name LIKE "%Helmet Rent%" OR p.product_name LIKE "Rental Helmet Full Day")
                            AND (s.date >= ? AND s.date <= ?)');
        $query->execute([$date1, $date2]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
    } else {
        // since video phots are being displayed collectively
        $query = $db->prepare(sprintf('SELECT SUM(so.amount - (so.discount * so.amount / 100))  AS paid
                            FROM sales s
                            INNER JOIN sales_order so ON s.invoice_number = so.invoice
                            INNER JOIN products p ON so.product = p.product_id 
                            WHERE (p.product_name LIKE ? %s)
                            AND (s.date >= ? AND s.date <= ?)', ($product_name == 'Video') ? 'OR p.product_name LIKE "%photo%"' : ''));
        $query->execute(["%" . $product_name . "%", $date1, $date2]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
    }

    $arr2 = [[
        'package_name' => $product_name == 'Video' ? 'Videos/Photos' : $product_name,
        'paid' => $row['paid'],
        'aed_value' => $row['paid']
    ]];

    return $arr2;
}

/**
 * @param $product_name
 * @param $date1
 * @param $date2
 * @return array
 */
function getOtherRevenue($product_name, $date1, $date2)
{
    global $db;

    $others = ['"Facility Rental"', '"Sandstorm Registration Fee"', '"Sandstorm Registration Fee 2019"'];
    $sql = sprintf('SELECT so.name, SUM(so.amount - (so.discount * so.amount / 100)) AS paid
                        FROM sales s
                        INNER JOIN sales_order so ON s.invoice_number = so.invoice
                        WHERE 
                        (so.name IN (%s))
                        AND (s.date >= ? AND s.date <= ?)
                        AND so.invoice != ""
                        GROUP BY so.name', implode(',', $others));
    $query = $db->prepare($sql);
    $query->execute([$date1, $date2]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $arr2[] = [
            'package_name' => $row['name'],
            'paid' => $row['paid'],
            'aed_value' => $row['paid']
        ];
    }

    return $arr2;
}

function getClassSessionRevenue($product_name, $date1, $date2)
{
    global $db;

    $sql = sprintf('SELECT s.amount FROM `flight_purchases` fp
        INNER JOIN sales s ON fp.invoice_id = s.invoice_number
        WHERE fp.flight_offer_id = 13
        AND s.date >= ? AND s.date <= ?
        GROUP BY s.invoice_number');
    $query = $db->prepare($sql);
    $query->execute([$date1, $date2]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    $paid = array_sum(array_map(function ($item) {
        return $item['amount'];
    }, $rows));

    $arr2 = [[
        'package_name' => $product_name,
        'paid' => $paid,
        'aed_value' => $paid
    ]];

    return $arr2;
}

/**
 * @param $packages
 * @param $from
 * @param $to
 * @param bool $search_days
 * @return array
 */
function getMinutesFlownInPackages($packages, $from, $to, $search_days = false)
{
    global $db;

    if (count($packages) > 1) {
        $package_name_check = 'AND (';
        for ($i = 0; $i < count($packages); $i++) {
            $package_name_check .= sprintf('fpkg.package_name LIKE "%%%s%%"', $packages[$i]);
            if ($i != count($packages) - 1) {
                $package_name_check .= ' OR ';
            }
        }
        $package_name_check .= ')';
    } else {
        if ($packages == ['Skydivers'] || $packages == ['FTF']) {
            $join_with_discount = 'LEFT JOIN discounts d ON fp.discount_id = d.id OR fp.discount_id = 0';
        } else {
            $join_with_discount = 'INNER JOIN discounts d ON fp.discount_id = d.id';
        }

        if ($packages == ['FTF']) {
            $package_name_check = "AND fpkg.package_name LIKE 'FTF%'";
        } else if ($packages == ['Skydivers']) {
            $package_name_check = "AND fpkg.id IN (6, 8)";
        }

        if ($packages == ['Skydivers'] || $packages == ['FTF']) {
            $discount_category_check = "AND (d.category NOT IN ('Presidential Guard', 'Navy Seal', 'Military') AND d.category NOT LIKE 'Military%')";
        } else {
            $discount_category_check = "AND (d.category IN ('Presidential Guard', 'Navy Seal', 'Military') OR d.category LIKE 'Military%')";
        }
    }

    $sql = sprintf('SELECT DISTINCT fb.id, fpkg.package_name, fb.duration AS duration 
                        FROM flight_bookings fb
                        INNER JOIN flight_purchases fp ON fb.flight_purchase_id = fp.id
                        INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                        INNER JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                        %s
                        WHERE
                          DATE(fb.flight_time) >= :from AND DATE(fb.flight_time) <= :to
                          %s
                          %s
                        ', $join_with_discount, $package_name_check, $discount_category_check);

    if ($search_days == 'weekends') {
        $sql .= ' AND (DAYNAME(fb.flight_time) = "Friday" OR DAYNAME(fb.flight_time) = "Saturday")';
    } else if ($search_days == 'weekdays') {
        $sql .= ' AND (DAYNAME(fb.flight_time) != "Friday" AND DAYNAME(fb.flight_time) != "Saturday")';
    }

    //$sql .= ' GROUP BY fpkg.package_name';

    $query = $db->prepare($sql);
    $query->execute([
        ':from' => $from,
        ':to' => $to
    ]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    $arr = [];
    foreach ($rows as $row) {
        if (isset($arr[$row['package_name']])) {
            $arr[$row['package_name']] += $row['duration'];
        } else {
            $arr[$row['package_name']] = $row['duration'];
        }
    }

    // we need to sum same packages e.g. FTF-Single, FTF-Multipe into FTF
    if ($search_days) {
        $arr2 = [];
        for ($i = 0; $i < count($packages); $i++) {
            foreach ($arr as $package_full_name => $minutes_used) {

                if ($packages[$i] == 'Skydivers' && strpos($package_full_name, 'Experienced') !== false) {
                    $arr2[$packages[$i]] += $minutes_used;
                } else if (strpos($packages[$i], 'Military') === 0 && strpos($package_full_name, 'Experienced') !== false) {
                    $arr2[$packages[$i]] += $minutes_used;
                } else if (strpos($package_full_name, $packages[$i]) !== false) {
                    $arr2[$packages[$i]] += $minutes_used;
                }
            }
        }
        return $arr2;
    }

    return $arr;
}

/**
 * @param $from
 * @param $to
 * @return array
 */
function getFlightDiscountsGiven($from, $to)
{
    global $db;

    $query = $db->prepare('SELECT d.category, d.percent, SUM((d.percent*fp.price/100)) AS discount_value
                        FROM `flight_purchases` fp
                        INNER JOIN discounts d ON fp.discount_id = d.id
                        WHERE DATE(fp.created) >= ? AND DATE(fp.created) <= ?
                        GROUP BY d.category');
    $query->execute([$from, $to]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
}

/**
 * @param $from
 * @param $to
 * @return array
 */
function getMerchandiseDiscountsGiven($from, $to)
{
    global $db;

    $query = $db->prepare('SELECT d.category, d.percent, SUM((d.percent*so.price/100)) AS discount_value 
                        FROM `sales_order` so
                        INNER JOIN discounts d ON so.discount_id = d.id
                        WHERE so.date >= ? AND so.date <= ? AND d.percent > 0
                        GROUP BY d.category');
    $query->execute([$from, $to]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
}

function getUnconsumedRevenueForYear($year)
{
    global $db;

    $query = $db->prepare('SELECT
            ur.*, DATE(ur.created) AS expired_date,
            fp.invoice_id, DATE(fp.created) AS purchase_date,
            fo.offer_name,
            fpkg.package_name,
            c.customer_name,
            d.category AS discount_name
        FROM
            unconsumed_revenue ur
        INNER JOIN flight_purchases fp ON
            ur.flight_purchase_id = fp.id
        INNER JOIN customer c ON
            fp.customer_id = c.customer_id
        INNER JOIN flight_offers fo ON 
            fp.flight_offer_id = fo.id
        INNER JOIN flight_packages fpkg ON 
            fo.package_id = fpkg.id
        LEFT JOIN discounts d ON 
            fp.discount_id = d.id
        WHERE
            ur.year = ?
            AND fpkg.package_name NOT IN("FDR", "Giveaways", "Staff Flying")
            AND c.customer_name NOT IN("FDR")');
    $query->execute([$year]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
}

/**
 * @param $year
 * @param null $month
 * @return array
 */
function getStartEndDateFromMonthYear($year, $month = null)
{

    if ($month == null) {
        $time = $year;
    } else {
        $time = "{$month}-{$year}";
    }

    $dt = DateTime::createFromFormat('M-Y', $time);
    $start_date = $dt->format('Y-m-01');
    $end_date = $dt->format('Y-m-t');

    return [
        'start' => $start_date,
        'end' => $end_date
    ];
}

/**
 * @param $customer_id
 * @param $month
 * @param $year
 * @param $minutes
 * @param $amount
 * @param $pre_2018_minutes
 * @param $pre_2018_amount
 */
function saveCustomerMonthlyLiability($customer_id, $month, $year, $minutes, $amount, $pre_2018_minutes = null, $pre_2018_amount = null, $pre_2018_minutes_used = null)
{
    global $db;

    $query = $db->prepare('SELECT id FROM customer_monthly_liability 
      WHERE customer_id = ? AND month = ? AND year = ?
      LIMIT 1');
    $query->execute([$customer_id, $month, $year]);
    $row = $query->fetch(PDO::FETCH_ASSOC);

    if ($row) {

        $sql = 'UPDATE customer_monthly_liability 
          SET liability_minutes=?, liability_amount=? ';

        $arr = [$minutes, $amount];

        if ($pre_2018_amount != null) {
            $sql .= ', pre_2018_minutes=?, pre_2018_amount=?, pre_2018_minutes_used=?';
            $arr[] = $pre_2018_minutes;
            $arr[] = $pre_2018_amount;
            $arr[] = $pre_2018_minutes_used;
        }

        $sql .= ' WHERE id = ?';
        $arr[] = $row['id'];

        $query = $db->prepare($sql);
        $query->execute($arr);
    } else {

        if ($pre_2018_minutes == null) {
            $pre_2018_minutes = 0;
        }
        if ($pre_2018_amount == null) {
            $pre_2018_amount = 0;
        }
        if ($pre_2018_minutes_used == null) {
            $pre_2018_minutes_used = 0;
        }

        $query = $db->prepare('INSERT INTO customer_monthly_liability VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, NULL)');
        $query->execute([$customer_id, $month, $year, $minutes, $amount, $pre_2018_minutes, $pre_2018_amount, $pre_2018_minutes_used]);
    }
}

/**
 * @param $month_index int Not 0 based
 * @return string e.g Jan, Feb
 */
function getMonthNameFromIndex($month_index)
{
    $date_obj   = DateTime::createFromFormat('!m', $month_index);
    $month_name = $date_obj->format('M');
    return $month_name;
}

/**
 * Should be called each day after office hours when today's sales email is sent
 */
function recordCustomerMonthlyLiability($all_months = false)
{
    global $db;

    // we had Pre-2018 liability month-wise in a csv file till Aug 2018 that we already imported
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'];

    $query = $db->prepare('SELECT 
          customer_id, customer_name, credit_cash, credit_time, per_minute_cost 
          FROM customer');
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) > 0) {
        foreach ($result as $customer) {

            if ($all_months) {
                foreach ($months as $month) {
                    $arr_liability = getCustomerLiabilityForMonth($customer['customer_id'], $month);
                    saveCustomerMonthlyLiability($customer['customer_id'], $month, 2018, $arr_liability[0], $arr_liability[1]);
                }
            } else {
                $arr_liability = getCustomerLiabilityForMonth($customer['customer_id'], date('M'));
                saveCustomerMonthlyLiability($customer['customer_id'], date('M'), date('Y'), $arr_liability[0], $arr_liability[1], $customer['credit_time'], $customer['credit_cash'], $arr_liability[2]);
            }
        }
    }
}

/**
 * @param $customer_id
 * @param $month
 * @return array
 */
function getCustomerLiabilityForMonth($customer_id, $month)
{
    global $db;

    $dt = new DateTime("first day of {$month}");
    $first_date_of_month = $dt->format('Y-m-d');
    $dt = new DateTime("last day of {$month}");
    $end_date_of_month = $dt->format('Y-m-d');

    $purchased_minutes = 0;
    $purchased_minutes_cost = 0;
    $minutes_used_from_purchase = 0;
    $minutes_used_from_purchase_cost = 0;
    $minutes_used_from_credit = 0;
    $minutes_used_from_credit_cost = 0;

    // get records starting from 1 year before date, as credit from records older than 1 year will be marked expired
    $query = $db->prepare('SELECT fp.*, fo.duration FROM flight_purchases fp
                  INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                  WHERE fp.created >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND fp.created <= ? AND fp.customer_id = ?');
    $query->execute([$end_date_of_month, $customer_id]);
    $result2 = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result2 as $fp) {

        $query = $db->prepare('SELECT flight_purchase_id, from_flight_purchase_id, duration AS minutes_used 
                  FROM flight_bookings 
                  WHERE flight_purchase_id = ?'); // AND DATE(flight_time) >= ? AND DATE(flight_time) <= ?

        $query->execute([$fp['id']]);
        $fb = $query->fetch(PDO::FETCH_ASSOC);

        if ($fp['deduct_from_balance'] == 0) {
            $purchased_minutes += $fp['duration'];
            $cost_per_minute = getPerMinuteCostOfPurchasedPackage($fp['id']);
            $purchased_minutes_cost += $cost_per_minute * $fp['duration'];
        } else if ($fp['deduct_from_balance'] == 1) {
            $cost_per_minute = getPerMinuteCostOfPurchasedPackage($fb['from_flight_purchase_id']);
        } else if ($fp['deduct_from_balance'] == 2) {
            $cost_per_minute = getPerMinuteCostForCustomer($fp['customer_id']);
        }

        if ($fp['deduct_from_balance'] != 2) {
            $minutes_used_from_purchase += $fb['minutes_used'];
            $minutes_used_from_purchase_cost += $cost_per_minute * $fb['minutes_used'];
        } else {
            $minutes_used_from_credit += $fb['minutes_used'];
            $minutes_used_from_credit_cost += $cost_per_minute * $fb['minutes_used'];
        }
    }

    $liability_minutes = $purchased_minutes - $minutes_used_from_purchase;
    $liability_minutes_cost = $purchased_minutes_cost - $minutes_used_from_purchase_cost;

    return [$liability_minutes, $liability_minutes_cost, $minutes_used_from_credit, $minutes_used_from_credit_cost];
}

function getFTFRevenue($start_date, $end_date, $include_rf_in_ftf = false, $return_sum = true)
{

    $arr_ftf = [];
    /** FTF without discounts applied */
    $arr2 = getDataAndAggregate('FTF', $start_date, $end_date);
    $arr_ftf = array_merge($arr_ftf, $arr2);

    $ftf_discounts = getDiscountsOf();

    foreach ($ftf_discounts as $ftf_discount) {
        //$arr2 = getDataAndAggregate($ftf_discount, $start_date, $end_date);

        $ftf_subdiscounts = getDiscountsOf($ftf_discount);
        $arr_subdiscount = [];
        foreach ($ftf_subdiscounts as $subdiscount) {
            $arr3 = getDataAndAggregate($subdiscount, $start_date, $end_date);
            $arr_subdiscount = array_merge($arr_subdiscount, $arr3);
        }

        $arr_subdiscount_sum[0] = [
            'package_name' => $ftf_discount,
            'paid' => array_sum(array_column($arr_subdiscount, 'paid')),
            'total_minutes' => array_sum(array_column($arr_subdiscount, 'total_minutes')),
            'minutes_used' => array_sum(array_column($arr_subdiscount, 'minutes_used')),
            'aed_value' => array_sum(array_column($arr_subdiscount, 'aed_value')),
            'avg_per_min' => array_sum(array_column($arr_subdiscount, 'avg_per_min')),
            $ftf_discount => $arr_subdiscount
        ];


        $arr_ftf = array_merge($arr_ftf, $arr_subdiscount_sum);
    }



    if ($include_rf_in_ftf) {
        /** RF */
        $arr2 = getDataAndAggregate('RF - Repeat Flights', $start_date, $end_date);
        $arr_ftf = array_merge($arr_ftf, $arr2);
    }

    if ($return_sum) {
        $arr_ftf_sum[0] = [
            'package_name' => 'FTF',
            'paid' => array_sum(array_column($arr_ftf, 'paid')),
            'total_minutes' => array_sum(array_column($arr_ftf, 'total_minutes')),
            'minutes_used' => array_sum(array_column($arr_ftf, 'minutes_used')),
            'aed_value' => array_sum(array_column($arr_ftf, 'aed_value')),
            'avg_per_min' => array_sum(array_column($arr_ftf, 'avg_per_min')),
        ];

        return $arr_ftf_sum;
    }

    return $arr_ftf;
}

/**
 * @param $discount_name
 * @param $start_date
 * @param $end_date
 * @return float
 */
function getFlightSaleViaDiscountName($discount_name, $start_date, $end_date)
{
    global $db;
    $stmt = $db->prepare("SELECT SUM(s.amount) AS amount FROM sales s
                        INNER JOIN flight_purchases fp ON s.invoice_number = fp.invoice_id
                        INNER JOIN discounts d ON fp.discount_id = d.id
                        WHERE d.category = ?
                        AND s.date >= ? AND s.date <= ?");
    $stmt->execute([$discount_name, $start_date, $end_date]);
    $row = $stmt->fetch();

    return round($row['amount'], 1);
}

function getPaidSalariesWagesForPeriod($start_date, $end_date)
{
    global $db;
    $query = $db->prepare('SELECT MAX(current_salary) AS paid FROM `employee_salaries` WHERE effect_date <= ? GROUP BY employee_id ORDER BY effect_date DESC');
    $query->execute([$end_date]);
    $rows = $query->fetchAll();

    $sum = array_sum(array_map(function ($item) {
        return $item['paid'];
    }, $rows));
    return $sum;
}

function getBenefitsAllowancesForPeriod($start_date, $end_date)
{
    global $db;
    $query = $db->prepare('SELECT MAX(monthly_total) - staff_uniform AS paid FROM `employee_salaries` WHERE effect_date <= ? GROUP BY employee_id ORDER BY effect_date DESC');
    $query->execute([$end_date]);
    $rows = $query->fetchAll();

    $sum = array_sum(array_map(function ($item) {
        return $item['paid'];
    }, $rows));
    return $sum;
}

/**
 * @param $customer_id
 * @param $flight_purchase_id
 * @param $year
 * @param $minutes
 * @param $cost
 */
function saveUnconsumedRevenue($customer_id, $flight_purchase_id, $year, $minutes, $cost)
{
    global $db;

    $query = $db->prepare(
        sprintf(
            'SELECT * FROM unconsumed_revenue WHERE customer_id=? AND year=? AND flight_purchase_id %s',
            $flight_purchase_id == null ? 'IS NULL' : "={$flight_purchase_id}"
        )
    );
    $query->execute([$customer_id, $year]);
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    if (count($result) > 0) {
        $query = $db->prepare('UPDATE unconsumed_revenue SET minutes = ?, cost = ? WHERE customer_id=? AND flight_purchase_id=? AND year=?');
        $query->execute([$minutes, $cost, $customer_id, $flight_purchase_id, $year]);
    } else {
        $query = $db->prepare('INSERT INTO unconsumed_revenue VALUES (?, ?, ?, ?, ?, NULL)');
        $query->execute([$customer_id, $flight_purchase_id, $year, $minutes, $cost]);
    }
}

function markPurchasesExpired()
{
    global $db;

    $query = $db->prepare('SELECT s.date, s.invoice_number, c.email, c.address, c.customer_name, c.customer_id 
      FROM sales s
      INNER JOIN customer c ON s.customer_id = c.customer_id
      WHERE s.expiry = DATE(NOW()) OR s.date = DATE_SUB(NOW(),INTERVAL 1 YEAR)
      AND c.customer_id NOT IN (
        SELECT customer_id FROM customer_yearly_purchases
      )');

    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        // select only those invoices which company is liable to pay
        $query2 = $db->prepare('SELECT * FROM flight_purchases fp 
          INNER JOIN flight_credits fc ON fp.id = fc.flight_purchase_id
          INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
          WHERE fp.invoice_id = ? AND fc.minutes > 0 AND fc.expired_on IS NULL AND fp.customer_id > 0');
        $query2->execute([$row['invoice_number']]);

        $result2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        $flight_purchase_ids = [];
        foreach ($result2 as $row2) {
            $minutes_unconsumed = $row2['minutes'];
            $discount_percent = $row2['discount'];
            $discount = $discount_percent * $row2['price'] / 100;
            $per_minute_cost = ($row2['price'] - $discount) / $row2['duration'];
            $price_unconsumed = round($minutes_unconsumed * $per_minute_cost, 2);
            saveUnconsumedRevenue($row2['customer_id'], $row2['flight_purchase_id'], date('Y', strtotime($row['date'])), $minutes_unconsumed, $price_unconsumed);

            $email = (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) ? $row['email'] : $row['address'];
            sendFlightPurchaseExpiredEmail($row['invoice_number'], $row['customer_name'], $email);

            $flight_purchase_ids[] = $row2['flight_purchase_id'];
        }

        if (count($flight_purchase_ids) > 0) {
            $query3 = $db->prepare('UPDATE flight_credits SET expired_on = DATE(NOW()) WHERE flight_purchase_id IN (?)');
            $str = implode(',', $flight_purchase_ids);
            $query3->execute([$str]);

            if ($query3->affected_rows == 0) {
                try {
                    $query4 = $db->prepare('INSERT INTO flight_credits VALUES (?, ?, ?, NOW())');
                    $query4->execute([
                        $row['customer_id'], $row2['id'], $minutes_unconsumed
                    ]);
                } catch (Exception $e) {
                    // so that code continues to execute
                }
            }
        }
    }
}

/**
 * @param $invoice_number
 * @param $customer_name
 * @param $customer_email
 */
function sendFlightPurchaseExpiredEmail($invoice_number, $customer_name, $customer_email)
{
    $body = sprintf('<div>
                <img src="%s" width="200" />
                <p>Hi <b>%s</b>, </p>
                <p>This is to notify you that your flight purchase against invoice no: <b>%s</b> has expired today. For more info, kindly send an email to <b>info@inflightdubai.com</b>.</p>
            </div>', BASE_URL . 'main/img/inflight_logo.png', $customer_name, $invoice_number);

    sendEmail($customer_email, 'Expiration of Purchased Flights', $body, true);
}

function sendFlightExpiryReminder()
{
    global $db;

    // get purchases which are expiring after 30 days OR which were purchased 1 year minus 30 days prior (for cases where expiry is null)
    $query = $db->prepare('SELECT s.date, s.invoice_number, c.email, c.address, c.customer_name, s.expiry
      FROM sales s
      INNER JOIN customer c ON s.customer_id = c.customer_id
      WHERE (
          s.expiry = DATE(NOW() + INTERVAL 30 DAY) 
          OR 
          s.date = DATE_ADD(DATE_SUB(NOW(),INTERVAL 1 YEAR), INTERVAL 30 DAY)
        )
        AND c.customer_id NOT IN (
          SELECT customer_id FROM customer_yearly_purchases
        )');

    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {

        // select only those invoices which company is liable to pay
        $query2 = $db->prepare('SELECT * FROM flight_purchases fp 
          INNER JOIN flight_credits fc ON fp.id = fc.flight_purchase_id
          INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
          WHERE fp.invoice_id = ? AND fc.minutes > 0 AND fc.expired_on IS NULL');
        $query2->execute([$row['invoice_number']]);

        $result2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        $flight_offers = [];
        foreach ($result2 as $row2) {
            $flight_offers[] = $row2['flight_purchase_id'] . ' - ' . $row2['offer_name'];
        }

        if (count($flight_offers) > 0) {
            $body = sprintf('<div>
                <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
                <p>Hi <b>' . $row['customer_name'] . '</b>:, </p>
                <p>This is to notify you that the flights you purchased on <b>%s</b> against invoice no: <b>%s</b> will be expiring within a month.
                    Kindly utilize the flight offers or send an email to <b>info@inflightdubai.com</b> for more information. After 30 days from now, these purchases will be expired.
                </p>
            </div>', $row['date'], $row['invoice_number']);

            $email = (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) ? $row['email'] : $row['address'];
            sendEmail($email, 'Expiration of Purchased Offers', $body, true);
        }
    }
}

function getUserById($user_id)
{
    global $db;
    $query = $db->prepare('SELECT * FROM user WHERE id = ?');
    $query->execute([$user_id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);
    return $row;
}

function getRowById($tbl, $id)
{
    global $db;
    $query = $db->prepare('SELECT * FROM ' . $tbl . ' WHERE id = ?');
    $query->execute([$id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);
    return $row;
}

function getRowsWhere($tbl, $col, $id)
{
    global $db;
    $query = $db->prepare('SELECT * FROM ' . $tbl . ' WHERE ' . $col . ' = ?');
    $query->execute([$id]);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
}

function deleteRowById($tbl, $id)
{
    global $db;
    $query = $db->prepare('DELETE FROM ' . $tbl . ' WHERE id = ?');
    $query->execute([$id]);
    return $query->affected_rows;
}

function deleteRowsWhere($tbl, $col, $val)
{
    global $db;
    $query = $db->prepare('DELETE FROM ' . $tbl . ' WHERE ' . $col . ' = ?');
    $query->execute([$val]);
    return $query->affected_rows;
}

function getRnL($start_date, $end_date, $parent_package = null, $level = 2)
{
    global $db;

    $package_column = $level == 1 ? 'parent_package AS package_name' : 'package AS package_name';

    $sql = 'SELECT ' . $package_column . ', SUM(paid) AS paid, SUM(total_minutes) AS total_minutes, SUM(minutes_used) AS minutes_used, SUM(aed_value) AS aed_value, avg_per_min
      FROM rnl_cache WHERE date >= ? AND date <= ?';
    $params = [$start_date, $end_date];

    if ($parent_package == 'FTF') {
        $sql .= ' AND (package = "FTF" OR parent_package = "FTF")';
    } else if ($level == 1 && $parent_package == 'Military') {
        $sql .= ' AND (parent_package = "Military")';
    } else if ($parent_package != null) {
        $sql .= ' AND parent_package = ?';
        $params[] = $parent_package;
    } else {
        $sql .= ' AND parent_package IS NULL';
    }

    if ($level == 1) {
        $sql .= ' GROUP BY parent_package';
    } else {
        $sql .= ' GROUP BY package';
    }

    $query = $db->prepare($sql);
    $query->execute($params);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    if ($parent_package == null) {
        foreach ($rows as &$row) {
            if ($row['package_name'] == 'FTF') { // sum all ftf packages and show as one row
                $sub_rows1 = getRnL($start_date, $end_date, $row['package_name']);
                $row = [
                    'package_name' => 'FTF',
                    'paid' => array_sum(array_column($sub_rows1, 'paid')),
                    'total_minutes' => array_sum(array_column($sub_rows1, 'total_minutes')),
                    'minutes_used' => array_sum(array_column($sub_rows1, 'minutes_used')),
                    'aed_value' => array_sum(array_column($sub_rows1, 'aed_value')),
                    'avg_per_min' => $row['avg_per_min'],
                ];
            }
        }
    }

    return $rows;
}

function getRnLForCurrentDay($start_day, $end_day)
{
    $arr_revenue = [];

    $arr_ftf = getFTFRevenue($start_day, $end_day, false, false);

    $arr_ftf_sum[0] = [
        'package_name' => 'FTF',
        'paid' => array_sum(array_column($arr_ftf, 'paid')),
        'total_minutes' => array_sum(array_column($arr_ftf, 'total_minutes')),
        'minutes_used' => array_sum(array_column($arr_ftf, 'minutes_used')),
        'aed_value' => array_sum(array_column($arr_ftf, 'aed_value')),
        'avg_per_min' => array_sum(array_column($arr_ftf, 'avg_per_min')),
    ];

    $arr_revenue = array_merge($arr_revenue, $arr_ftf_sum);

    /** UP-Sale */
    $arr2 = getDataAndAggregate('UP-Sale', $start_day, $end_day);
    $arr_revenue = array_merge($arr_revenue, $arr2);

    /** RF */
    $arr2 = getDataAndAggregate('RF - Repeat Flights', $start_day, $end_day);
    $arr_revenue = array_merge($arr_revenue, $arr2);

    /** SKYDIVERS */
    $arr2 = getDataAndAggregate('Skydivers', $start_day, $end_day);
    $arr_revenue = array_merge($arr_revenue, $arr2);

    $arr_military = [];
    /** Military */
    $arr2 = getDataAndAggregate('Military', $start_day, $end_day);
    $arr_military = array_merge($arr_military, $arr2);

    /** Navy Seal */
    $arr2 = getDataAndAggregate('Navy Seal', $start_day, $end_day);
    $arr_military = array_merge($arr_military, $arr2);

    /** Presidential Guard */
    $arr2 = getDataAndAggregate('Presidential Guard', $start_day, $end_day);
    $arr_military = array_merge($arr_military, $arr2);

    /** Sky god */
    $arr2 = getDataAndAggregate('Sky god%', $start_day, $end_day);
    $arr_military = array_merge($arr_military, $arr2);

    $arr_military_sum[0] = [
        'package_name' => 'Military',
        'paid' => array_sum(array_column($arr_military, 'paid')),
        'total_minutes' => array_sum(array_column($arr_military, 'total_minutes')),
        'minutes_used' => array_sum(array_column($arr_military, 'minutes_used')),
        'aed_value' => array_sum(array_column($arr_military, 'aed_value')),
        'avg_per_min' => array_sum(array_column($arr_military, 'avg_per_min')),
    ];

    $arr_revenue = array_merge($arr_revenue, $arr_military_sum);

    $arr_retail = [];

    /** HELMET RENT */
    $arr2 = getMerchandiseRevenue('Helmet Rent', $start_day, $end_day);
    $arr_retail = array_merge($arr_retail, $arr2);

    /** VIDEO */
    $arr2 = getMerchandiseRevenue('Video', $start_day, $end_day);
    $arr_retail = array_merge($arr_retail, $arr2);

    /** MERCHANDISE */
    $arr2 = getMerchandiseRevenue(TYPE_MERCHANDISE, $start_day, $end_day);
    $arr_retail = array_merge($arr_retail, $arr2);

    /** OTHER e.g. Facility Rental, Sandstorm Registration Fee  */
    $arr2 = getOtherRevenue('Other', $start_day, $end_day);
    $arr_retail = array_merge($arr_retail, $arr2);

    $arr_retail_sum[0] = [
        'package_name' => 'Retail Revenue',
        'paid' => array_sum(array_column($arr_retail, 'paid')),
        'aed_value' => array_sum(array_column($arr_retail, 'aed_value')),
    ];

    $arr_revenue = array_merge($arr_revenue, $arr_retail_sum);

    return [
        'arr_revenue' => $arr_revenue,
        'arr_ftf' => $arr_ftf,
        'arr_military' => $arr_military,
        'arr_retail' => $arr_retail
    ];
}

function saveRnLRow($start_date, $package, $parent_package, $paid, $total_minutes, $minutes_used, $aed_value, $avg_per_min)
{
    global $db;

    switch ($package) {
        case NAVY_SEAL:
            $start_date_like = substr($start_date, 0, -2) . "%";
            $query = $db->prepare('SELECT * FROM rnl_cache WHERE date LIKE "' . $start_date_like . '" AND package = ? LIMIT 1');
            $query->execute([$package]);
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $paid = 0;
                $total_minutes = 0;
            } else {
                $purchase = getCustomerYearlyPurchase(756, $start_date, null);
                $total_minutes = $purchase['per_month_minutes'];
                $paid = $purchase['per_minute_cost'] * $total_minutes;
            }
            break;
    }

    $query = $db->prepare('INSERT INTO rnl_cache VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)');
    $query->execute([$start_date, $package, $parent_package, (float) $paid, (int) $total_minutes, (int) $minutes_used, (float) $aed_value, (float) $avg_per_min]);
}
