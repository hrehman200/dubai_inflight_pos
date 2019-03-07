<?php
include_once('../connect.php');

$invoice = $_POST['req_reference_number'];

sendEmail('hrehman200@gmail.com', 'Testing', 'Invoice: '.$invoice . json_encode($_POST));

if(empty($invoice) || is_null($invoice)) {

    // cleanup if customer cancelled the purchase
    $query = $db->prepare('SELECT id
      FROM flight_purchases fp
      WHERE status = 0 AND customer_id = ?');
    $query->execute([$_SESSION['CUSTOMER_ID']]);
    $result = $query->fetchAll();

    if (count($result) > 0) {

        foreach($result as $row) {
            //deleteFlightPurchase($row['id']);
        }
    }

    header("location: store.php");
    exit();
}

if(array_key_exists('decision', $_POST) && $_POST['decision'] == 'ERROR') {
    echo 'An error occurred. Please go back and retry the transaction.<br/>';
    echo '<b>Error: </b>'. $_POST['message'];
    exit();
}

// mark groupon codes as used
$query = $db->prepare('SELECT groupon_code FROM flight_purchases WHERE groupon_code IS NOT NULL AND invoice_id=? ');
$query->execute([$invoice]);
$groupon_codes = $query->fetchAll(PDO::FETCH_ASSOC);
foreach($groupon_codes as $gc) {
    $query = $db->prepare('UPDATE groupon_discount_codes SET used = NOW() WHERE code = ? LIMIT 1');
    $query->execute([$gc['groupon_code']]);
}

$query = $db->prepare('SELECT customer_id FROM customer WHERE email = ? OR address = ?');
$query->execute([$_POST['req_bill_to_email'], $_POST['req_bill_to_email']]);
$customer = $query->fetch(PDO::FETCH_ASSOC);
if($customer && $customer['customer_id'] > 0) {
    $customer_id = $customer['customer_id'];
} else {
    $customer_id = 0;
}

$query = $db->prepare('SELECT SUM(discount) AS total_discount FROM flight_purchases WHERE invoice_id=?');
$query->execute([$invoice]);
$row = $query->fetch();
$discount = is_null($row['total_discount']) ? 0 : $row['total_discount'];

$salesType = 'Service';

$today_date = date('Y-m-d');

$monthNumber = date_parse_from_format("Y-m-d", $today_date);
$monthNum = $monthNumber["month"];

$dateObj = DateTime::createFromFormat('!m', $monthNum);
$monthName = $dateObj->format('M');

$specificyear = date_parse_from_format("Y-m-d", $today_date);
$salesyear = $specificyear["year"];

$sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment, discount, customer_id, sale_type, mode_of_payment_1, mop_amount, mop1_amount, after_dis,
      expiry)
    VALUES (:a,:b,:c,:d,:month,:year,:e,:z,:due_date, :mode_of_payment, :discount, :customerId, :Service, :mode_of_payment_1, :mop_amount, :mop1_amount, :discountedValue, 
      DATE(NOW() + INTERVAL 1 YEAR))";
$q = $db->prepare($sql);
$q->execute(array(
    ':a' => $invoice,
    ':b' => 'Customer',
    ':c' => $today_date,
    ':d' => 'online',
    ':month' => $monthName,
    ':year' => $salesyear,
    ':e' => $_POST['req_amount'],
    ':z' => $_POST['req_amount'],
    ':due_date' => $_POST['req_amount'],
    ':mode_of_payment' => 'Online',
    ':discount' => $discount,
    ':customerId' => $customer_id,
    ':Service' => $salesType,
    ':mode_of_payment_1' => '',
    ':mop_amount' => $_POST['req_amount'],
    ':mop1_amount' => '0',
    ':discountedValue' => $_POST['req_amount']));

$query = $db->prepare("UPDATE flight_purchases SET status = 1, customer_id = :customerId WHERE invoice_id = :invoiceId");
$query->execute(array(
    ':invoiceId' => $invoice,
    ':customerId' => $customer_id
));

adjustBalanceForDeletedFlightBookings($invoice);

header("location: flight_preview.php?invoice=$invoice");

?>
