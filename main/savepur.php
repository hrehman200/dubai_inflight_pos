<?php
session_start();
include('../connect.php');
$a              = $_POST['iv'];
$b              = $_POST['date'];
$c              = $_POST['supplier'];
$d              = $_POST['remarks'];
$invoice_amount = $_POST['invoice_amount'];
$po_no          = $_POST['po_no'];
$po_amount      = $_POST['po_amount'];
$attachments    = $_FILES['attachments'];

if (!empty($attachments)) {
    $attachments     = reArrayFiles($attachments);
    $arr_attachments = [];

    foreach ($attachments as $val) {
        if (move_uploaded_file($val['tmp_name'], 'uploads/' . $val['name'])) {
            $arr_attachments[] = $val['name'];
        }
    }
}

$query = $db->prepare('SELECT po_amount, balance FROM purchases
  WHERE po_no=?
  ORDER BY date DESC
  LIMIT 1');
$query->execute(array($po_no));
if ($query->rowCount() > 0) {
    $row     = $query->fetch();
    $balance = $row['balance'] - $invoice_amount;
} else {
    $balance = $po_amount - $invoice_amount;
}


$query = $db->prepare('SELECT payment_term FROM supliers WHERE suplier_name = ? LIMIT 1');
$query->execute(array($c));
$row      = $query->fetch();
$days     = (int)$row['payment_term'];
$due_date = date('Y-m-d', strtotime($b . ' +30 day'));

// query
$sql = "INSERT INTO purchases (invoice_number,date,suplier,remarks,invoice_amount,po_no,po_amount,attachments,balance,due_date)
  VALUES (:a,:b,:c,:d,:invoice_amount,:po_no,:po_amount,:attachments,:balance,:due_date)";
$q   = $db->prepare($sql);
$q->execute(array(':a'              => $a,
                  ':b'              => $b,
                  ':c'              => $c,
                  ':d'              => $d,
                  ':invoice_amount' => $invoice_amount,
                  ':po_no'          => $po_no,
                  ':po_amount'      => $po_amount,
                  ':attachments'    => implode(";;;", $arr_attachments),
                  ':balance'        => $balance,
                  ':due_date'       => $due_date
));

header("location: purchasesportal.php?iv=$a");


?>