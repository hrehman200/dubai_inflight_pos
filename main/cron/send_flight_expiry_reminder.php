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
      WHERE s.expiry = DATE(NOW() + INTERVAL 30 DAY)');

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
            $flight_offers = [];
            foreach($result2 as $row2) {
                $flight_offers[] = $row2['flight_purchase_id'] .' - '. $row2['offer_name'];
            }

            if(count($flight_offers) > 0) {
                $body = sprintf('<div>
                    <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
                    <p>Hi <b>' . $row['customer_name'] . '</b>:, </p>
                    <p>This is to notify you that the following flight offers you purchased on <b>%s</b> against invoice no: <b>%s</b> will be expiring within a month.
                        <br><br>
                        %s
                        <br><br>
                        Kindly utilize the flight offers or send an email to <b>info@inflightdubai.com</b> for more information. After 30 days from now, these purchases will be expired.
                    </p>
                </div>', $row['date'], $row['invoice_number'], implode("<br>", $flight_offers));

                echo 'Sending email to '.$row['customer_name'].' for invoice '.$row['invoice_number']."\n";
                sendEmail($row['email'], 'Expiration of Purchased Offers', $body);
            }

        }
    } else {
        echo 'No expiring flight offers found';
    }

} else {
    echo 'This script can only be ran from command line';
}