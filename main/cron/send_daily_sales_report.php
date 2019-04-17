<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 17/04/2019
 * Time: 09:54 AM
 */

require_once dirname(dirname(__DIR__)).'/connect.php';

if(php_sapi_name() === 'cli') {

    ob_start();
    include dirname(__DIR__) . '/partials/collect_meraas.php';
    $table = ob_get_clean();

    $subject = 'Verified Sales Report for ' . date('jS F, Y');
    $body = $table;
    sendEmail('carlos.euribe@inflightdubai.com', $subject, $body);
    sendEmail('shah@inflightdubai.com', $subject, $body);

} else {
    echo 'This script can only be ran from command line';
}