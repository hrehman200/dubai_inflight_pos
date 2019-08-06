<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 17/04/2019
 * Time: 09:54 AM
 */

require_once dirname(dirname(__DIR__)).'/connect.php';

if(php_sapi_name() === 'cli') {

    $query = $db->prepare('SELECT * FROM eod_report_queue ORDER BY id DESC LIMIT 1');
    $query->execute();
    $user = $query->fetch();

    if(isset($user['user_id'])) {

        $_GET['user_id'] = $user['user_id'];

        ob_start();
        include dirname(__DIR__) . '/partials/collect_meraas.php';
        $table = ob_get_clean();

        $subject = 'Verified Sales Report for ' . date('jS F, Y');
        $body = $table;
        sendEmail('carlos.euribe@inflightdubai.com', $subject, $body, false, $user['email']);
        sendEmail('shah@inflightdubai.com', $subject, $body, false, $user['email']);

        $query = $db->prepare('DELETE FROM eod_report_queue WHERE 1');
        $query->execute();

        recordCustomerMonthlyLiability();
        sendFlightExpiryReminder();
        markPurchasesExpired();
    }

    require_once __DIR__ . '/save_rnl_per_day.php';

} else {
    echo 'This script can only be ran from command line';
}