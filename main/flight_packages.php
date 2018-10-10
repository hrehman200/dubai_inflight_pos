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
    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
    <script src="js/bootbox.min.js" type="text/javascript"></script>
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
    require_once('../connect.php');
    session_start();
    ?>
    <?php
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

                <div align="center">
                    <h3>Commercial Packages</h3>
                <?php
                $packages = $db->prepare("SELECT * FROM flight_packages WHERE status = 1 AND (type IS NULL OR type = 0)");
                $packages->execute(array());

                while($row = $packages->fetch()) {
                    echo sprintf('<a class="btn" href="flight_picker.php?pkg_id=%d&id=&invoice=%s">
                    <img src="img/flight_pacakges/%s" width="128" class="" /> <br/>
                    %s</a>', $row['id'], $finalcode, $row['image'], $row['package_name']);
                }
                ?>
                </div>

                <hr>

                <div align="center">
                    <h3>Internal Packages</h3>
                    <?php
                    $packages = $db->prepare("SELECT * FROM flight_packages WHERE status = 1 AND type = ?");
                    $packages->execute(array(FLIGHT_PACKAGE_TYPE_INTERNAL));

                    while($row = $packages->fetch()) {
                        echo sprintf('<a class="btn btnInternalPackages" href="flight_picker.php?pkg_id=%d&id=&invoice=%s" data-package="%s">
                        <img src="img/flight_pacakges/%s" width="128" class="" /> <br/>
                        %s</a>', $row['id'], $finalcode, $row['package_name'], $row['image'], $row['package_name']);
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>
</body>
<?php include('footer.php'); ?>
</html>

<style>
    .contentheader a {
        padding: 10px;
        margin: 10px;
        width: 150px;
        height: 150px;
    }
</style>

<script type="text/javascript">
</script>
