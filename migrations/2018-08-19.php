<?php
include_once ('../connect.php');

$query = $db->prepare('SELECT * FROM discounts WHERE category LIKE "Groupon%" ');
$query->execute();
$discounts = $query->fetchAll(PDO::FETCH_ASSOC);
foreach($discounts as $discount) {
    for($i=0; $i<500; $i++) {
        $code = createRandomPassword('', 12, true);
        $query = $db->prepare(" INSERT INTO groupon_discount_codes(id, discount_id, code) VALUES (NULL, ?, ?)");
        $query->execute([$discount['id'], $code]);
    }
}