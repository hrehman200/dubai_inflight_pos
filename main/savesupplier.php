<?php
session_start();
include_once('../connect.php');
$a            = $_POST['name'];
$b            = $_POST['address'];
$c            = $_POST['contact'];
$d            = $_POST['cperson'];
$e            = $_POST['note'];
$email        = $_POST['email'];
$category     = $_POST['category'];
$payment_term = $_POST['payment_term'];
$attachment_1   = $_FILES['attachment_1'];
$attachment_2   = $_FILES['attachment_2'];

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

// query
$sql = "INSERT INTO supliers (suplier_name,suplier_address,suplier_contact,contact_person,note,email,category,payment_term,attachment_1,attachment_2)
  VALUES (:a,:b,:c,:d,:e,:email,:category,:payment_term,:attachment_1,:attachment_2)";
$q   = $db->prepare($sql);
$q->execute(array(
    ':a'            => $a,
    ':b'            => $b,
    ':c'            => $c,
    ':d'            => $d,
    ':e'            => $e,
    ':email'        => $email,
    ':category'     => $category,
    ':payment_term' => $payment_term,
    ':attachment_1' => $attachment_1,
    ':attachment_2' => $attachment_2,
));
header("location: supplier.php");


?>