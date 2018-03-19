<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18/02/2018
 * Time: 12:54 PM
 */

require_once dirname(dirname(__DIR__)).'/connect.php';

if(php_sapi_name() === 'cli') {

    $query = $db->prepare('SELECT s.date, s.invoice_number, c.email, c.customer_name FROM sales s
      INNER JOIN customer c ON s.customer_id = c.customer_id
      WHERE s.expiry = DATE(NOW())');

    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) > 0) {
        foreach ($result as $row) {

            // select only those invoices which company is liable to pay
            $query2 = $db->prepare('SELECT * FROM flight_purchases fp 
              INNER JOIN flight_credits fc ON fp.id = fc.flight_purchase_id
              INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
              WHERE fp.invoice_id = ? AND fc.minutes > 0 AND fc.expired_on IS NULL');
            $query2->execute([$row['invoice_number']]);

            $result2 = $query2->fetchAll(PDO::FETCH_ASSOC);
            $flight_purchase_ids = [];
            foreach($result2 as $row2) {
                $flight_purchase_ids[] = $row2['flight_purchase_id'];
            }

            if(count($flight_purchase_ids) > 0) {
                $query3 = $db->prepare('UPDATE flight_credits SET expired_on = DATE(NOW()) WHERE flight_purchase_id IN (?)');
                $str = implode(',', $flight_purchase_ids);
                $query3->execute([$str]);
                echo sprintf("Flight purchase ids: (%s) marked as expired\n",  $str);
            }
        }

    } else {
        echo 'No expiring flight offers found';
    }

} else {
    echo 'This script can only be ran from command line';
}