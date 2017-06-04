<!DOCTYPE html>
<head>
    <!-- js -->
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
    <script src="src/facebox.js" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('a[rel*=facebox]').facebox({
                loadingImage: 'src/loading.gif',
                closeImage: 'src/closelabel.png'
            })
        })
    </script>
    <title>
        POS
    </title>
    <?php
    require_once('auth.php');
    include('../connect.php');
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

        .sidebar-nav {
            padding: 9px 0;
        }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- combosearch box-->
    <script src="vendors/bootstrap.js"></script>

    <script src="js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="js/bootbox.min.js" type="text/javascript"></script>
    <script src="js/bootstrap-typeahead.min.js" type="text/javascript"></script>

    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <!--sa poip up-->


    <script language="javascript" type="text/javascript">
        /* Visit http://www.yaldex.com/ for full source code
         and get more free JavaScript, CSS and DHTML scripts! */
        /*<!--Begin
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
        window.onload = startclock;*/
        // End -->
    </SCRIPT>

</head>
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
<body>
<?php include('navfixed.php'); ?>
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
                                <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="text"
                                                                                  class="trans" name="face" value=""
                                                                                  disabled>
                            </form>
                        </div>
                    </li>

                </ul>
            </div><!--/.well -->
        </div><!--/span-->
        <div class="span10">
            <div class="contentheader">
                <i class="icon-money"></i>
                Book Flight
            </div>
            <ul class="breadcrumb">
                <a href="index.php">
                    <li>Dashboard</li>
                </a> /
                <li class="active">
                    Book Flight
                </li>
            </ul>
            <div style="margin-top: -19px; margin-bottom: 21px;">
                <a href="flight_packages.php">
                    <button class="btn btn-default btn-large" style="float: none;"><i
                            class="icon icon-circle-arrow-left icon-large"></i> Back
                    </button>
                </a>
            </div>

            <form action="save_flight_order.php" id="formFlightTime" method="post">

                <input type="hidden" name="pt" value="<?php echo $_GET['id']; ?>"/>
                <input type="hidden" name="invoice" value="<?php echo $_GET['invoice']; ?>"/>
                <input type="hidden" name="pkg_id" value="<?php echo $_GET['pkg_id']; ?>"/>
                <input type="hidden" name="flightDate" id="flightDate" value="" />
                <input type="hidden" name="flightTime" id="flightTime" value="" />
                <input type="hidden" name="flightDuration" id="flightDuration" value="" />
                <input type="hidden" name="offerDuration" id="offerDuration" value="" />

                <?php
                $result = $db->prepare("SELECT * FROM flight_packages WHERE id = :package_id");
                $result->execute(array('package_id'=>$_GET['pkg_id']));
                $row = $result->fetch();
                ?>
                <h4><?php echo $row['package_name']; ?></h4>

                <select class="span6" name="flightOffer" id="flightOffer">
                    <option>Select a Flight Offer</option>
                    <?php
                    $result = $db->prepare("SELECT * FROM flight_offers WHERE package_id = :package_id");
                    $result->execute(array('package_id'=>$_GET['pkg_id']));
                    for ($i = 0; $row = $result->fetch(); $i++) {
                        ?>
                        <option value="<?php echo $row['id']; ?>" data-duration="<?php echo $row['duration']; ?>" <?php echo $_GET['offer_id']==$row['id'] ? 'selected' : ''?> >
                            <?php echo $row['offer_name']; ?> - <?php echo $row['code']; ?> - <?php echo $row['duration']; ?> Minutes - AED<?php echo $row['price']; ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
                <input type="hidden" name="date" value="<?php echo date("m/d/y"); ?>"/>

                <br/>
                <input type="text" class="form-contorl span6" placeholder="Search Customers" id="customer" name="customer" />
                <button id="btnAddCustomer" data-href="user_login.php" class="btn btn-secondary" style="margin-bottom:9px;">
                    Add Customer
                </button>

                <div class="row">
                    <div class="span3" style="margin-left:25px;">
                        <div id="datePicker"></div>
                    </div>

                    <div class="span6" id="timeslots">
                    </div>
                </div>

            </form>
            <table class="table table-bordered" id="resultTable" data-responsive="table">
                <thead>
                <tr>
                    <th> Offer Code</th>
                    <th> Package</th>
                    <th> Flight Offer</th>
                    <th> Price</th>
                    <th> Minutes</th>
                    <th> Action</th>
                </tr>
                </thead>
                <tbody>

                <?php
                $id = $_GET['invoice'];
                include('../connect.php');

                $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $str_query = parse_url($url, PHP_URL_QUERY);

                $result = $db->prepare("SELECT * FROM sales_order WHERE invoice= :invoiceId GROUP BY invoice, flight_offer_id");
                $result->bindParam(':invoiceId', $id);
                $result->execute();
                for ($i = 1; $row = $result->fetch(); $i++) {
                    ?>
                    <tr class="record">
                        <td hidden><?php echo $row['product']; ?></td>
                        <td><?php echo $row['product_code']; ?></td>
                        <td><?php echo $row['gen_name']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo formatMoney($row['price'], true); ?></td>
                        <td><?php echo $row['qty']; ?></td>
                        <td width="90"><a
                                href="delete_flight_order.php?transaction_id=<?php echo $row['transaction_id']."&".$str_query;?>" >
                                <button class="btn btn-mini btn-warning"><i class="icon icon-remove"></i> Cancel
                                </button>
                            </a></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td> Total Amount:</td>
                    <td> Total Minutes:</td>
                    <td></td>
                </tr>
                <tr>
                    <th colspan="3" align="right"><strong style="font-size: 12px; color: #222222;">Total:</strong></th>
                    <td colspan="1"><strong style="font-size: 12px; color: #222222;">
                            <?php
                            function formatMoney($number, $fractional = false) {
                                if ($fractional) {
                                    $number = sprintf('%.2f', $number);
                                }
                                while (true) {
                                    $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
                                    if ($replaced != $number) {
                                        $number = $replaced;
                                    } else {
                                        break;
                                    }
                                }

                                return $number;
                            }

                            $sdsd     = $_GET['invoice'];
                            $resultas = $db->prepare("SELECT sum(price) FROM sales_order WHERE invoice= :a");
                            $resultas->bindParam(':a', $sdsd);
                            $resultas->execute();
                            for ($i = 0; $rowas = $resultas->fetch(); $i++) {
                                $fgfg = $rowas['sum(price)'];
                                echo formatMoney($fgfg, true);
                            }
                            ?>
                        </strong></td>
                    <td colspan="1"><strong style="font-size: 12px; color: #222222;">
                            <?php
                            $resulta = $db->prepare("SELECT sum(qty) FROM sales_order WHERE invoice= :b");
                            $resulta->bindParam(':b', $sdsd);
                            $resulta->execute();
                            for ($i = 0; $qwe = $resulta->fetch(); $i++) {
                                $asd = $qwe['sum(qty)'];
                                echo formatMoney($asd, true);
                            }
                            ?>

                    </td>
                    <th></th>
                </tr>

                </tbody>
            </table>
            <br>
            <a rel="facebox"
               href="checkout.php?pt=cash&
               invoice=<?php echo $_GET['invoice'] ?>&
               total=<?php echo $fgfg ?>&
               totalprof=<?php echo $asd ?>&
               cashier=<?php echo $_SESSION['SESS_FIRST_NAME'] ?>&
               savingflight=1">
                <button class="btn btn-success btn-large btn-block"><i class="icon icon-save icon-large"></i> SAVE
                </button>
            </a>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<div id="add-customer-modal" class="modal fade" style="width: 350px; left:58%;">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Add Customer</h4>
        </div>
        <div class="modal-body">
            <p>Loading...</p>
        </div>
        <div class="modal-footer">
            <div class="msg"></div>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" id="btnSaveCustomer" class="btn btn-primary" data-loading-text="<i>Saving...</i>">Save</button>
        </div>
    </div>
</div>

</body>

<script type="text/javascript">

    $("#customer").typeahead({
        onSelect: function(item) {
            console.log(item);
        },
        ajax: {
            url: "api.php",
            timeout: 500,
            displayField: "customer_name",
            triggerLength: 1,
            method: "post",
            loadingClass: "loading-circle",
            preDispatch: function (query) {
                return {
                    search: query,
                    call: 'searchCustomers',
                }
            },
            preProcess: function (response) {
                console.log(response.success);
                if (response.success == false) {
                    return false;
                }
                return response.data;
            }
        },
    });

    var _getTimeslots = function(flightDate, flightOfferId, duration) {

        if(duration == undefined) {
            //alert('Select a flight offer first');
            return;
        }

        $.ajax({
            url:'api.php',
            method: 'POST',
            data: {
                'call': 'getTimeslotsForFlightDate',
                'flight_date': flightDate,
                'flight_offer_id': flightOfferId,
                'duration': duration
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if(response.success == 1) {
                    $('#timeslots').html(response.data);
                }
            }
        });
    };

    $("#datePicker").datepicker({
        format: 'yyyy-mm-dd'
    }).on('changeDate', function(e) {
        var pickedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
        $('#flightDate').val(pickedDate);
        _getTimeslots(pickedDate, $('#flightOffer').val(), $('#flightOffer option:selected').data('duration'));

    }).datepicker('update', '<?php echo $_GET['date']?>')
        .trigger('changeDate');

    $('#btnAddCustomer').on('click', function(e){
        e.preventDefault();
        $('#add-customer-modal').modal('show').find('.modal-body').load($(this).data('href'));
    });

    $('#btnSaveCustomer').on('click', function(e) {
        $(e.target).button('loading');
        $.ajax({
            url:'api.php',
            method: 'POST',
            data: $('#register-form').serialize(),
            dataType: 'json',
            success: function(response) {
                $(e.target).button('reset');
                if(response.success == 1) {
                    $('#add-customer-modal').modal('hide');
                    var _customer = response.data;
                    $('#customer').append('<option value="'+_customer.customer_id+'" selected>'+_customer.customer_name+'</option>');
                } else {
                    $('#add-customer-modal .msg').html('');
                }
            }
        });
    });

    $('#add-customer-modal').on('shown.bs.modal', function() {
       $('#dob').datepicker({
           format: 'yyyy-mm-dd'
       });
    });

    $('#timeslots').on('click', '.label', function(e) {

        /*var dialog = bootbox.dialog({
            title: 'A custom dialog with init',
            message: '<p><i class="fa fa-spin fa-spinner"></i> Loading...</p>'
        });
        dialog.init(function(){
            dialog.find('.bootbox-body').html('I was loaded after the dialog was shown!');

        });*/

        bootbox.prompt({
            title: "Enter minutes to fly",
            inputType: 'number',
            callback: function (minutes) {

                if(minutes !== null) {
                    var duration = $('#flightOffer option:selected').data('duration');
                    if (minutes <= duration) {
                        $('#flightTime').val($(e.target).text());
                        $('#offerDuration').val(duration);
                        $('#flightDuration').val(minutes);
                        $('#formFlightTime').submit();
                    } else {
                        alert('You can not assign more than ' + duration + ' minutes.');
                        return false;
                    }
                }
            }
        });

    });

</script>

<?php include('footer.php'); ?>
</html>

