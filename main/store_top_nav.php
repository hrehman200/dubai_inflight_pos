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
                    <li><a><i class="icon-user icon-large"></i>
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


	