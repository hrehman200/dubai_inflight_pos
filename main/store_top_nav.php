<?php
require_once('../connect.php');

$query = $db->prepare('SELECT * FROM customer WHERE customer_id = ? LIMIT 1');
$query->execute(array($_SESSION['CUSTOMER_ID']));
$row = $query->fetch();
?>

<!-- Google Tag Manager -->
<script>/*(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PTHNTCC');*/</script>

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

<div class="nav-section">
    <div class="if-logo">
        <a href="/en">
            <img alt="logo" src="images/if-logo-en.svg">
        </a>
    </div>
    <!--ADD DRUPAL-->
    <div class="main-nav">
        <div class="nav-wrap">
            <ul class="nav-listing">
                <li class="has-items"> <a href="https://www.inflightdubai.com/en/flying-with-us/the-experience">Flying With Us</a>
                    <ul class="nav__submenu">
                        <li class="nav__submenu-item"><a href="/en/flying-with-us/the-experience">The Experience</a>
                        </li>
                        <li class="nav__submenu-item"><a href="https://www.inflightdubai.com/en/flying-with-us/inflight-Dubai-wind-tunnel">The Wind Tunnel</a>
                        </li>
                        <li class="nav__submenu-item"><a href="https://www.inflightdubai.com/en/flying-with-us/requirements">Requirements</a>
                        </li>
                        <li class="nav__submenu-item"></li>
                        <li class="nav__submenu-item"></li>
                    </ul>
                </li>
                <li class="has-items"> <a href="https://www.inflightdubai.com/en/indoor-skydiving-packages">Packages</a>
                    <ul class="nav__submenu">
                        <li class="nav__submenu-item"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/first-time-flyer">First Time Flyer</a>
                        </li>
                        <li class="nav__submenu-item"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/return-flyers">Return Flyers</a>
                        </li>
                        <li class="nav__submenu-item"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/birthday-packages">Birthday Packages</a>
                        </li>
                        <li class="nav__submenu-item"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/corporate-packages">Corporate Packages</a>
                        </li>
                        <li class="nav__submenu-item"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/school-events">School Events</a>
                        </li>
                        <li class="nav__submenu-item"></li>
                        <li class="nav__submenu-item"></li>
                    </ul>
                </li>
                <li class="leaf"><a href="https://www.inflightdubai.com/en/inflight-dubai-gift-vouchers-coupons">Gift Vouchers &amp; Offers</a>
                </li>
                <li class="last leaf"><a href="https://www.inflightdubai.com/en/merchandise-store">Merchandise</a>
                </li>

                <?php
                    if (isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
                        ?>
                        <button class="btn btnBookings">My Bookings (<span id="spBookings">0</span>)</button>
                        <li><a class="" href="javascript:;" data-link="customer_update.php">
                                <?php
                                if($row['image'] != '') {
                                    echo sprintf('<img src="%s" style="max-height: 20px; width: auto;" />', BASE_URL.'main/uploads/'.$row['image']);
                                } else {
                                    echo '<i class="icon-user icon-large"></i>';
                                }
                                ?>
                                Welcome: <strong> <?php echo $_SESSION['CUSTOMER_FIRST_NAME']; ?></strong></a></li>
                        <?php
                    }

                    if (isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
                        ?>
                        <li><a href="javascript:;" class="btnLogout"><font color="red"><i class="icon-off icon-large"></i></font> Log Out</a></li>
                        <?php
                    } else {
                        ?>
                        <li><a class="btnRegister" href='javascript:;' data-link="customer_add.php">Register</a></li>
                        <li><a class="btnLogin" href='javascript:;' data-link="customer_login.php">Login</a></li>
                        <?php
                    }
                ?>
            </ul>
            <!-- Search pop -->
            <div class="search-pop">
                <form class="search-form form-inline my-2 my-lg-0 ms-top-search" role="search" action="https://www.inflightdubai.com/en/inflight-search" method="post" id="search-block-form--2" accept-charset="UTF-8" target="_self">
                    <div>
                        <div class="container-inline">
                            <h2 class="element-invisible">Search form</h2>
                            <div class="form-item form-type-textfield form-item-search-block-form">
                                <input title="" class="custom-search-box form-control ms-search-input form-text" placeholder="Search" type="text" id="edit-search-block-form--4" name="search_block_form" value="" size="15" maxlength="128">
                            </div>
                            <div class="close-btn"></div>
                            <div class="form-actions form-wrapper" id="edit-actions--2">
                                <input class="form-control form-control-submit form-submit" type="submit" id="edit-submit--2" name="op" value="Search">
                                <div class="pop-close-btn"></div>
                            </div>
                            <input type="hidden" name="form_build_id" value="form-DArfC2Lp7dP-ROVa74o3JQ5OtlybDbcFsaCGVRmBRW8">
                            <input type="hidden" name="form_id" value="search_block_form">
                            <input type="hidden" name="custom_search_paths" value="inflight-search/[key]">
                        </div>
                    </div>
                </form>
            </div>
            <!-- search pop closed -->
            <!--<div class="lang-sector common-select-drop ">
                <div class="select">
                    <div class="select-styled">en</div>
                </div>
                <ul class="custom-select1 select-option1">
                    <li class=" active"><a href="https://www.inflightdubai.com/en" class="text-all-caps" data-value="en">en</a>
                    </li>
                    <li class=""><a href="https://www.inflightdubai.com/ar" class="text-all-caps" data-value="ar">ar</a>
                    </li>
                </ul>
            </div>
            <div class="top-search"><span>&nbsp;</span>
            </div>-->

        </div>
        <!--<div class="book-now"><a target="_blank" rel="nofollow" id="menu-desktop-book-now" href="https://store.inflightdubai.com/inflight/main/store.php">Book now</a>-->
        </div>
        <!--<div class="lang-sector common-select-drop mob-lang-sector">
            <div class="select">
                <div class="select-styled">en</div>
            </div>
            <ul class="custom-select1 select-option1">
                <li class=" active"><a href="https://www.inflightdubai.com/en" class="text-all-caps" data-value="en">en</a>
                </li>
                <li class=""><a href="https://www.inflightdubai.com/ar" class="text-all-caps" data-value="ar">ar</a>
                </li>
            </ul>
        </div>-->
        <div class="mob-booking-close">
            <div class="mob-menu menuFive clickMenuFive"> <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <div class="mob-menu-wrap">
            <div class="mob-menu menuFive"> <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</div>
<!-- mobile -->
<div class="mob-menu-pop">
    <div class="mob-menu-list">
        <ul class="nav-listing">
            <li class="has-items" class="first expanded"> <a href="https://www.inflightdubai.com/en/flying-with-us/the-experience">Flying With Us</a>
                <ul class="nav__submenu">
                    <li class="nav__submenu-item" class="first leaf"><a href="https://www.inflightdubai.com/en/flying-with-us/the-experience">The Experience</a>
                    </li>
                    <li class="nav__submenu-item" class="leaf"><a href="https://www.inflightdubai.com/en/flying-with-us/inflight-Dubai-wind-tunnel">The Wind Tunnel</a>
                    </li>
                    <li class="nav__submenu-item" class="last leaf"><a href="https://www.inflightdubai.com/en/flying-with-us/requirements">Requirements</a>
                    </li>
                    <li class="nav__submenu-item"></li>
                    <li class="nav__submenu-item"></li>
                </ul>
            </li>
            <li class="has-items" class="expanded"> <a href="/en/indoor-skydiving-packages">Packages</a>
                <ul class="nav__submenu">
                    <li class="nav__submenu-item" class="first leaf"><a href="/en/indoor-skydiving-packages/first-time-flyer">First Time Flyer</a>
                    </li>
                    <li class="nav__submenu-item" class="leaf"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/return-flyers">Return Flyer</a>
                    </li>
                    <li class="nav__submenu-item" class="leaf"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/birthday-packages">Birthday Packages</a>
                    </li>
                    <li class="nav__submenu-item" class="leaf"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/corporate-packages">Corporate Packages</a>
                    </li>
                    <li class="nav__submenu-item" class="last leaf"><a href="https://www.inflightdubai.com/en/indoor-skydiving-packages/school-events">School Events</a>
                    </li>
                    <li class="nav__submenu-item"></li>
                    <li class="nav__submenu-item"></li>
                </ul>
            </li>
            <li class="leaf"><a href="https://www.inflightdubai.com/en/inflight-dubai-gift-vouchers-coupons">Gift Vouchers &amp; Offers</a>
            </li>
            <li class="last leaf"><a href="https://www.inflightdubai.com/en/merchandise-store">Merchandise</a>
            </li>
            <?php
            if (isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
                ?>
                <button class="btn .btnBookings">My Bookings (<span id="spBookings">0</span>)</button>
                <li><a class="" href="javascript:;" data-link="customer_update.php">
                        <?php
                        if($row['image'] != '') {
                            echo sprintf('<img src="%s" style="max-height: 20px; width: auto;" />', BASE_URL.'main/uploads/'.$row['image']);
                        } else {
                            echo '<i class="icon-user icon-large"></i>';
                        }
                        ?>
                        Welcome: <strong> <?php echo $_SESSION['CUSTOMER_FIRST_NAME']; ?></strong></a></li>
                <?php
            }

            if (isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
                ?>
                <li><a href="javascript:;" class="btnLogout"><font color="red"><i class="icon-off icon-large"></i></font> Log Out</a></li>
                <?php
            } else {
                ?>
                <li><a class="btnRegister" href='javascript:;' data-link="customer_add.php">Register</a></li>
                <li><a class="btnLogin" href='javascript:;' data-link="customer_login.php">Login</a></li>
                <?php
            }
            ?>
        </ul>
        <div class="mob-search">
            <form class="search-form form-inline my-2 my-lg-0 ms-top-search" role="search" action="https://www.inflightdubai.com/en/inflight-search" method="post" id="search-block-form" accept-charset="UTF-8">
                <div>
                    <div class="container-inline">
                        <div class="form-item form-type-textfield form-item-search-block-form">
                            <input title="" class="custom-search-box form-control ms-search-input form-text" placeholder="Search" type="text" id="edit-search-block-form--2" name="search_block_form" value="" size="15" maxlength="128" />
                        </div>
                        <div class="close-btn"></div>
                        <div class="form-actions form-wrapper" id="edit-actions">
                            <input class="form-control form-control-submit form-submit" type="submit" id="edit-submit" name="op" value="Search" />
                            <div class="pop-close-btn"></div>
                        </div>
                        <input type="hidden" name="form_build_id" value="form-EDb5c2DQgZNaqRkvSWFlHSrgyQMZ-HIRSQipNXseOsU" />
                        <input type="hidden" name="form_id" value="search_block_form" />
                        <input type="hidden" name="custom_search_paths" value="inflight-search/[key]" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--mob-menu-list closed -->
    <!--<div class="book-now"><a target="_blank" rel="nofollow" id="menu-desktop-book-now" href="#">Book now</a>
    </div>-->
    <div class="mobile-select-drop"></div>
</div>

<div id="add-customer-modal" class="modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Register</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
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

<div id="login-customer-modal" class="modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Login</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
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

<div id="forgotpass-modal" class="modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Forgot Password</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
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

<div id="profile-modal" class="modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Profile</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
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

        $('.btnLogout').on('click', function (e) {
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


	