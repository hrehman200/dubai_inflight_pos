<!DOCTYPE html>
<html>
<head>
    <title>

    </title>


    <script src="lib/jquery.js" type="text/javascript"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">
    <!--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.3.0/paper.css">-->

    <style type="text/css" media="print">
        @page {
            size: 76.2mm 152mm;
            margin: 4mm;
        }

        body, section {
            padding: 0;
            margin: 0;
            /*width: 76.2mm;
            height: 152mm;*/
            page-break-after: avoid;
            page-break-before: avoid;
        }

        table {
            position: relative;
        }

        #footer {
            position: absolute;
            bottom: 0;
            font-size: 9px;
            line-height: 1em;
            text-align: center;
            width: 76.2mm;
            height: 5mm;
        }
    </style>
    <?php

    error_reporting(0);

    $invoice = $_GET['invoice'];
    $sale_type = $_GET['sale_type'];

    //&payfirst=$cash&paysecond=$remaining_cash

    $firstPaymentOption = $_GET['payfirst'];
    $secondPaymentOption = $_GET['paysecond'];

    include_once('../connect.php');
    $result = $db->prepare("SELECT * FROM sales WHERE invoice_number= :userid");
    $result->bindParam(':userid', $invoice);
    $result->execute();
    for ($i = 0; $row = $result->fetch(); $i++) {
        $cname = $row['name'];
        $invoice = $row['invoice_number'];
        $date = $row['date'];
        $cash = $row['due_date'];
        $cashier = $row['cashier'];

        $pt = $row['type'];
        $am = $row['amount'];
        if ($pt == 'cash') {
            $cash = $row['due_date'];
            $amount = $cash - $am;
        }

        $modeOfPayment = $row['mode_of_payment'];
        $modeOfPayment1 = $row['mode_of_payment_1'];

        $firstPaymentOption = $row['mop_amount'];
        $secondPaymentOption = $row['mop1_amount'];
    }
    ?>
<body>

<section>

    <img src="img/inflight_logo.png" width="180" style="margin-left:25%;"/>

    <?php
    $resulta = $db->prepare("SELECT * FROM customer WHERE customer_name= :a");
    $resulta->bindParam(':a', $cname);
    $resulta->execute();
    for ($i = 0; $rowa = $resulta->fetch(); $i++) {
        $address = $rowa['address'];
        $contact = $rowa['contact'];
    }
    ?>
    <table cellpadding="3" cellspacing="0"
           style="font-family: arial; font-size: 12px;	text-align:left; width:100%;">
        <center>
            <div style="font:bold 15px 'Aleo';">Tax Invoice <br> Inflight Dubai LLC <br>Al Ain Road E66, Margham Desert<br>Next to skydive, Dubai, AE<BR>TRN:100225068400003</div>
        </center>
        <tbody>
        <Br>
        <tr>
            <Br><td colspan="3">Date :<?= date("M j, Y") ?></td>
        </tr>
        <tr>
            <td colspan="3" style="padding-bottom:5px;"> Doc# :<?= $invoice ?></td>
        </tr>

        <?php
        $id = $_GET['invoice'];
        $result = $db->prepare("SELECT so.*, vc.vat_code, vc.percent 
                              FROM sales_order so
                              LEFT JOIN vat_codes vc ON so.vat_code_id = vc.id
                              WHERE invoice= :userid");
        $result->bindParam(':userid', $id);
        $result->execute();

        $total_amount = 0;
        $total_vat_percent = 0;
        $total_vat_amount = 0;

        for ($i = 0; $row = $result->fetch(); $i++) {

            $discount_percent = $row['discount'];
            $discount_amount = $discount_percent * $row['amount'] / 100;
            $total_discount_amount += $discount_amount;
            $arr_discount_percent[] = $discount_percent;

            $vat_percent = $row['percent'];
            $current_amount_w_discount = $row['amount'] - $discount_amount;
            $total_amount += $current_amount_w_discount;
            $vat_amount  = $vat_percent * $current_amount_w_discount / 105;
            $total_vat_amount += $vat_amount;


            ?>
            <!--<tr>
            <td colspan="3"><u>Description </u></td>
             <td align="left">Qty</td>
             <td align="right">Amount<td>
        </tr>-->
            <tr class="record">
                <td><br><?php echo $row['name']; ?></td>
                <td align="left"><br>x<?php echo $row['qty']; ?></td>
                <td align="right"><br>
                    <?php
                    echo number_format($row['amount'], 2);
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td colspan="2"><br>
                Discount: &nbsp;
            </td>
            <td align="right"><br>
                <?php echo sprintf("-%.2f", $total_discount_amount); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><strong
                >Amount Payable(AED):</strong> &nbsp;
            </td>
            <td align="right"><b>
                    <?php
                    $total_amount -= $discount_amount;
                    echo number_format($total_amount, 2);
                    ?></b>
            </td>
        </tr>


        <tr>
            <td colspan="2"><br><br><?php echo $modeOfPayment; ?>:
            </td>
            <td align="right"><br><br>
                <?php

                function formatMoney($number, $fractional = false) {
                    if ($fractional) {
                        $number = sprintf('%.2f', $number);
                    }
                    while (true) {
                        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
                        if ($replaced != $number) {
                            $number = $replaced;
                        } else {
                            break;
                        }
                    }

                    return $number;
                }

                echo number_format($firstPaymentOption, 2);
                ?></td>
        </tr>

        <?php
        if ($modeOfPayment1 != '-1') {
            ?>
            <tr>
                <td colspan="2"><?php echo $modeOfPayment1; ?></td>
                <td align="right"><?php
                    echo number_format($secondPaymentOption, 2);
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td colspan="2">VAT (5%): &nbsp;
            </td>
            <td align="right">
                <?php
                echo sprintf("%.2f", $total_vat_amount);
                ?>
            </td>
        </tr>
        </tbody>
    </table>

    <div id="footer">
        THANKS FOR YOUR PURCHASES<br>
        WWW.INFLIGHTDUBAI.COM
        800-INFLIGHT (46354448)
    </div>

</section>


</body>

</html>


<script type="text/javascript">
    $(function () {
        window.print();
    });
</script>


