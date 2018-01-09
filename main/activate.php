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
    <link href="css/bootstrap.css" rel="stylesheet">
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

<div id="add-customer-modal" class="modal fade" style="width: 350px; left:58%;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Register</h4>
            </div>
            <div class="modal-body">
                <p>Loading...</p>
            </div>
            <div class="modal-footer">
                <div class="msg"></div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btnSaveCustomer" class="btn btn-primary" data-loading-text="<i>Saving...</i>">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<div id="login-customer-modal" class="modal fade" style="width: 350px; left:58%;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Login</h4>
            </div>
            <div class="modal-body">
                <p>Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btnLoginCustomer" class="btn btn-primary" data-loading-text="<i>Saving...</i>">
                    Login
                </button>
            </div>
        </div>
    </div>
</div>

</body>

<script type="text/javascript">

    $('#btnRegister').on('click', function (e) {
        e.preventDefault();
        $('#add-customer-modal').modal('show').find('.modal-body').load($(this).data('link'));
    });

    $('#btnLogin').on('click', function (e) {
        e.preventDefault();
        $('#login-customer-modal').modal('show').find('.modal-body').load($(this).data('link'));
    });

    $('#btnLogout').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {call:'logoutCustomer'},
            dataType: 'json',
            success: function (response) {
                window.location.href = window.location.href;
            }
        });
    });

    $('#btnLoginCustomer').on('click', function (e) {
        $(e.target).button('loading');
        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: $('#login-form').serialize(),
            dataType: 'json',
            success: function (response) {
                $(e.target).button('reset');
                if (response.success == 1) {
                    window.location.href = window.location.href + '<?='?invoice=RS-'.createRandomPassword()?>';
                } else {
                    $('#login-customer-modal .msg').html('<div class="alert alert-danger">'+response.msg+'</div>');
                }
            }
        });
    });

    $('#btnSaveCustomer').on('click', function (e) {
        $(e.target).button('loading');

        var data = new FormData($('#register-form')[0]);

        $.ajax({
            url: 'api.php',
            method: 'POST',
            dataType: 'json',
            type: "POST",
            enctype: 'multipart/form-data',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,

            success: function (response) {
                $(e.target).button('reset');
                if (response.success == 1) {

                    $('#add-customer-modal .msg').removeClass('alert alert-danger').addClass('alert alert-success').html(response.msg);
                    setTimeout(function() {
                        $('#add-customer-modal').modal('hide');
                        $('#btnLogin').click();
                    }, 3000);

                    /*
                     var _customer = response.data;
                     $('#customer').append('<option value="' + _customer.customer_id + '" selected>' + _customer.customer_name + '</option>');*/

                } else {
                    $('#add-customer-modal .msg').removeClass('alert alert-success').addClass('alert alert-danger').html(response.msg);
                }
            }
        });
    });

    $('#add-customer-modal').on('shown.bs.modal', function () {
        $('#dob').datepicker({
            format: 'yyyy-mm-dd'
        });
    });


</script>

<?php include('footer.php'); ?>
</html>

