<?php
require_once('../connect.php');

$query = $db->prepare('SELECT * FROM customer WHERE customer_id = ? LIMIT 1');
$query->execute(array($_SESSION['CUSTOMER_ID']));
$row = $query->fetch();
?>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PTHNTCC');</script>

<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PTHNTCC"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager -->

<!-- Facebook Pixel Code -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '467503977002724');
    fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
               src="https://www.facebook.com/tr?id=467503977002724&ev=PageView&noscript=1"
    /></noscript>
<!-- End Facebook Pixel Code -->

<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="<?=BASE_URL?>main/store.php"><b>Online Booking</b></a>
            <div class="nav-collapse collapse">
                <ul class="nav pull-right">

                    <?php
                    if (isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
                    ?>
                    <button class="btn" id="btnBookings">My Bookings (<span id="spBookings">0</span>)</button>
                    <li><a class="btnProfile" href="javascript:;" data-link="customer_update.php">
                            <?php
                            if($row['image'] != '') {
                                echo sprintf('<img src="%s" style="max-height: 20px; width: auto;" />', BASE_URL.'main/uploads/'.$row['image']);
                            } else {
                                echo '<i class="icon-user icon-large"></i>';
                            }
                            ?>
                            Welcome:<strong> <?php echo $_SESSION['CUSTOMER_FIRST_NAME']; ?></strong></a></li>
                        <?php
                    }

                    if (isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
                        ?>
                        <li><a href="javascript:;" id="btnLogout"><font color="red"><i class="icon-off icon-large"></i></font> Log Out</a></li>
                        <?php
                    } else {
                        ?>
                        <li><a class="btnRegister" href='javascript:;' data-link="customer_add.php">Register</a></li>
                        <li><a class="btnLogin" href='javascript:;' data-link="customer_login.php">Login</a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div><!--/.nav-collapse -->
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

<div id="forgotpass-modal" class="modal fade" style="width: 350px; left:58%;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Forgot Password</h4>
            </div>
            <div class="modal-body">
                <p>Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btnSendPassReset" class="btn btn-primary" data-loading-text="<i>Submitting...</i>">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

<div id="profile-modal" class="modal fade" style="width: 350px; left:58%;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Profile</h4>
            </div>
            <div class="modal-body">
                <p>Loading...</p>
            </div>
            <div class="modal-footer">
                <div class="msg"></div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btnSaveProfile" class="btn btn-primary" data-loading-text="<i>Submitting...</i>">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    $(function() {
        $('.btnRegister').on('click', function (e) {
            e.preventDefault();
            $('#add-customer-modal').modal({backdrop: 'static',keyboard: false}).find('.modal-body').load($(this).data('link'));
        });

        $('.btnLogin').on('click', function (e) {
            e.preventDefault();
            $('#login-customer-modal').modal({backdrop: 'static',keyboard: false}).find('.modal-body').load($(this).data('link'));
        });

        $('.btnForgotPass').on('click', function (e) {
            e.preventDefault();
            $('#forgotpass-modal').modal({backdrop: 'static',keyboard: false}).find('.modal-body').load($(this).data('link'));
        });

        $('.btnProfile').on('click', function (e) {
            e.preventDefault();
            $('#profile-modal').modal({backdrop: 'static',keyboard: false}).find('.modal-body').load($(this).data('link'));
        });

        $('#btnLogout').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: {call:'logoutCustomer'},
                dataType: 'json',
                success: function (response) {
                    window.location.href = 'store.php';
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
                        var invoice = getParameterByName('invoice');
                        var p = getParameterByName('p');
                        console.log(invoice);
                        console.log(p);
                        if(!invoice || invoice == 'undefined') {
                            invoice = 'RS-'+'<?=createRandomPassword()?>';
                        }
                        if(p == 1) {
                            p = 2;
                        }
                        window.location.href = 'store.php?invoice='+invoice+'&p='+p;
                    } else {
                        $('#login-customer-modal .msg').html('<div class="alert alert-danger">'+response.msg+'</div>');
                    }
                }
            });
        });

        $('#btnSendPassReset').on('click', function (e) {
            $(e.target).button('loading');
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: $('#forgotpass-form').serialize(),
                dataType: 'json',
                success: function (response) {
                    $(e.target).button('reset');
                    if (response.success == 1) {
                        $('#forgotpass-modal .msg').html('<div class="alert alert-success">'+response.msg+'</div>');
                    } else {
                        $('#forgotpass-modal .msg').html('<div class="alert alert-danger">'+response.msg+'</div>');
                    }
                }
            });
        });

        $('#btnSaveCustomer').on('click', function (e) {
            $(e.target).button('loading');

            var invoice = getParameterByName('invoice');
            var p = getParameterByName('p');

            $('#register-form')
                .find('input[name="invoice"]').val(invoice).end()
                .find('input[name="p"]').val(p).end();
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

                    } else {
                        $('#add-customer-modal .msg').removeClass('alert alert-success').addClass('alert alert-danger').html(response.msg);
                    }
                }
            });
        });

        $('#btnSaveProfile').on('click', function (e) {
            $(e.target).button('loading');

            var data = new FormData($('#profile-form')[0]);

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
                        $('#profile-modal .msg').removeClass('alert alert-danger').addClass('alert alert-success').html(response.msg);
                    } else {
                        $('#profile-modal .msg').removeClass('alert alert-success').addClass('alert alert-danger').html(response.msg);
                    }
                }
            });
        });

        $('#add-customer-modal').on('shown.bs.modal', function () {
            $('#dob').datepicker({
                format: 'yyyy-mm-dd'
            });
        });
    });
</script>


	