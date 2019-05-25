<?php
$d1     = $_GET['d1'];
$d2     = $_GET['d2'];

if(strlen($d1) > 0 && $d1 != 0) {
    if(strpos($d1, '/') !== false) {
        $dt = DateTime::createFromFormat('m/d/Y', $d1);
    } else {
        $dt = DateTime::createFromFormat('Y-m-d', $d1);
    }
} else {
    $dt = new DateTime('today');
}
$d1 = $dt->format('Y-m-d');

if(strlen($d2) > 0 && $d2 != 0) {
    if(strpos($d2, '/') !== false) {
        $dt = DateTime::createFromFormat('m/d/Y', $d2);
    } else {
        $dt = DateTime::createFromFormat('Y-m-d', $d2);
    }
} else {
    $dt = new DateTime('today');
}
$d2 = $dt->format('Y-m-d');
?>
<table class="table table-bordered table-striped" id="tblSalesReport" border="1" style="text-align: left; border-collapse: collapse;">
    <thead>
    <tr>
        <th colspan="13" style="text-align: center;">
            <h4>End of Day Report from&nbsp;<?php echo date('M j, Y', strtotime($d1)) ?>&nbsp;to&nbsp;<?php echo date('M j, Y', strtotime($d2)) ?></h4>
            <?php
            if(isset($_GET['verified']) || isset($_GET['user_id'])) {
                $user = getUserById($_GET['user_id']);
                ?>
                <h4>Verified by: <?=isset($_SESSION['SESS_FIRST_NAME']) ? $_SESSION['SESS_FIRST_NAME'] : $user['name']?></h4>
                <?php
            }
            ?>
        </th>
    </tr>
    <tr>
        <th width="16%"> Invoice Number</th>
        <th width="16%"> Operator</th>
        <th width="13%"> Transaction Date</th>
        <th width="13%"> Cash</th>
        <th width="13%"> Card</th>
        <th width="13%"> Online</th>
        <th width="13%"> Souq</th>
        <th width="13%"> Customer ID</th>
        <th width="20%"> Customer Name</th>
        <th width="20%"> Service</th>
        <th width="20%"> Merchandise</th>
        <th width="18%"> VAT</th>
        <th width="18%"> Net of VAT</th>

    </tr>
    </thead>
    <tbody>

    <?php

    $sql = "SELECT s.*, c.customer_name FROM sales s
                        LEFT JOIN customer c ON s.customer_id = c.customer_id
                        LEFT JOIN flight_purchases fp ON s.invoice_number = fp.invoice_id";

    if($_SESSION['SESS_LAST_NAME'] == 'Operator') {
        $sql .= " INNER JOIN user u ON s.cashier = u.name AND u.position = 'Operator' ";
    }

    $sql .= " WHERE date >= :a AND date <= :b 
                AND 
                (fp.created IS NULL || TIME(fp.created) <= '18:00:00')"; // either fp is null OR its created date is before office timings

    if($_SESSION['SESS_LAST_NAME'] == 'Operator') {
        $sql .= sprintf(" AND u.name = '%s'", $_SESSION['SESS_FIRST_NAME']);
    }
    $sql .= ' AND s.amount > 0 AND 
                        (s.mode_of_payment !="Account" AND s.mode_of_payment_1 != "Account" AND s.mode_of_payment !="credit_time" AND s.mode_of_payment_1 != "credit_time") ';
    $sql .= " AND (c.customer_name != 'FDR' OR c.customer_name IS NULL)
                    GROUP BY IFNULL(fp.invoice_id, s.invoice_number)
                    ORDER by transaction_id DESC";

    $result = $db->prepare($sql);
    $result->bindParam(':a', $d1);
    $result->bindParam(':b', $d2);
    $result->execute();
    $total_sale   = 0;
    $total_profit = 0;
    $total_cash   = 0;
    $total_card   = 0;
    $total_account = 0;
    $total_online = 0;
    $total_souq = 0;

    for ($i = 0; $row = $result->fetch(PDO::FETCH_ASSOC); $i++) {
        $current_cost = round($row['amount'], 0);
        $discount = $current_cost * $row['discount'] / 100.00;

        $query = $db->prepare('SELECT SUM(amount) AS totalService,
                        SUM(amount) - (SUM(amount) * SUM(discount) / 100) AS discountedService
                        FROM sales_order WHERE invoice = ? AND gen_name = "Service" LIMIT 1');
        $query->execute([$row['invoice_number']]);
        $row2 = $query->fetch();
        $is_service_merchandise = ($row2['discountedService'] > 0);

        $query = $db->prepare('SELECT SUM(amount) AS totalMerchandise,
                        SUM(amount) - (SUM(amount) * SUM(discount) / 100) AS discountedMerchandise
                        FROM sales_order WHERE invoice = ? AND gen_name = "Merchandise" LIMIT 1');
        $query->execute([$row['invoice_number']]);
        $row3 = $query->fetch();
        $is_merchandise = ($row3['discountedMerchandise'] > 0);

        $is_only_service = (!$is_merchandise && !$is_service_merchandise);

        if ($is_only_service) {
            $invoiceHref = 'flight_preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'];

        } else {
            $invoiceHref = 'preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'] . '&payfirst=&paysecond=&d1=' . $d1 . '&d2=' . $d2;
        }
        $total_sale += $current_cost;
        $total_profit += $row['profit'];

        if($row['mode_of_payment'] == 'Cash') {
            $total_cash += $row['mop_amount'];
        }
        if($row['mode_of_payment_1'] == 'Cash') {
            $total_cash += $row['mop1_amount'];
        }

        if($row['mode_of_payment'] == 'Card') {
            $total_card += $row['mop_amount'];
        }
        if($row['mode_of_payment_1'] == 'Card') {
            $total_card += $row['mop1_amount'];
        }

        if($row['mode_of_payment'] == 'Account') {
            $total_account += $row['mop_amount'];
        }
        if($row['mode_of_payment_1'] == 'Account') {
            $total_account += $row['mop1_amount'];
        }

        if($row['mode_of_payment'] == 'Online') {
            $total_online += $row['mop_amount'];
        }
        if($row['mode_of_payment_1'] == 'Online') {
            $total_online += $row['mop1_amount'];
        }
        if($row['mode_of_payment'] == 'Souq') {
            $total_souq += $row['mop_amount'];
        }
        if($row['mode_of_payment_1'] == 'Souq') {
            $total_souq += $row['mop1_amount'];
        }

        ?>
        <tr>
            <td><a href='<?php echo $invoiceHref ?>'> <?php echo $row['invoice_number']; ?></td>
            <td><?php echo $row['cashier']; ?></td>
            <td><?php echo $row['date']; ?></td>
            <td><?php
                if ($row['mode_of_payment']=='Cash') echo $row['mop_amount'];
                if ($row['mode_of_payment_1']=='Cash') echo $row['mop1_amount'];?></td>
            <td><?php
                if ($row['mode_of_payment']=='Card') echo $row['mop_amount'];
                if ($row['mode_of_payment_1']=='Card') echo $row['mop1_amount'];?></td>
            <td><?php
                if ($row['mode_of_payment']=='Online') echo $row['mop_amount'];
                if ($row['mode_of_payment_1']=='Online') echo $row['mop1_amount'];?></td>
            <td><?php
                if ($row['mode_of_payment']=='Souq') echo $row['mop_amount'];
                if ($row['mode_of_payment_1']=='Souq') echo $row['mop1_amount'];?></td>
            <td><?php
                echo ($row['customer_id']) ? $row['customer_id'] : $row['customer_id']; ?></td>
            <td><?php
                echo ($row['customer_name']) ? $row['customer_name'] : $row['name']; ?></td>
            <td><?php
                if($is_only_service) {
                    echo number_format(($row['mop_amount'] + $row['mop1_amount']), 0);

                } else if($is_merchandise || $is_service_merchandise) {
                    echo number_format(round($row2['discountedService'],2), 0);
                }
                ?></td>
            <td><?php
                if ($is_merchandise || $is_service_merchandise) {
                    echo number_format(round($row3['discountedMerchandise'], 2), 0);
                }
                ?></td>

            <td>
                <?php
                $vat_percent = "5%";
                $VAT = $row['amount'] *$vat_percent/105;
                //$vat_amount  = $vat_percent * $current_amount_w_discount / 105;
                echo number_format($VAT, 0);
                ?></td>

            <td><?= number_format($current_cost-$VAT, 0); ?></td>

        </tr>
        <?php

    }
    ?>

    <tr>
        <td colspan="9" style="text-align: right;"> <b>Total:</b></td>
        <td colspan="1" style=""><b><?= number_format($total_sale, 0) ?></b></td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: right;"> <b>Cash:</b></td>
        <td colspan="1" style=""><b><?= number_format($total_cash, 0) ?></b></td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: right;"> <b>Card:</b></td>
        <td colspan="1" style=""><b><?= number_format($total_card, 0) ?></b></td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: right;"> <b>Souq:</b></td>
        <td colspan="1" style=""><b><?= $total_souq > 0 ? number_format($total_souq, 0) : 'No Sale' ?></b></td>
        <td colspan="3"></td>
    </tr>

    <?php
    if(strtolower($_SESSION['SESS_LAST_NAME']) == 'admin' ||
        strtolower($_SESSION['SESS_LAST_NAME']) == ROLE_ACCOUNT ||
        $_SESSION[SESS_MOCK_ROLE] == ROLE_ACCOUNT ||
        php_sapi_name() == 'cli') {
        ?>
        <tr>
            <td colspan="9" style="text-align: right;"><b>Online:</b></td>
            <td colspan="1" style=""><b><?= number_format($total_online, 0) ?></b></td>
            <td colspan="3"></td>
        </tr>
        <?php
    }
    ?>

    <?php
    if($_SESSION['SESS_LAST_NAME'] == 'Operator' || $_SESSION[SESS_MOCK_ROLE] == ROLE_OPERATOR) {
        ?>
        <tr>
            <td colspan="9" style="text-align: right;"><b>Operator:</b></td>
            <td colspan="1" style=""><b><?= $_SESSION['SESS_FIRST_NAME'] ?></b></td>
            <td colspan="3"></td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td colspan="9" style="text-align: right;"><b>Verified By:</b></td>
        <td colspan="1" style="padding-top:10px;">
            <?php
            if(isset($user)) {
                echo $user['name'];
            }
            ?>
            _______________________
        </td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: right;"><b>Signature:</b></td>
        <td colspan="1" style="padding-top:50px;">
            <?php
            if(isset($user)) {
                echo sprintf('<img src="%s" />', BASE_URL.'/main/uploads/'.$user['sign_img']);
            }
            ?>__________________
        </td>
        <td colspan="3"></td>
    </tr>

    </tbody>

</table>


<?php
$sql = "SELECT s.*, c.customer_name FROM sales s
                        LEFT JOIN customer c ON s.customer_id = c.customer_id
                        INNER JOIN flight_purchases fp ON s.invoice_number = fp.invoice_id";

if($_SESSION['SESS_LAST_NAME'] == 'Operator') {
    $sql .= " INNER JOIN user u ON s.cashier = u.name AND u.position = 'Operator' ";
}

$sql .= " WHERE date >= ? AND date <= ? AND fp.created >= ? AND fp.created <= ?";

if($_SESSION['SESS_LAST_NAME'] == 'Operator') {
    $sql .= sprintf(" AND u.name = '%s'", $_SESSION['SESS_FIRST_NAME']);
}
$sql .= ' AND s.amount > 0 AND 
                        (s.mode_of_payment = "Online") ';
$sql .= " AND (c.customer_name != 'FDR' OR c.customer_name IS NULL)
                    GROUP BY fp.invoice_id
                    ORDER by transaction_id DESC";

$query = $db->prepare($sql);
$query->execute([$d1, $d2, $d1.' 18:00:00', $d1.' 23:59:59']);
$rows = $query->fetchAll(PDO::FETCH_ASSOC);
if(count($rows) == 0) {
    exit;
}
?>

<hr style="border-color:black;">

<table class="table table-bordered table-striped" id="tblSalesReport" border="1" style="text-align: left; border-collapse: collapse;">
    <thead>
    <tr>
        <th colspan="13" style="text-align: center;">
            <h4>Additional Sale after EOD</h4>
        </th>
    </tr>
    <tr>
        <th width="16%"> Invoice Number</th>
        <th width="16%"> Operator</th>
        <th width="13%"> Transaction Date</th>
        <th width="13%"> Cash</th>
        <th width="13%"> Card</th>
        <th width="13%"> Online</th>
        <th width="13%"> Souq</th>
        <th width="13%"> Customer ID</th>
        <th width="20%"> Customer Name</th>
        <th width="20%"> Service</th>
        <th width="20%"> Merchandise</th>
        <th width="18%"> VAT</th>
        <th width="18%"> Net of VAT</th>

    </tr>
    </thead>
    <tbody>
    <?php foreach($rows as $row) {
        $current_cost = round($row['amount'], 0);
        $online_amount_after_office += $current_cost;
        $invoiceHref = 'flight_preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'];
        ?>
        <tr>
            <td><a href="<?=$invoiceHref?>"> <?=$row['invoice_number']?></a></td>
            <td><?=$row['cashier']?></td>
            <td><?=$row['date']?></td>
            <td></td>
            <td></td>
            <td><?=$row['mop_amount']?></td>
            <td></td>
            <td><?=$row['customer_id']?></td>
            <td><?=$row['customer_name']?></td>
            <td><?=number_format(($row['mop_amount'] + $row['mop1_amount']), 0)?></td>
            <td></td>
            <td>
                <?php
                $vat_percent = "5%";
                $VAT = $row['amount'] *$vat_percent/105;
                //$vat_amount  = $vat_percent * $current_amount_w_discount / 105;
                echo number_format($VAT, 0);
                ?></td>

            <td><?= number_format($current_cost-$VAT, 0) ?></td>

        </tr>
    <?php } ?>
    <tr>
        <td colspan="10"></td>
    </tr>
    <tr>
        <td><b>Grand Total: </b></td>
        <td><?=number_format($online_amount_after_office+$total_sale)?></td>
        <td colspan="7" style="text-align: right;"><b>Verified By:</b></td>
        <td colspan="1" style="padding-top:10px;">
            <?php
            $marija = getUserById(15);
            echo $marija['name'];
            ?>
            _______________________
        </td>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td><b>Print Date:</b></td>
        <td><?=date('Y-m-d')?></td>
        <td colspan="7" style="text-align: right;"><b>Signature:</b></td>
        <td colspan="1" style="padding-top:50px;">
            <?php
            echo sprintf('<img src="%s" />', BASE_URL.'/main/uploads/'.$marija['sign_img']);
            ?>__________________
        </td>
        <td colspan="3"></td>
    </tr>
    </tbody>
</table>