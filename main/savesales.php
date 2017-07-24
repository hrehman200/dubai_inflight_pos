<?php
include('../connect.php');
$a               = $_POST['invoice'];
$b               = $_POST['cashier'];
$c               = $_POST['date'];
$d               = $_POST['ptype'];
$e               = $_POST['amount'];
$z               = $_POST['profit'];
$mode_of_payment = $_POST['mode_of_payment'];
$cname           = $_POST['cname'];
$discount        = $_POST['discount'];
$customer_id     = $_POST['customerId'];
$partner_id      = $_POST['partnerId'];

$monthNumber = date_parse_from_format("m/d/y", $c);
$monthNum    = $monthNumber["month"];

$dateObj   = DateTime::createFromFormat('!m', $monthNum);
$monthName = $dateObj->format('M');

$specificyear = date_parse_from_format("m/d/y", $c);
$salesyear    = $specificyear["year"];

$due_date = '0000-00-00';

if ($d == 'cash' || $d == 'credit') {
    $f        = $_POST['cash'];
    $due_date = '0000-00-00';

} else if ($d == 'account') {
    $due_date = $_POST['due_date'];
}

if (@$_POST['savingflight'] == 1) {

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,balance, mode_of_payment, discount, customer_id, due_date, sale_type)
        VALUES (:a,:b,:c,:d,:monh,:year,:e,:z,:balance, :mode_of_payment, :discount, :customerId, :dueDate, 'Service')";
    $q   = $db->prepare($sql);
    $arr = array(':a'               => $a,
                 ':b'               => $b,
                 ':c'               => $c,
                 ':d'               => $d,
                 ':monh'            => $monthName,
                 ':year'            => $salesyear,
                 ':e'               => $e,
                 ':z'               => $z,
                 ':balance'         => (int)$f,
                 ':mode_of_payment' => $mode_of_payment,
                 ':discount'        => $discount,
                 ':customerId'      => $customer_id,
                 ':dueDate'         => $due_date);
    $q->execute($arr);


    $query = $db->prepare("UPDATE flight_purchases SET status = 1 WHERE invoice_id = :invoiceId");
    $query->execute(array(
        ':invoiceId' => $a
    ));

    header("location: flight_preview.php?invoice=$a");

} else {

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,balance, mode_of_payment)
        VALUES (:a,:b,:c,:d,:monh,:year,:e,:z,:balance, :mode_of_payment)";
    $q   = $db->prepare($sql);
    $q->execute(array(':a' => $a, ':b' => $b, ':c' => $c, ':d' => $d, ':monh' => $monthName, ':year' => $salesyear, ':e' => $e, ':z' => $z, ':balance' => $f, 'mode_of_payment' => $mode_of_payment));

    header("location: preview.php?invoice=$a");
}


?>