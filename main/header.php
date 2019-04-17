<html>
<head>
    <?php
    require_once(dirname(__DIR__).'/connect.php');
    set_time_limit(180);
    ?>
    <title>
        POS
    </title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }

        .sidebar-nav {
            padding: 9px 0;
        }

    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="tcal.css"/>
    <script type="text/javascript" src="tcal.js"></script>

    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
    <script src="src/facebox.js" type="text/javascript"></script>
    <script src="vendors/bootstrap.js"></script>
    <script src="js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="js/bootbox.min.js" type="text/javascript"></script>
    <script src="js/bootstrap-typeahead.min.js" type="text/javascript"></script>
    <script src="js/polyfiller.js" type="text/javascript"></script>

</head>
<body>
<?php
include('navfixed.php');
?>