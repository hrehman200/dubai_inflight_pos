<?php
$modeOfPayment = "";

$result = $db->prepare("SELECT * FROM sales WHERE invoice_number= :invoice_id");
$result->bindParam(':invoice_id', $invoice_id);
$result->execute();

//print($result);

$discount = 0;
for ($i = 0; $row = $result->fetch(); $i++) {
    $cname    = $row['name'];
    $invoice  = $row['invoice_number'];
    $date     = $row['date'];
    $cash     = $row['due_date'];
    $cashier  = $row['cashier'];
    $discount = $row['discount'];

    $pt          = $row['type'];
    $price       = $row['amount'];
    $cash_return = 0;
    if ($pt == 'cash') {
        $cash_return = $cash - $price;
    }

    $modeOfPayment  = $row['mode_of_payment'];
    $modeOfPayment1 = $row['mode_of_payment_1'];

    $payfirst  = $row['mop_amount'];
    $paysecond = $row['mop1_amount'];

}

?>

<table border="1" cellpadding="4" cellspacing="0"
       style="font-family: arial; font-size: 12px;	text-align:left;" width="100%">
    <thead>
    <tr>
        <th width="90"> Code</th>
        <th> Package</th>
        <th> Offer</th>
        <th> Price</th>
        <th> Discount</th>
        <th> VAT</th>
        <th> Minutes</th>
    </tr>
    </thead>
    <tbody>

    <?php
    $result = $db->prepare("SELECT fp.id AS flight_purchase_id, fp.deduct_from_balance, fp.class_people, fo.code, fpkg.package_name, fo.offer_name, fp.price, fo.duration,
                                      fp.discount, vc.percent
                                      FROM flight_purchases fp
                                      LEFT JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                                      LEFT JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                                      LEFT JOIN vat_codes vc ON fp.vat_code_id = vc.id
                                      WHERE fp.invoice_id= :invoiceId");
    $result->bindParam(':invoiceId', $invoice_id);
    $result->execute();

    $total_cost     = 0;
    $total_duration = 0;
    $current_price  = 0;
    while ($row = $result->fetch()) {
        if ($row['deduct_from_balance'] == 0) {
            if ($row['class_people'] > 0) {
                $current_price = (CLASS_SESSION_COST * $row['class_people']);
            } else {
                $current_price = $row['price'];
            }
            $total_duration += $row['duration'];
        }
        ?>
        <tr class="record">
            <td><?php echo $row['code']; ?></td>
            <td><?php echo $row['package_name']; ?></td>
            <td><?php echo $row['deduct_from_balance'] == 1 ? $row['offer_name'] . ' (Deduct from balance)' : $row['offer_name']; ?></td>
            <td>
                <?php
                if ($row['deduct_from_balance'] == 1) {
                    echo '-';
                } else {
                    echo number_format($current_price);
                }
                ?></td>
            <td>
                <?php
                $discount_percent = $row['discount'];
                $discount_amount = $discount_percent * $current_price / 100;
                $current_price_w_discount = $current_price - $discount_amount;
                echo sprintf("-%.2f (%.1f%%)", $discount_amount, $discount_percent);
                $total_cost += $current_price_w_discount;
                ?>
            </td>
            <td>
                <?php
                $vat_percent = $row['percent'];
                $vat_amount = $vat_percent * $current_price_w_discount / 105;
                echo sprintf("%.2f (%.1f%%)", $vat_amount, $vat_percent);
                ?>
            </td>
            <td><?php echo $row['deduct_from_balance'] == 1 ? '-' : $row['duration']; ?></td>
        </tr>

        <?php
        $query2 = $db->prepare('SELECT * FROM flight_bookings WHERE flight_purchase_id = :flight_purchase_id');
        $query2->bindParam(':flight_purchase_id', $row['flight_purchase_id']);
        $query2->execute();
        while ($row2 = $query2->fetch()) {
            ?>
            <tr>
                <td colspan="2"></td>
                <td style="text-align: center;"><?= substr($row2['flight_time'], 0, -3) ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?= $row2['duration'] ?></td>
            </tr>
            <?php
        }
        ?>

        <?php
    }
    ?>
    <tr>
        <td colspan="4" style="text-align: right;"><b>Total:</b></td>
        <td><b><?php
                echo number_format($total_cost, 2);
                ?></b></td>
        <td colspan="2"></td>
    </tr>

    <?php
    if ($modeOfPayment != -1) {
        ?>
        <tr>
            <td colspan="4" style="text-align: right;"><?php
                echo $modeOfPayment;
                ?>:</td>
            <td><?php
                echo number_format($payfirst, 2);
                ?></td>
            <td colspan="2"></td>
        </tr>
        <?php
    }
    ?>

    <?php
    if ($modeOfPayment1 != -1 && $modeOfPayment1 != '') {
        ?>
        <tr>
            <td colspan="4" style="text-align: right;"><?php
                echo $modeOfPayment1;
                ?></td>
            <td><?php
                echo number_format($paysecond, 2);
                ?></td>
            <td colspan="2"></td>
        </tr>
        <?php
    }
    ?>
    <?php
    $sales_query = $db->prepare("SELECT ((mop_amount+mop1_amount) - CEIL(after_dis)) AS changeVal
                            from sales WHERE invoice_number= :invoiceId");
    $sales_query->bindParam(':invoiceId', $invoice_id);
    $sales_query->execute();
    $sales_row = $sales_query->fetch();
    ?>
    <tr>
        <td colspan="4" style="text-align: right;">Change:</td>
        <td><?php echo number_format($sales_row['changeVal']); ?></td>
        <td colspan="2"></td>
    </tr>

    </tbody>
</table>