<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18/02/2018
 * Time: 12:54 PM
 */

ini_set('max_execution_time', 1800);

require_once dirname(dirname(__DIR__)).'/connect.php';

markPurchasesExpired();
sendFlightExpiryReminder();
