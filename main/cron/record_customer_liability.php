<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 09/08/2018
 * Time: 12:54 PM
 */

require_once dirname(dirname(__DIR__)) . '/connect.php';

set_time_limit(0);
recordCustomerMonthlyLiability(true);