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
                <h3>Reset Password</h3>
            </div>

            <div>
                <?php
                if(isset($_POST['fpt'])) {

                    $response = [];
                    if(strlen($_POST['password']) >= 6) {
                        if($_POST['password'] == $_POST['new_password']) {

                            $sql = "SELECT customer_id FROM customer 
                                WHERE forgot_pass_token IS NOT NULL AND forgot_pass_token = ? 
                                LIMIT 1 ";
                            $query = $db->prepare($sql);
                            $query->execute(array(sha1($_POST['fpt'])));
                            $row = $query->fetch();

                            if($row) {
                                $query = $db->prepare('UPDATE customer SET password = ?, forgot_pass_token = ?, status=1 WHERE customer_id = ?');
                                $query->execute(array(sha1($_POST['password']), '', $row['customer_id']));

                                $response = array('success'=>1, 'msg'=>'Password reset successfully. You can now login.');

                            } else {
                                $response = array('success'=>0, 'msg'=>'Invalid token. Make sure you came to this page via link we sent to your email');
                            }
                        } else {
                            $response = array('success'=>0, 'msg'=>'Password and Confirm Password does not match');
                        }
                    } else {
                        $response = array('success'=>0, 'msg'=>'Please enter password of atleast 6 characters');
                    }
                }
                ?>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">

                    <?php
                    if(array_key_exists('success', $response)) {
                        echo sprintf('<div class="alert alert-%s">%s</div>', $response['success']==1?'success':'danger', $response['msg']);
                    }
                    ?>

                    <input type="hidden" name="fpt" value="<?= $_REQUEST['fpt'] ?>"/>
                    <div class="form-group">
                        <label for="password">New Password:</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="password">Confirm Password:</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <button type="submit" class="btn btn-default">Submit</button>
                </form>
            </div>

        </div>
    </div>
</div>

</body>

<?php include('footer.php'); ?>
</html>

