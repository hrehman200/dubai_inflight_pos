<?php

error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR);
session_start();

require_once (__DIR__. '/config.php');

/* Database config */
$db_host     = 'localhost';
$db_user     = 'root';
$db_pass     = '';
$db_database = 'sales';

/* End config */

$db = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_database, $db_user, $db_pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


require('flight_func.php');

$called_from = basename($_SERVER['PHP_SELF']);

/*if (!in_array($called_from, [
        'api.php',
        'connect.php',
        'flight_picker.php',
        'flight_preview.php',
        'checkout.php',
        'delete_flight_order.php',
        'save_flight_order.php',
        'savesales.php']
)
) {

    // cleanup
    $query = $db->prepare('SELECT s.invoice_number AS invoice_number, fp.id
      FROM flight_purchases fp
      LEFT JOIN sales s ON fp.invoice_id = s.invoice_number');
    $query->execute();
    $result = $query->fetchAll();

    if (count($result) > 0) {

        $result = array_filter($result, function ($v) {
            return $v['invoice_number'] == null;
        });

        $flight_purchase_ids = array_map(function ($v) {
            return $v['id'];
        }, $result);

        foreach($flight_purchase_ids as $flight_purchase_id) {
            deleteFlightPurchase($flight_purchase_id);
        }
    }

}*/

?>