<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 09/08/2018
 * Time: 12:54 PM
 */

require_once dirname(dirname(__DIR__)) . '/connect.php';

$start_date = new DateTime('2018-01-01');
$end_date = new DateTime('2019-09-08');

$ftf_discounts = getDiscountsOf();
$military_discounts = getDiscountsOfParent('Military');

while ($start_date <= $end_date) {

    // we will be saving each row for each day
    $start_end = $start_date->format('Y-m-d');

    $arr_revenue = [];

    /*$arr_ftf = getFTFRevenue($start_end, $start_end, false, false);

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
    }*/

    /** UP-Sale */
    /*$arr = getDataAndAggregate('UP-Sale', $start_end, $start_end);
    saveRnLRow($start_end, 'UP-Sale', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);*/

    /** RF */
    /*$arr = getDataAndAggregate('RF - Repeat Flights', $start_end, $start_end);
    saveRnLRow($start_end, 'RF - Repeat Flights', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);*/

    /** SKYDIVERS */
//    $arr = getDataAndAggregate('Skydivers', $start_end, $start_end);
//    saveRnLRow($start_end, 'Skydivers', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

    foreach($military_discounts as $military_discount) {

        $military_pkg = '';
        switch($military_discount) {

            case 'Military':
                $military_pkg = 'Military'; // in db
                $military_discount = 'Military Individuals'; // how its shown in RnL
                break;

            case 'Navy Seal':
            case 'Navy Seal Zero %':
                $military_pkg = $military_discount = 'Navy Seal';
                break;

            case 'Sky god%':
                $military_pkg = 'Sky god%';
                $military_discount = 'US Navy';
                break;

            default:
                $military_pkg = $military_discount;

        }

        $arr = getDataAndAggregate($military_pkg, $start_end, $start_end);
        saveRnLRow($start_end, $military_discount, 'Military', $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);
    }

    /** HELMET RENT */
    /*$arr = getMerchandiseRevenue('Helmet Rent', $start_end, $start_end);
    saveRnLRow($start_end, 'Helmet Rent', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);*/

    /** VIDEO */
    /*$arr = getMerchandiseRevenue('Video', $start_end, $start_end);
    saveRnLRow($start_end, 'Videos/Photos', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);*/

    /** MERCHANDISE */
    /*$arr = getMerchandiseRevenue(TYPE_MERCHANDISE, $start_end, $start_end);
    saveRnLRow($start_end, TYPE_MERCHANDISE, 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);*/

    /** OTHER e.g. Facility Rental, Sandstorm Registration Fee  */
    /*$arr = getOtherRevenue('Other', $start_end, $start_end);
    saveRnLRow($start_end, 'Other', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);*/

    /** Class session */
    $arr = getClassSessionRevenue('Class Session', $start_end, $start_end);
    saveRnLRow($start_end, 'Class Session', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

    $start_date->modify('+1 day');

}