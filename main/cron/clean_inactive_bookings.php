<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18/02/2018
 * Time: 12:54 PM
 */

require_once dirname(dirname(__DIR__)).'/connect.php';

if(php_sapi_name() === 'cli') {

    $query = $db->prepare('SELECT id
      FROM flight_purchases fp
      WHERE status = 0 AND created < (NOW() - INTERVAL 30 MINUTE)');
    $query->execute();
    $result = $query->fetchAll();

    if (count($result) > 0) {
        foreach ($result as $row) {
            echo 'Deleting flight purchase ' . $row['id'] . "\n";
            deleteFlightPurchase($row['id']);
        }
    }

} else {
    echo 'This script can only be ran from command line';
}