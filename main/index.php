<!DOCTYPE html>
<html>
<head>
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
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('a[rel*=facebox]').facebox({
                loadingImage: 'src/loading.gif',
                closeImage: 'src/closelabel.png'
            })
        })
    </script>
    <?php
    require_once('auth.php');
    ?>
    <?php
    function createRandomPassword() {
        $chars = "003232303232023232023456789";
        srand((double)microtime() * 1000000);
        $i    = 0;
        $pass = '';
        while ($i <= 7) {

            $num = rand() % 33;

            $tmp = substr($chars, $num, 1);

            $pass = $pass . $tmp;

            $i++;

        }

        return $pass;
    }

    $finalcode = 'RS-' . createRandomPassword();
    ?>

    <script language="javascript" type="text/javascript">
        /* Visit http://www.yaldex.com/ for full source code
         and get more free JavaScript, CSS and DHTML scripts! */

        var timerID = null;
        var timerRunning = false;
        function stopclock() {
            if (timerRunning)
                clearTimeout(timerID);
            timerRunning = false;
        }
        function showtime() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds()
            var timeValue = "" + ((hours > 12) ? hours - 12 : hours)
            if (timeValue == "0") timeValue = 12;
            timeValue += ((minutes < 10) ? ":0" : ":") + minutes
            timeValue += ((seconds < 10) ? ":0" : ":") + seconds
            timeValue += (hours >= 12) ? " P.M." : " A.M."
            document.clock.face.value = timeValue;
            timerID = setTimeout("showtime()", 1000);
            timerRunning = true;
        }
        function startclock() {
            stopclock();
            showtime();
        }
        window.onload = startclock;
        // End -->
    </SCRIPT>
</head>
<body>
<?php include('navfixed.php'); ?>
<a href="../index.php">Logout</a>


<?php
$position = $_SESSION['SESS_LAST_NAME'];

?>

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
                                <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="submit"
                                                                                  class="trans" name="face" value="">
                            </form>
                        </div>
                    </li>
                </ul>
            </div><!--/.well -->
        </div><!--/span-->
        <div class="span10">
            <div class="contentheader">
                <i class="icon-dashboard"></i> Dashboard

                <font style=" font:bold 44px 'Aleo'; text-shadow:1px 1px 25px #000; color:#fff;">
                    <center>Inflight Dubai</center>
                </font>

                <?php
                include('main-menu.php');
                ?>
                <!--<div id="mainmain">


                    <a href="sales.php?id=cash&invoice=<?php /*echo $finalcode */?>"><i class="icon-shopping-cart icon-2x"></i><br> Sales</a>
                    <a href="products.php"><i class="icon-list-alt icon-2x"></i><br> Products</a>
                    <a href="customer.php"><i class="icon-group icon-2x"></i><br> Customers</a>
                    <a href="bookingcalander.php"><i class="icon-group icon-2x"></i><br> Booking Calander</a>
                    <a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i><br> Sales Report</a>
                    <a href="../index.php"><font color="red"><i class="icon-off icon-2x"></i></font><br> Logout</a>

                    <div class="clearfix"></div>
                </div>-->
            </div>
        </div>
    </div>
</body>
<?php include('footer.php'); ?>
</html>
