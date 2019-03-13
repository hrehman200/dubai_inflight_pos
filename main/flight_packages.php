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
                    <h3><?=(is_string($_GET['pkg_id']) ? $_GET['pkg_id'] : 'Packages')?></h3>
                    <?php
                    $packages = $db->prepare("SELECT * FROM flight_packages WHERE status = 1 AND (type IS NULL OR type = 0)");
                    $packages->execute(array());

                    $packages1 = [
                        'FTF' => [
                            'packages' => [
                                'Earn your wings' => [
                                    'id' => 1,
                                    'img' => ''
                                ],
                                'Spread your wings' => [
                                    'id' => 3,
                                    'img' => ''
                                ],
                                'FTF Class Session' => [
                                    'id' => 5,
                                    'img' => ''
                                ]
                            ],
                            'img' => ''
                        ],
                        'Return Flyer' => [
                            'id' => 6,
                            'img' => '',
                            'packages' => []
                        ],
                        'Military' => [
                            'packages' => [
                                'Military Contract' => [
                                    'id' => 6,
                                    'img' => ''
                                ],
                                'Military Individuals' => [
                                    'id' => 6,
                                    'img' => ''
                                ]
                            ],
                            'img' => ''
                        ],
                        'Add On' => [
                            'packages' => [
                                'Up Sale' => [
                                    'id' => 18,
                                    'img' => ''
                                ],
                                'Classroom' => [
                                    'id' => 5,
                                    'img' => ''
                                ],
                                'Gift Vouchers' => [
                                    'id' => 11,
                                    'img' => ''
                                ],
                                'FTF School Package' => [
                                    'id' => 12,
                                    'img' => ''
                                ]
                            ],
                            'img' => ''
                        ]
                    ];

                    function getLink($pkg_name, $pkg_data, $parent_pkg = null) {
                        global $finalcode;
                        if(array_key_exists('id', $pkg_data)) {
                            if($parent_pkg == 'Add On') {
                                $parent_pkg = 'Other';
                            }
                            $href = sprintf('flight_picker.php?pkg_id=%d&pkg_name=%s&invoice=%s', $pkg_data['id'], $parent_pkg, $finalcode);
                        } else {
                            $href = sprintf('flight_packages.php?pkg_id=%s&id=&invoice=%s', $pkg_name, $finalcode);
                        }

                        return sprintf('<a class="btn" href="%s">
                                    <img src="img/flight_pacakges/%s" width="128" class="" /> <br/>
                                    %s</a>', $href, $pkg_data['img'], $pkg_name);
                    }

                    $pkg_id = $_GET['pkg_id'];
                    if(is_string($pkg_id)) {
                        foreach($packages1[$pkg_id]['packages'] as $pkg_name => $pkg_data) {
                            echo getLink($pkg_name, $pkg_data, $pkg_id);
                        }
                    } else {
                        foreach ($packages1 as $pkg_name => $pkg_data) {
                            echo getLink($pkg_name, $pkg_data, $pkg_name);
                        }
                    }

                    ?>
                </div>

                <?php if(!isset($_GET['pkg_id'])) { ?>

                <hr>

                <div align="center">
                    <h3>Internal Bookings</h3>
                    <?php
                    $packages = $db->prepare("SELECT * FROM flight_packages WHERE status = 1 AND type = ?");
                    $packages->execute(array(FLIGHT_PACKAGE_TYPE_INTERNAL));

                    while ($row = $packages->fetch()) {
                        echo sprintf('<a class="btn btnInternalPackages" href="flight_picker.php?pkg_id=%d&pkg_name=Other&invoice=%s" data-package="%s">
                        <img src="img/flight_pacakges/%s" width="128" class="" /> <br/>
                        %s</a>', $row['id'], $finalcode, $row['package_name'], $row['image'], $row['package_name']);
                    }
                    ?>
                </div>

                <?php } ?>

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
