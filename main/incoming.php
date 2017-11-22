<?php
session_start();
include_once('../connect.php');
$invoice_id       = $_POST['invoice'];
$product_id       = $_POST['product'];
$quantity         = $_POST['qty'];
$w                = $_POST['pt'];
$date             = $_POST['date'];
$discount_percent = FLAT_DISCOUNT; // later operator can edit it

$result   = $db->prepare("SELECT * FROM products WHERE product_id= :productId");
$result->bindParam(':productId', $product_id);
$result->execute();

$row            = $result->fetch();
$product_price  = $row['price'];
$code           = $row['product_code'];
$gen            = $row['gen_name'];
$name           = $row['product_name'];
$product_profit = $row['profit'];

// update quantity in stock
$sql = "UPDATE products 
        SET qty=qty-?
		WHERE product_id=?";
$q   = $db->prepare($sql);
$q->execute(array($quantity, $product_id));

$discount_amount              = $discount_percent * $product_price / 100;
$product_price_after_discount = $product_price - $discount_amount;
$total_price                  = $product_price * $quantity;
$total_profit                 = $product_profit * $quantity;

// query
$sql = "INSERT INTO
        sales_order (invoice,product,qty,amount,name,price,profit,product_code,gen_name,date,vat_code_id, discount)
        VALUES
        (:a,:b,:c,:d,:e,:f,:h,:i,:j,:k,:vatCodeId, :discount)";
$q   = $db->prepare($sql);
$q->execute(array(
    ':a'         => $invoice_id,
    ':b'         => $product_id,
    ':c'         => $quantity,
    ':d'         => $total_price,
    ':e'         => $name,
    ':f'         => $product_price,
    ':h'         => $total_profit,
    ':i'         => $code,
    ':j'         => $gen,
    ':k'         => $date,
    ':vatCodeId' => getVatCodeId($gen),
    ':discount'  => $discount_percent
));
header("location: sales.php?id=$w&invoice=$invoice_id");


?>