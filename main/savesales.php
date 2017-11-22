<?php
include_once('../connect.php');
$a               = $_POST['invoice'];
$b               = $_POST['cashier'];
$c               = $_POST['date'];
$d               = $_POST['ptype'];
$e               = $_POST['amount'];
$z               = $_POST['profit'];
$cname           = $_POST['cname'];
$customer_id     = $_POST['customerId'];
$salesType   = $_POST['salesType'];
$productName = $_POST['productName'];

$mode_of_payment = $_POST['mode_of_payment'];
$mode_of_payment_1 = $_POST['mode_of_payment_1'];
$total_cash = $_POST['total_cash'];

$cash = $_POST['cash'];
$remaining_cash = $_POST['remaining_cash'];

$query = $db->prepare('SELECT SUM(discount) AS total_discount FROM sales_order WHERE invoice=?');
$query->execute([$a]);
$row = $query->fetch();
$discount = $row['total_discount'];

if ($salesType == '' || empty($salesType )) {
    # code...
    $salesType ='Service';
}

$monthNumber = date_parse_from_format("Y-m-d", $c);
$monthNum    = $monthNumber["month"];

$dateObj   = DateTime::createFromFormat('!m', $monthNum);
$monthName = $dateObj->format('M');

$specificyear = date_parse_from_format("Y-m-d", $c);
$salesyear    = $specificyear["year"];

if ($d == 'cash') {
    $f = $_POST['cash'] + $remaining_cash;
} else if ($d == 'credit') {
    $f = $_POST['due'];
}

if (@$_POST['savingflight'] == 1) {

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment, discount, customer_id, sale_type, mode_of_payment_1, mop_amount, mop1_amount, after_dis)
        VALUES (:a,:b,:c,:d,:monh,:year,:e,:z,:due_date, :mode_of_payment, :discount, :customerId, :Service, :mode_of_payment_1, :mop_amount, :mop1_amount, :discountedValue)";
    $q   = $db->prepare($sql);
    $q->execute(array(':a' => $a, ':b' => $b, ':c' => $c, ':d' => $d, ':monh' => $monthName, ':year' => $salesyear, ':e' => $e, ':z' => $z, ':due_date' => $f, ':mode_of_payment' => $mode_of_payment, ':discount' => $discount, ':customerId' => $customer_id, ':Service' => $salesType, ':mode_of_payment_1' => $mode_of_payment_1
        , ':mop_amount' => $cash, ':mop1_amount' => $remaining_cash, ':discountedValue' => $discountedValue));

    if ($mode_of_payment == 'credit_cash') {
        # code...
        $result = $db->prepare("SELECT * FROM customer WHERE customer_id = :customer_id");
        $result->execute(array('customer_id'=>$_POST['customerId']));
        $row = $result->fetch();

        $credit_cash = $row['credit_cash'];
        $remainingCreditCash = $credit_cash - $_POST['cash'];

        $queryCS = $db->prepare("UPDATE customer SET credit_cash =:credit_cash WHERE customer_id = :customer_id");
        $queryCS->execute(array(':credit_cash' => $remainingCreditCash, ':customer_id' => $_POST['customerId']));
    }

    $query = $db->prepare("UPDATE flight_purchases SET status = 1 WHERE invoice_id = :invoiceId");
    $query->execute(array(
        ':invoiceId' => $a
    ));

    adjustBalanceForDeletedFlightBookings($a);

    header("location: flight_preview.php?invoice=$a&payfirst=$cash&paysecond=$remaining_cash");

} else {

    $salesType ='Merchandise';

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment, discount, customer_id, sale_type, mode_of_payment_1, mop_amount, mop1_amount, after_dis)
        VALUES (:a,:b,:c,:d,:monh,:year,:e,:z,:due_date, :mode_of_payment, :discount, :customerId, :Service, :mode_of_payment_1, :mop_amount, :mop1_amount, :discountedValue)";
    $q   = $db->prepare($sql);
    $q->execute(array(':a' => $a, ':b' => $b, ':c' => $c, ':d' => $d, ':monh' => $monthName, ':year' => $salesyear, ':e' => $e, ':z' => $z, ':due_date' => $f, ':mode_of_payment' => $mode_of_payment, ':discount' => $discount, ':customerId' => $customer_id, ':Service' => $salesType, ':mode_of_payment_1' => $mode_of_payment_1
        , ':mop_amount' => $cash, ':mop1_amount' => $remaining_cash, ':discountedValue' => $total_cash));



    header("location: preview.php?invoice=$a&payfirst=$cash&paysecond=$remaining_cash&sale_type=&d1=&d2=");
}


?>
