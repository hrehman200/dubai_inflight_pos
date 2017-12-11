<?php
include_once('../connect.php');

$invoice             = $_GET['invoice'];
$firstPaymentOption  = $_GET['payfirst'];
$secondPaymentOption = $_GET['paysecond'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>
    </title>
    <link href="css/bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <script src="lib/jquery.js" type="text/javascript"></script>
    <?php
    $modeOfPayment = "";

    $result = $db->prepare("SELECT * FROM sales WHERE invoice_number= :userid");
    $result->bindParam(':userid', $invoice);
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

        $firstPaymentOption  = $row['mop_amount'];
        $secondPaymentOption = $row['mop1_amount'];

    }

    ?>
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

    ?>

    <style type="text/css" media="print">
        @page {
            size: 3in 6in;
            margin: 4mm;
        }

        html, body {
            padding: 0;
            margin: 0;
            width:76.2mm;
            height:152.4mm;
        }

        #footer {
            position: absolute;
            bottom: 0;
            font-size:9px;
            line-height: 1em;
            text-align: center;
            width:76.2mm;
        }
    </style>

</head>
<body>


<div class="container-fluid">
    <div class="row-fluid">

        <div class="span12" align="center">
            <img src="img/inflight_logo.png" width="180" style="margin-left:35px;"/>


            <?php
            $resulta = $db->prepare("SELECT * FROM customer WHERE customer_name= :a");
            $resulta->bindParam(':a', $cname);
            $resulta->execute();
            for ($i = 0; $rowa = $resulta->fetch(); $i++) {
                $address = $rowa['address'];
                $contact = $rowa['contact'];
            }
            ?>

            <table cellpadding="0" cellspacing="0"
                   style="font-family: arial; font-size: 12px;	text-align:left;" width="100%">
                <tbody>

                    <tr>
                        <td colspan="3" ><?=date("M j, Y")?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding-bottom:5px;"><?=$invoice?></td>
                    </tr>

                <?php
                $invoice_id = $_GET['invoice'];

                $result = $db->prepare("SELECT fp.id AS flight_purchase_id, fp.deduct_from_balance, fp.class_people, fo.code, fpkg.package_name, fo.offer_name, fo.price, fo.duration,
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
                            $current_price = $row['price'] + (CLASS_SESSION_COST * $row['class_people']);
                        } else {
                            $current_price = $row['price'];
                        }
                        $total_duration += $row['duration'];
                    }
                    ?>
                    <tr class="record">
                        <td><?php echo $row['deduct_from_balance'] == 1 ? $row['offer_name'] . ' (Deduct from balance)' : $row['offer_name']; ?></td>
                        <td>
                            <?php

                            $discount_percent = $row['discount'];
                            $discount_amount  = $discount_percent * $current_price / 100;
                            $current_price    -= $discount_amount;
                            //echo sprintf("-%.2f (%.1f%%)", $discount_amount, $discount_percent);
                            $total_cost += $current_price;

                            if ($row['deduct_from_balance'] == 1) {
                                echo '-';
                            } else {
                                echo number_format($current_price, 2);
                            }
                            ?></td>
                    </tr>

                    <?php
                }
                ?>
                <tr>
                    <td style="text-align: left;"><b>Total:</b></td>
                    <td><b><?php
                            echo number_format($total_cost, 2);
                            ?></b></td>
                </tr>

                <?php
                if ($modeOfPayment != -1) {
                    ?>
                    <tr>
                        <td style="text-align: left;"><?php
                            echo $modeOfPayment;
                            ?></td>
                        <td><?php
                            echo number_format($firstPaymentOption, 2);
                            ?></td>
                    </tr>
                    <?php
                }
                ?>

                <?php
                if ($modeOfPayment1 != -1) {
                    ?>
                    <tr>
                        <td style="text-align: left;"><?php
                            echo $modeOfPayment1;
                            ?></td>
                        <td><?php
                            echo number_format($secondPaymentOption, 2);
                            ?></td>
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
                    <td style="text-align: left;">Change:</td>
                    <td><?php echo number_format($sales_row['changeVal']); ?></td>
                </tr>

                </tbody>
            </table>

        </div>
    </div>
</div>

<div id="footer">
    THANKS FOR YOUR PURCHASES<br>
    WWW.INFLIGHTDUBAI.COM
</div>

</body>

</html>


<script type="text/javascript">
    $(function () {
        window.print();
    });
</script>