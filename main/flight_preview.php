<!DOCTYPE html>
<html>
<head>
    <?php require_once ('auth.php');?>
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
    <link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
    <script src="lib/jquery.js" type="text/javascript"></script>
    <script src="src/facebox.js" type="text/javascript"></script>
    <script language="javascript">
        function Clickheretoprint()
        {
            var disp_setting="toolbar=yes,location=no,directories=yes,menubar=yes,";
            disp_setting+="scrollbars=yes,width=800, height=400, left=100, top=25";
            var content_vlue = document.getElementById("content").innerHTML;

            var docprint=window.open("","",disp_setting);
            docprint.document.open();
            docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
            docprint.document.write(content_vlue);
            docprint.document.close();
            docprint.focus();
        }
    </script>
    <?php
    $invoice=$_GET['invoice'];
    include('../connect.php');
    $result = $db->prepare("SELECT * FROM sales WHERE invoice_number= :userid");
    $result->bindParam(':userid', $invoice);
    $result->execute();
    $discount = 0;
    for($i=0; $row = $result->fetch(); $i++){
        $cname=$row['name'];
        $invoice=$row['invoice_number'];
        $date=$row['date'];
        $cash=$row['due_date'];
        $cashier=$row['cashier'];
        $discount = $row['discount'];

        $pt=$row['type'];
        $price=$row['amount'];
        $cash_return = 0;
        if($pt=='cash'){
            $cash_return= $cash - $price;
        }
    }
    ?>
    <?php
    function createRandomPassword() {
        $chars = "003232303232023232023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;
        while ($i <= 7) {

            $num = rand() % 33;

            $tmp = substr($chars, $num, 1);

            $pass = $pass . $tmp;

            $i++;

        }
        return $pass;
    }

    function formatMoney($number, $fractional=false) {
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

    $finalcode='RS-'.createRandomPassword();
    ?>



    <script language="javascript" type="text/javascript">
        /* Visit http://www.yaldex.com/ for full source code
         and get more free JavaScript, CSS and DHTML scripts! */
        <!-- Begin
        var timerID = null;
        var timerRunning = false;
        function stopclock (){
            if(timerRunning)
                clearTimeout(timerID);
            timerRunning = false;
        }
        function showtime () {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds()
            var timeValue = "" + ((hours >12) ? hours -12 :hours)
            if (timeValue == "0") timeValue = 12;
            timeValue += ((minutes < 10) ? ":0" : ":") + minutes
            timeValue += ((seconds < 10) ? ":0" : ":") + seconds
            timeValue += (hours >= 12) ? " P.M." : " A.M."
            document.clock.face.value = timeValue;
            timerID = setTimeout("showtime()",1000);
            timerRunning = true;
        }
        function startclock() {
            stopclock();
            showtime();
        }
        window.onload=startclock;
        // End -->
    </SCRIPT>
<body>

<?php include('navfixed.php');?>

<div class="container-fluid">
    <div class="row-fluid">
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
                                <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="submit" class="trans" name="face" value="">
                            </form>
                        </div>
                    </li>

                </ul>
            </div><!--/.well -->
        </div><!--/span-->

        <div class="span10">
            <a href="javascript:window.history.back();" class="btn btn-default"><i class="icon-arrow-left"></i> Back to Sales</a>

            <div class="content" id="content">
                <div style="margin: 0 auto; padding: 20px; width: 900px; font-weight: normal;">
                    <div style="width: 100%; height: 190px;" >
                        <div style="width: 900px; float: left;">
                            <center><div style="font:bold 25px 'Aleo';">Sales Receipt</div>
                                Inflight Dubai	<br>
                                Vertical Indoor Windtunnel	<br>	<br>
                            </center>
                            <div>
                                <?php
                                $resulta = $db->prepare("SELECT * FROM customer WHERE customer_name= :a");
                                $resulta->bindParam(':a', $cname);
                                $resulta->execute();
                                for($i=0; $rowa = $resulta->fetch(); $i++){
                                    $address=$rowa['address'];
                                    $contact=$rowa['contact'];
                                }
                                ?>
                            </div>
                        </div>
                        <div style="width: 136px; float: left; height: 70px;">
                            <table cellpadding="3" cellspacing="0" style="font-family: arial; font-size: 12px;text-align:left;width : 100%;">

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
                        <table border="1" cellpadding="4" cellspacing="0" style="font-family: arial; font-size: 12px;	text-align:left;" width="100%">
                            <thead>
                            <tr>
                                <th width="90"> Code </th>
                                <th> Package </th>
                                <th> Offer </th>
                                <th> Price </th>
                                <th> Minutes </th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php
                            $invoice_id=$_GET['invoice'];

                            $result = $db->prepare("SELECT fp.id AS flight_purchase_id, fo.code, fpkg.package_name, fo.offer_name, fo.price, fo.duration FROM flight_purchases fp
                                      LEFT JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                                      LEFT JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                                      WHERE fp.invoice_id= :invoiceId");
                            $result->bindParam(':invoiceId', $invoice_id);
                            $result->execute();

                            $total_cost = 0;
                            $total_duration = 0;
                            while($row = $result->fetch()) {
                                $total_cost += $row['price'];
                                $total_duration += $row['duration'];
                                ?>
                                <tr class="record">
                                    <td><?php echo $row['code']; ?></td>
                                    <td><?php echo $row['package_name']; ?></td>
                                    <td><?php echo $row['offer_name'] ? $row['offer_name'] : 'Deduct from balance'; ?></td>
                                    <td><?php echo $row['price']; ?></td>
                                    <td><?php echo $row['duration']; ?></td>
                                </tr>

                                <?php
                                $query2 = $db->prepare('SELECT * FROM flight_bookings WHERE flight_purchase_id = :flight_purchase_id');
                                $query2->bindParam(':flight_purchase_id', $row['flight_purchase_id']);
                                $query2->execute();
                                while($row2 = $query2->fetch()) {
                                    ?>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td style="text-align: center;"><?=substr($row2['flight_time'],0,-3)?></td>
                                        <td></td>
                                        <td><?=$row2['duration']?></td>
                                    </tr>
                                    <?php
                                }
                                ?>

                                <?php
                            }
                            ?>
                            <tr>
                                <td colspan="3" style="text-align: right;">Sub Total:</td>
                                <td><?=$total_cost?></td>
                                <td colspan="2"><?=$total_duration?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: right;">Discount:</td>
                                <td><?php
                                    $discount_value = floor($discount * $total_cost / 100);
                                    echo sprintf('-%d (%s%%)', $discount_value, $discount);
                                    ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: right;">Total:</td>
                                <td><?php
                                    $total = $total_cost - $discount_value;
                                    echo $total;
                                    ?></td>
                                <td colspan="2"></td>
                            </tr>

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <div class="pull-right" style="margin-right:100px;">
            <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
        </div>
    </div>
</div>


