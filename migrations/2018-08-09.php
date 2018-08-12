<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 14/03/2018
 * Time: 12:43 PM
 */

include_once ('../connect.php');

if(($handle = fopen('./2018-08-09.csv', 'r')) !== FALSE) {
    set_time_limit(0);
    $row = 0;

    while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        if($row > 0) {  // ignore header
            $customer_name = $data[1];
            $per_minute_cost = $data[3];

            if($customer_name != '') {
                $query = $db->prepare(" SELECT customer_id, customer_name FROM customer WHERE customer_name LIKE ?");
                $query->execute(['%'.$customer_name.'%']);
                if ($query->rowCount() > 0) {
                    $customer = $query->fetch(PDO::FETCH_ASSOC);

                    for($i=6; $i<=14; $i++) {
                        $month = $i==6 ? 12 : ($i-6);
                        $month_name = getMonthNameFromIndex($month);
                        $year = $i==6 ? 2017 : 2018;
                        $minutes = $data[$i];
                        $amount = $data[$i]*$per_minute_cost;

                        saveCustomerMonthlyLiability($customer['customer_id'], $month_name, $year, 0, 0, $minutes, $amount);
                        echo "Saved {$amount} {$minutes} liability for <b>{$customer_name}</b> for month <b>{$month_name}-{$year}</b><br>";
                    }

                } else {
                    echo "No customer found for name <b>{$customer_name}</b><br>";
                }
            }
        }
        $row++;
    }
    fclose($handle);
}