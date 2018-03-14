<?php
include_once('../connect.php');

$invoice = $_POST['req_reference_number'];

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

$query = $db->prepare('SELECT SUM(discount) AS total_discount FROM flight_purchases WHERE invoice_id=?');
$query->execute([$invoice]);
$row = $query->fetch();
$discount = $row['total_discount'];

$salesType = 'Service';

$today_date = date('Y-m-d');

$monthNumber = date_parse_from_format("Y-m-d", $today_date);
$monthNum = $monthNumber["month"];

$dateObj = DateTime::createFromFormat('!m', $monthNum);
$monthName = $dateObj->format('M');

$specificyear = date_parse_from_format("Y-m-d", $today_date);
$salesyear = $specificyear["year"];

$sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment, discount, customer_id, sale_type, mode_of_payment_1, mop_amount, mop1_amount, after_dis)
    VALUES (:a,:b,:c,:d,:month,:year,:e,:z,:due_date, :mode_of_payment, :discount, :customerId, :Service, :mode_of_payment_1, :mop_amount, :mop1_amount, :discountedValue)";
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
    ':customerId' => $_SESSION['CUSTOMER_ID'],
    ':Service' => $salesType,
    ':mode_of_payment_1' => '',
    ':mop_amount' => $_POST['req_amount'],
    ':mop1_amount' => '0',
    ':discountedValue' => $_POST['req_amount']));

$query = $db->prepare("UPDATE flight_purchases SET status = 1 WHERE invoice_id = :invoiceId");
$query->execute(array(
    ':invoiceId' => $invoice
));

adjustBalanceForDeletedFlightBookings($invoice);

header("location: flight_preview.php?invoice=$invoice");

?>
