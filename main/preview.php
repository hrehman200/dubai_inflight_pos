<?php
$invoice   = $_GET['invoice'];
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('auth.php'); ?>
    <title>
        POS
    </title>
    <link href="css/bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style type="text/css">

        .sidebar-nav {
            padding: 9px 0;
        }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
    <script src="lib/jquery.js" type="text/javascript"></script>
    <script src="src/facebox.js" type="text/javascript"></script>
    <script language="javascript">
        function Clickheretoprint() {
            /*var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
             disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
             var content_vlue = document.getElementById("content").innerHTML;

             var docprint = window.open("", "", disp_setting);
             docprint.document.open();
             docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
             docprint.document.write(content_vlue);
             docprint.document.close();
             docprint.focus();*/

            $("<iframe>")
                .hide()
                .attr("src", "preview_print.php?invoice=<?=$invoice?>")
                .appendTo("body");

        }
    </script>
    <?php

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

    $result = $db->prepare('SELECT GROUP_CONCAT(vat_code) AS vat_code
      FROM vat_codes vc
      INNER JOIN sales_order so ON vc.id = so.vat_code_id
      WHERE so.invoice = ?');
    $result->execute(array($invoice));
    $row       = $result->fetch();
    $vat_codes = $row['vat_code'];
    ?>
    <?php
    $finalcode = 'RS-' . createRandomPassword();
    ?>


    <script language="javascript" type="text/javascript">
        /* Visit http://www.yaldex.com/ for full source code
         and get more free JavaScript, CSS and DHTML scripts! */
        var timerID      = null;
        var timerRunning = false;
        function stopclock() {
            if (timerRunning)
                clearTimeout(timerID);
            timerRunning = false;
        }
        function showtime() {
            var now       = new Date();
            var hours     = now.getHours();
            var minutes   = now.getMinutes();
            var seconds   = now.getSeconds()
            var timeValue = "" + ((hours > 12) ? hours - 12 : hours)
            if (timeValue == "0") timeValue = 12;
            timeValue += ((minutes < 10) ? ":0" : ":") + minutes
            timeValue += ((seconds < 10) ? ":0" : ":") + seconds
            timeValue += (hours >= 12) ? " P.M." : " A.M."
            document.clock.face.value = timeValue;
            timerID                   = setTimeout("showtime()", 1000);
            timerRunning              = true;
        }
        function startclock() {
            stopclock();
            showtime();
        }
        window.onload = startclock;
    </SCRIPT>
<body>

<?php include('navfixed.php'); ?>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span2">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">

                    <?php
                    include "side-menu.php";
                    ?>
                    <br><br><br>
                    <li>
                        <div class="hero-unit-clock">

                            <form name="clock">
                                <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="text"
                                                                                  class="trans" name="face" value=""
                                                                                  disabled>
                            </form>
                        </div>
                    </li>

                </ul>
            </div><!--/.well -->
        </div><!--/span-->

        <div class="span10">

            <?php

            if ($_GET['sale_type'] != '') {
                # code...
                echo '<a href="salesreport.php?d1=' . $_GET['d1'] . '&d2=' . $_GET['d2'] . '"><button class="btn btn-default"><i class="icon-arrow-left"></i> Back to Sales</button></a>';
            } else {
                echo '<a href="sales.php?id=cash&invoice=' . $finalcode . '"><button class="btn btn-default"><i class="icon-arrow-left"></i> Back to Merchandise</button></a>';
            }

            ?>


            <div class="content" id="content">
                <div style="margin: 0 auto; padding: 20px; width: 900px; font-weight: normal;">
                    <div style="width: 100%; height: 190px;">
                        <div style="width: 900px; float: left;">
                            <center>
                                <div style="font:bold 25px 'Aleo';">Sales Receipt</div>
                                Inflight Dubai <br>
                                Indoor SkyDiving <br> <br>
                            </center>
                            <div>
                                <?php
                                $resulta = $db->prepare("SELECT * FROM customer WHERE customer_name= :a");
                                $resulta->bindParam(':a', $cname);
                                $resulta->execute();
                                for ($i = 0; $rowa = $resulta->fetch(); $i++) {
                                    $address = $rowa['address'];
                                    $contact = $rowa['contact'];
                                }
                                ?>
                            </div>
                        </div>
                        <div style="width: 200px; float: left; margin-bottom:20px;">
                            <table cellpadding="3" cellspacing="0"
                                   style="font-family: arial; font-size: 12px;text-align:left;width : 100%;">

                                <tr>
                                    <td>OR No. :</td>
                                    <td><?php echo $invoice ?></td>
                                </tr>
                                <tr>
                                    <td>Date :</td>
                                    <td><?php echo $date ?></td>
                                </tr>
                                <tr>
                                    <td>Vat Code:</td>
                                    <td><?= $vat_codes ?></td>
                                </tr>
                            </table>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div style="width: 100%; margin-top:-70px;">
                        <table border="1" cellpadding="4" cellspacing="0"
                               style="font-family: arial; font-size: 12px;	text-align:left;" width="100%">
                            <thead>
                            <tr>
                                <th width="90"> Product Code</th>
                                <th> Product Name</th>
                                <th> Qty</th>
                                <th> Price</th>
                                <th> Discount</th>
                                <th> Amount</th>
                                <th> VAT</th>
                            </tr>
                            </thead>
                            <tbody>

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
                                    <td><?php echo $row['product_code']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['qty']; ?></td>
                                    <td>
                                        <?php
                                        $ppp = $row['price'];
                                        echo formatMoney($ppp, true);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $discount_percent = $row['discount'];
                                        $discount_amount  = $discount_percent * $row['amount'] / 100;
                                        $ddd              = $row['discount'];
                                        echo sprintf('-%.2f, (%.1f%%)', $discount_amount, $discount_percent);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $row['amount'] -= ($discount_amount * $row['qty']);
                                        $total_amount  += $row['amount'];
                                        echo number_format($row['amount'], 2);
                                        ?>
                                    </td>
                                    <td><?php
                                        $vat_percent = $row['percent'];
                                        $vat_amount  = $vat_percent * $row['amount'] / 100;
                                        echo number_format($vat_amount, 2);
                                        ?></td>
                                </tr>
                                <?php
                            }
                            ?>

                            <tr>
                                <td colspan="5" style=" text-align:right;"><strong
                                            style="font-size: 12px;">Total:</strong> &nbsp;
                                </td>
                                <td colspan="2"><strong style="font-size: 12px;">
                                        <?= number_format($total_amount, 2) ?>
                                    </strong></td>
                            </tr>


                            <tr>
                                <td colspan="5" style=" text-align:right;"><strong
                                            style="font-size: 12px; color: #222222;"><?php echo $modeOfPayment; ?>:&nbsp;</strong>
                                </td>
                                <td colspan="2"><strong style="font-size: 12px; color: #222222;">
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

                                        echo $firstPaymentOption;
                                        ?>
                                    </strong></td>
                            </tr>

                            <?php
                            if ($modeOfPayment1 != '-1') {
                                ?>
                                <tr>
                                    <td colspan="5" style=" text-align:right;"><strong
                                                style="font-size: 12px; color: #222222;"><?php echo $modeOfPayment1; ?>
                                            :&nbsp;</strong></td>
                                    <td colspan="2"><strong style="font-size: 12px; color: #222222;">
                                            <?php
                                            echo $secondPaymentOption;
                                            ?>
                                        </strong></td>
                                </tr>
                                <?php
                            }
                            ?>

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <div class="pull-right" style="margin-right:100px;">
            <a href="javascript:Clickheretoprint()" style="font-size:20px;">
                <button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button>
            </a>
        </div>
    </div>
</div>


