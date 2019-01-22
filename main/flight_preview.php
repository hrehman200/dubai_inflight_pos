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
                        <?php
                        $invoice_id = $_GET['invoice'];
                        require_once './partials/flight_preview.php';
                        ?>

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


