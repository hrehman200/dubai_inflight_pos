<!DOCTYPE html>
<head>
    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
    <title>
        POS
    </title>
    <?php
    include_once('../connect.php');
    ?>

    <link href="vendors/uniform.default.css" rel="stylesheet" media="screen">
    <link href="css/bootstrap_dark.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap-datepicker.standalone.css">

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- combosearch box-->
    <script src="vendors/bootstrap.js"></script>

    <script src="js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="js/bootbox.min.js" type="text/javascript"></script>
    <script src="js/bootstrap-typeahead.min.js" type="text/javascript"></script>

    <script src="js/polyfiller.js" type="text/javascript"></script>

    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>

</head>
<body>

<?php include('store_top_nav.php'); ?>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span10 offset1">
            <div class="contentheader">
                <i class="icon-certificate"></i>
                Account Activation
            </div>

            <div align="center">
            <?php
            if($_GET['lt'] != '') {
                $sql = "SELECT customer_id FROM customer 
                  WHERE activate_token IS NOT NULL AND activate_token = ? 
                  LIMIT 1 ";
                $query = $db->prepare($sql);
                $query->execute(array($_GET['lt']));
                $row = $query->fetch();
                if($row) {
                    $sql = 'UPDATE customer SET activate_token = NULL, status = 1 WHERE customer_id = ? ';
                    $query = $db->prepare($sql);
                    $query->execute(array($row['customer_id']));

                    echo '<h3>Your account is activated. You can now login to the site.</h3>';
                } else {
                    echo '<h3>Invalid link. Please make sure you came to this page via link we emailed you.</h3>';
                }
            } else {
                echo '<h3>Invalid link</h3>';
            }
            ?>
            </div>

        </div>
    </div>
</div>

</body>

<?php include('footer.php'); ?>
</html>

