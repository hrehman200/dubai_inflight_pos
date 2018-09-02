<?php
include_once ('../connect.php');

set_time_limit(0);

$query = $db->prepare('SELECT * FROM discounts WHERE id IN (46, 47, 48) ');
$query->execute();
$discounts = $query->fetchAll(PDO::FETCH_ASSOC);

$codes_per_discount_category = $_GET['n'];

foreach($discounts as $discount) {
    for($i=0; $i<$codes_per_discount_category; $i++) {
        $code = createRandomPassword('', 12, true);
        $query = $db->prepare(" INSERT INTO groupon_discount_codes(id, discount_id, code) VALUES (NULL, ?, ?)");
        $query->execute([$discount['id'], $code]);
    }
    echo sprintf('%d codes generated for %s<br/>', $codes_per_discount_category, $discount['category']);
}