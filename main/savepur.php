<?php
session_start();
include_once('../connect.php');
$transaction_id = $_POST['transaction_id'];
$editing        = $transaction_id > 0;
$a              = $_POST['iv'];
$b              = $_POST['date'];
$c              = $_POST['supplier'];
$d              = $_POST['remarks'];
$invoice_amount = $_POST['invoice_amount'];
$prev_invoice_amount = $_POST['prev_invoice_amount'];
$po_no          = $_POST['po_no'];
$po_amount      = $_POST['po_amount'];
$attachment_1   = $_FILES['attachment_1'];
$attachment_2   = $_FILES['attachment_2'];
$attachment_3   = $_FILES['attachment_3'];

if (!empty($attachment_1['name'])) {
    if (move_uploaded_file($attachment_1['tmp_name'], 'uploads/' . $attachment_1['name'])) {
        $attachment_1 = $attachment_1['name'];
    }
}

if (!empty($attachment_2['name'])) {
    if (move_uploaded_file($attachment_2['tmp_name'], 'uploads/' . $attachment_2['name'])) {
        $attachment_2 = $attachment_2['name'];
    }
}

if (!empty($attachment_3['name'])) {
    if (move_uploaded_file($attachment_3['tmp_name'], 'uploads/' . $attachment_3['name'])) {
        $attachment_3 = $attachment_3['name'];
    }
}

if(!$editing) {
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

} else {
    // match invoice_amount and see whether its changed
    $query = $db->prepare('SELECT invoice_amount, balance FROM purchases WHERE transaction_id=?');
    $query->execute(array($transaction_id));
    if ($query->rowCount() > 0) {
        $row     = $query->fetch();
        if($row['invoice_amount'] != $invoice_amount) {
            $balance = $row['balance'] + $prev_invoice_amount;
            $balance -= $invoice_amount;
        }
    }
}

$query = $db->prepare('SELECT payment_term FROM supliers WHERE suplier_name = ? LIMIT 1');
$query->execute(array($c));
$row      = $query->fetch();
$days     = (int)$row['payment_term'];
$due_date = date('Y-m-d', strtotime($b . ' +30 day'));

// query
if ($editing) {

    $sql = "UPDATE purchases SET invoice_number=:a,
            date=:b,
            suplier=:c,
            remarks=:d,
            invoice_amount=:invoice_amount,
            po_no=:po_no,
            po_amount=:po_amount,
            due_date=:due_date";

    $arr = array(':a'              => $a,
                 ':b'              => $b,
                 ':c'              => $c,
                 ':d'              => $d,
                 ':invoice_amount' => $invoice_amount,
                 ':po_no'          => $po_no,
                 ':po_amount'      => $po_amount,
                 ':due_date'       => $due_date,
                 ':transaction_id' => $transaction_id
    );

    if(isset($balance)) {
        $arr[':balance'] = $balance;
        $sql .= ",balance=:balance";
    }

    if (!is_array($attachment_1)) {
        $arr[':attachment_1'] = $attachment_1;
        $sql .= ",attachments=:attachment_1";
    }

    if (!is_array($attachment_2)) {
        $arr[':attachment_2'] = $attachment_2;
        $sql .= ",attachments_2=:attachment_2";
    }

    if (!is_array($attachment_3)) {
        $arr[':attachment_3'] = $attachment_3;
        $sql .= ",attachments_3=:attachment_3";
    }

    $sql .= " WHERE transaction_id = :transaction_id";
    $q = $db->prepare($sql);
    $q->execute($arr);

} else {

    $arr = array(
        ':a'              => $a,
        ':b'              => $b,
        ':c'              => $c,
        ':d'              => $d,
        ':invoice_amount' => $invoice_amount,
        ':po_no'          => $po_no,
        ':po_amount'      => $po_amount,
        ':balance'        => $balance,
        ':due_date'       => $due_date
    );

    if (!is_array($attachment_1)) {
        $arr[':attachment_1'] = $attachment_1;
    } else {
        $arr[':attachment_1'] = '';
    }

    if (!is_array($attachment_2)) {
        $arr[':attachment_2'] = $attachment_2;
    }else {
        $arr[':attachment_2'] = '';
    }

    if (!is_array($attachment_3)) {
        $arr[':attachment_3'] = $attachment_3;
    }else {
        $arr[':attachment_3'] = '';
    }

    $sql = "INSERT INTO purchases (invoice_number,date,suplier,remarks,invoice_amount,po_no,po_amount,attachments,attachments_2,attachments_3,balance,due_date)
            VALUES (:a,:b,:c,:d,:invoice_amount,:po_no,:po_amount,:attachment_1,:attachment_2,:attachment_3,:balance,:due_date)";
    $q   = $db->prepare($sql);
    $q->execute($arr);


}

header("location: purchasesportal.php?iv=$a");


?>