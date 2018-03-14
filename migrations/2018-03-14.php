<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 14/03/2018
 * Time: 12:43 PM
 */

include_once ('../connect.php');

$query = $db->prepare('SELECT DISTINCT(fp.flight_offer_id), fo.price FROM flight_offers fo
  INNER JOIN flight_purchases fp ON fp.flight_offer_id = fo.id
');
$query->execute();

while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $query2 = $db->prepare('UPDATE flight_purchases SET price = ? WHERE flight_offer_id = ?');
    $query2->execute([$row['price'], $row['flight_offer_id']]);
}

echo 'Done';