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
        POS
    </title>
    <link href="css/bootstrap<?=(isset($_SESSION['CUSTOMER_ID'])?'_dark.min':'')?>.css" rel="stylesheet">

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
            $("<iframe>")
                .css('visibility', 'hidden')
                .attr("src", "flight_preview_print.php?invoice=<?=$invoice?>")
                .appendTo("body");
        }
    </script>
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

    <script language="javascript" type="text/javascript">
        var timerID = null;
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

<?php
if(!isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
    include('navfixed.php');
} else {
    include('store_top_nav.php');
}
?>

<div class="container-fluid">
    <div class="row-fluid">

        <?php
        if(!isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
            ?>
            <div class="span2">
                <div class="well sidebar-nav">
                    <ul class="nav nav-list">
                        <?php
                        include('side-menu.php');
                        ?>
                        <br><br><br><br><br><br>
                        <li>
                            <div class="hero-unit-clock">

                                <form name="clock">
                                    <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="submit"
                                                                                      class="trans" name="face"
                                                                                      value="">
                                </form>
                            </div>
                        </li>

                    </ul>
                </div><!--/.well -->
            </div><!--/span-->
            <?php
        }
        ?>

        <div class="<?=(isset($_SESSION['CUSTOMER_ID']) ? 'span12' : 'span10')?>">
            <div class="content" id="content">
                <div style="margin: 0 auto; padding: 20px; width: 900px; font-weight: normal;">
                    <div >
                        <div align="center" style="margin-top:50px;">
                            <img src="<?=BASE_URL?>main/img/inflight_logo.png" width="250" />
                        </div>
                        <?php
                        $resulta = $db->prepare("SELECT * FROM customer WHERE customer_name= :a");
                        $resulta->bindParam(':a', $cname);
                        $resulta->execute();
                        for ($i = 0; $rowa = $resulta->fetch(); $i++) {
                            $address = $rowa['address'];
                            $contact = $rowa['contact'];
                        }
                        ?>
                        <div style="width: 136px; float: left; height: 70px;">
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
                            </table>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div style="width: 100%; margin-top:-70px;">
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
                            $invoice_id = $_GET['invoice'];

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
                                        $current_price = $row['price'] + (CLASS_SESSION_COST * $row['class_people']);
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
                                        echo number_format($firstPaymentOption, 2);
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
                                        echo number_format($secondPaymentOption, 2);
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

                    </div>
                </div>
            </div>

            <div align="center">

                <?php
                if(isset($_SESSION['CUSTOMER_ID'])) {
                    ?>
                    <a href="store.php" style="font-size:20px;">
                        <button class="btn btn-primary btn-large"><i class="icon-backward"></i> Return to Store</button>
                    </a>
                    <?php
                }
                ?>

                <a href="javascript:Clickheretoprint()" style="font-size:20px;">
                    <button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button>
                </a>

            </div>
        </div>


    </div>
</div>


