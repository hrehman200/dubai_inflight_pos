<?php

require_once dirname(dirname(__DIR__)) . '/connect.php';

function insertRnLRow($start_date, $package, $parent_package, $paid, $total_minutes, $minutes_used, $aed_value, $avg_per_min) {
    global $db;
    $query = $db->prepare('INSERT INTO rnl_cache VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)');
    $query->execute([$start_date, $package, $parent_package, (float)$paid, (int)$total_minutes, (int)$minutes_used, (float)$aed_value, (float)$avg_per_min]);
}

function saveRnLRow($package, $date, $paid){
    global $db;

    $query = $db->prepare('SELECT * FROM rnl_cache WHERE package = ? AND date = ?');
    $query->execute([$package, $date]);
    if($query->num_rows > 0) {
        $query = $db->prepare('UPDATE rnl_cache SET paid=?, aed_value=? WHERE package = ? AND date = ?');
        $query->execute([(float)$paid, (float)$paid, $package, $date]);
    } else {
        insertRnLRow($date, $package, 'Retail Revenue', $paid, 0, 0, $paid, 0);
    }
}

$start_date = new DateTime('2019-08-01');
$end_date = new DateTime('2019-08-31');

while ($start_date <= $end_date) {

    $start_end = $start_date->format('Y-m-d');

    $arr = getMerchandiseRevenue('Helmet Rent', $start_end, $start_end);
    saveRnLRow('Helmet Rent', $start_end, $arr[0]['paid']);

    $arr = getMerchandiseRevenue('Video', $start_end, $start_end);
    saveRnLRow('Videos/Photos', $start_end, $arr[0]['paid']);

    $arr = getMerchandiseRevenue(TYPE_MERCHANDISE, $start_end, $start_end);
    saveRnLRow(TYPE_MERCHANDISE, $start_end, $arr[0]['paid']);

    $arr = getOtherRevenue('Other', $start_end, $start_end);
    saveRnLRow('Other', $start_end, $arr[0]['paid']);

    $start_date->modify('+1 day');
}

echo '----- DONE ------';