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
    <style type="text/css" media="print">
        @page {
            size: 3in 6in;
            margin: 10px;
        }

        html, body {
            padding: 0;
            margin: 0;
            width:76.2mm;
            height:auto;
            page-break-after: avoid;
            page-break-before: avoid;
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
    <?php

    error_reporting(0);

    $invoice   = $_GET['invoice'];
    $sale_type = $_GET['sale_type'];

    //&payfirst=$cash&paysecond=$remaining_cash

    $firstPaymentOption  = $_GET['payfirst'];
    $secondPaymentOption = $_GET['paysecond'];

    include_once('../connect.php');
    $result = $db->prepare("SELECT * FROM sales WHERE invoice_number= :userid");
    $result->bindParam(':userid', $invoice);
    $result->execute();
    for ($i = 0; $row = $result->fetch(); $i++) {
        $cname   = $row['name'];
        $invoice = $row['invoice_number'];
        $date    = $row['date'];
        $cash    = $row['due_date'];
        $cashier = $row['cashier'];

        $pt = $row['type'];
        $am = $row['amount'];
        if ($pt == 'cash') {
            $cash   = $row['due_date'];
            $amount = $cash - $am;
        }

        $modeOfPayment  = $row['mode_of_payment'];
        $modeOfPayment1 = $row['mode_of_payment_1'];

        $firstPaymentOption  = $row['mop_amount'];
        $secondPaymentOption = $row['mop1_amount'];
    }
    ?>
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
                   style="font-family: arial; font-size: 12px;	text-align:left; width:100%;">

                <tbody>
                    <tr>
                        <td colspan="3" style="padding-bottom:5px;"><?=date("M j, Y")?></td>
                    </tr>

                <?php
                $id     = $_GET['invoice'];
                $result = $db->prepare("SELECT so.*, vc.vat_code, vc.percent 
                              FROM sales_order so
                              LEFT JOIN vat_codes vc ON so.vat_code_id = vc.id
                              WHERE invoice= :userid");
                $result->bindParam(':userid', $id);
                $result->execute();

                $total_amount = 0;
                for ($i = 0; $row = $result->fetch(); $i++) {
                    ?>
                    <tr class="record">
                        <td><?php echo $row['name']; ?></td>
                        <td align="left">x<?php echo $row['qty']; ?></td>

                        <?php
                        $discount_percent = $row['discount'];
                        $discount_amount  = $discount_percent * $row['amount'] / 100;
                        $ddd              = $row['discount'];
                        ?>

                        <td align="right">
                            <?php
                            $row['amount'] -= ($discount_amount * $row['qty']);
                            $total_amount  += $row['amount'];
                            echo number_format($row['amount'], 2);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>

                <tr>
                    <td colspan="2"><strong
                        >Total:</strong> &nbsp;
                    </td>
                    <td align="right"><strong>
                            <?= number_format($total_amount, 2) ?>
                        </strong></td>
                </tr>


                <tr>
                    <td colspan="2"><?php echo $modeOfPayment; ?>:
                    </td>
                    <td align="right">
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


