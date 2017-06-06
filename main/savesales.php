<?php
session_start();
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

$monthNumber = date_parse_from_format("m/d/y", $c);
$monthNum    = $monthNumber["month"];

$dateObj   = DateTime::createFromFormat('!m', $monthNum);
$monthName = $dateObj->format('M');

$specificyear = date_parse_from_format("m/d/y", $c);
$salesyear    = $specificyear["year"];

if ($d == 'cash') {
    $f = $_POST['cash'];
} else if ($d == 'credit') {
    $f = $_POST['due'];
}

if($_POST['savingflight'] == 1) {

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment, discount)
        VALUES (:a,:b,:c,:d,:monh,:year,:e,:z,:f, :mode_of_payment, :discount)";
    $q   = $db->prepare($sql);
    $q->execute(array(':a' => $a, ':b' => $b, ':c' => $c, ':d' => $d, ':monh' => $monthName, ':year' => $salesyear, ':e' => $e, ':z' => $z, ':f' => $f, ':mode_of_payment' => $mode_of_payment, ':discount'=>$discount));


    $query = $db->prepare("UPDATE flight_purchases SET status = 1 WHERE invoice_id = :invoiceId");
    $query->execute(array(
       ':invoiceId' => $a
    ));

    header("location: flight_preview.php?invoice=$a");

} else {

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment)
        VALUES (:a,:b,:c,:d,:monh,:year,:e,:z,:f, :mode_of_payment)";
    $q   = $db->prepare($sql);
    $q->execute(array(':a' => $a, ':b' => $b, ':c' => $c, ':d' => $d, ':monh' => $monthName, ':year' => $salesyear, ':e' => $e, ':z' => $z, ':f' => $f, 'mode_of_payment' => $mode_of_payment));

    header("location: preview.php?invoice=$a");
}


?>