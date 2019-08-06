<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 09/08/2018
 * Time: 12:54 PM
 */

require_once dirname(dirname(__DIR__)) . '/connect.php';

function saveRnLRow($start_date, $package, $parent_package, $paid, $total_minutes, $minutes_used, $aed_value, $avg_per_min) {
    global $db;
    $query = $db->prepare('INSERT INTO rnl_cache VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)');
    $query->execute([$start_date, $package, $parent_package, (float)$paid, (int)$total_minutes, (int)$minutes_used, (float)$aed_value, (float)$avg_per_min]);
}

$start_date = new DateTime();
$end_date = new DateTime();
$ftf_discounts = getFTFDiscounts();

while ($start_date <= $end_date) {

    // we will be saving each row for each day
    $start_end = $start_date->format('Y-m-d');

    $arr_revenue = [];

    $arr_ftf = getFTFRevenue($start_end, $start_end, false, false);

    foreach ($arr_ftf as $item) {
        if(in_array($item['package_name'], $ftf_discounts)) {
            $discount_name = $item['package_name'];
            saveRnLRow($start_end, $discount_name, 'FTF', $item['paid'], $item['total_minutes'], $item['minutes_used'], $item['aed_value'], $item['avg_per_min']);
            foreach($item[$discount_name] as $discount_item) {
                saveRnLRow($start_end, $discount_item['package_name'], $discount_name, $discount_item['paid'], $discount_item['total_minutes'], $discount_item['minutes_used'], $discount_item['aed_value'], $discount_item['avg_per_min']);
            }
        } else {
            saveRnLRow($start_end, $item['package_name'], null, $item['paid'], $item['total_minutes'], $item['minutes_used'], $item['aed_value'], $item['avg_per_min']);
        }
    }

    /** UP-Sale */
    $arr = getDataAndAggregate('UP-Sale', $start_end, $start_end);
    saveRnLRow($start_end, 'UP-Sale', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    /** RF */
    $arr = getDataAndAggregate('RF - Repeat Flights', $start_end, $start_end);
    saveRnLRow($start_end, 'RF - Repeat Flights', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    /** SKYDIVERS */
    $arr = getDataAndAggregate('Skydivers', $start_end, $start_end);
    saveRnLRow($start_end, 'Skydivers', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    /** Military */
    $arr = getDataAndAggregate('Military', $start_end, $start_end);
    saveRnLRow($start_end, 'Military Individuals', 'Military', $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    /** Navy Seal */
    $arr = getDataAndAggregate('Navy Seal', $start_end, $start_end);
    saveRnLRow($start_end, 'Navy Seal', 'Military', $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    /** Presidential Guard */
    $arr = getDataAndAggregate('Presidential Guard', $start_end, $start_end);
    saveRnLRow($start_end, 'Presidential Guard', 'Military', $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    /** Sky god */
    $arr = getDataAndAggregate('Sky god%', $start_end, $start_end);
    saveRnLRow($start_end, 'US Navy', 'Military', $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    /** HELMET RENT */
    $arr = getMerchandiseRevenue('Helmet Rent', $start_end, $start_end);
    saveRnLRow($start_end, 'Helmet Rent', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

    /** VIDEO */
    $arr = getMerchandiseRevenue('Video', $start_end, $start_end);
    saveRnLRow($start_end, 'Videos/Photos', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

    /** MERCHANDISE */
    $arr = getMerchandiseRevenue(TYPE_MERCHANDISE, $start_end, $start_end);
    saveRnLRow($start_end, TYPE_MERCHANDISE, 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

    /** OTHER e.g. Facility Rental, Sandstorm Registration Fee  */
    $arr = getOtherRevenue('Other', $start_end, $start_end);
    saveRnLRow($start_end, 'Other', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

    $start_date->modify('+1 day');

}