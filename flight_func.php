<?php
include 'connect.php';

function getBookingsFromInvoice($invoice_id) {

    global $db;

    $result = $db->prepare("SELECT * FROM sales_order WHERE invoice= :invoiceId"); // GROUP BY invoice, flight_offer_id
    $result->bindParam(':invoiceId', $invoice_id);
    $result->execute();

    $data = [];
    while($row = $result->fetch()) {

        if(!isset($data[$row['']])) {

        }

    }



}