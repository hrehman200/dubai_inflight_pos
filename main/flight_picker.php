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

    <script src="js/polyfiller.js" type="text/javascript"></script>

    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <!--sa poip up-->


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
                <input type="hidden" name="customerId" id="customerId" value="<?=$_GET['customer_id']?>" />
                <input type="hidden" name="flightPurchaseId" id="flightPurchaseId" value="" />
                <input type="hidden" name="useBalance" id="useBalance" value="0" />

                <input type="hidden" name="creditDuration" id="creditDuration" value="" />
                <input type="hidden" name="useCredit" id="useCredit" value="0" />

                <?php
                $result = $db->prepare("SELECT * FROM flight_packages WHERE id = :package_id");
                $result->execute(array('package_id'=>$_GET['pkg_id']));
                $row = $result->fetch();
                ?>
                <h4><?php echo $row['package_name']; ?></h4>

                <select class="span6" name="flightOffer" id="flightOffer">
                    <option value="0">Select a Flight Offer</option>
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
                <input type="text" class="form-contorl span6" placeholder="Search Customers" id="customer" name="customer" autocomplete="off" />

                <button class="btn btn-info" style="margin-bottom: 9px;" id="btnFlightHistory">
                    Flight History
                </button>

                <button id="btnAddCustomer" data-href="user_login.php" class="btn btn-secondary" style="margin-bottom:9px;">
                    Add Customer
                </button>

                <button id="btnTransferCredit" class="btn btn-tertiary" style="margin-bottom:9px;">
                    Transfer Credit
                </button>

                <div class="row">
                    <div class="span3" style="margin-left:25px;">
                        <div id="datePicker"></div>
                    </div>

                    <div class="span5" >
                        <input type="checkbox" id="chkOnlySlotsWithDuration" name="chkOnlySlotsWithDuration" value="1" />
                        <label style="display: inline;" for="chkOnlySlotsWithDuration">Show slots that have <b><span id="spMinutes"></span> minutes</b> available</label>
                        <br/>

                      <input type="checkbox" id="chkOnlyOfficeTimeSlots" name="chkOnlyOfficeTimeSlots" value="1" unchecked style='display : none;'/>
                        <label style="display: none;" for="chkOnlyOfficeTimeSlots">Show office time slots only</label>
                        <br/><br/>

                        <div id="timeslots">
                        </div>
                    </div>

                    <h4>Customer's booking preview/balance</h4>
                    <div class="span3" id="divCustomerDetails">
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

                $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $str_query = parse_url($url, PHP_URL_QUERY);

                $result = $db->prepare("SELECT fp.id AS flight_purchase_id, fp.deduct_from_balance, fo.code, fpkg.package_name, fo.offer_name, fo.price, fo.duration FROM flight_purchases fp
                  LEFT JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                  LEFT JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                  WHERE fp.invoice_id= :invoiceId");
                $result->bindParam(':invoiceId', $id);
                $result->execute();

                $total_cost = 0;
                $total_duration = 0;
                while($row = $result->fetch()) {
                    if($row['deduct_from_balance']==0) {
                        $total_cost += $row['price'];
                        $total_duration += $row['duration'];
                    }
                    ?>
                    <tr class="record">
                        <td><?php echo $row['code']; ?></td>
                        <td><?php echo $row['package_name']; ?></td>
                        <td><?php echo $row['deduct_from_balance']==1 ? $row['offer_name'].' (Deduct from balance)' : $row['offer_name']; ?></td>
                        <td><?php echo $row['deduct_from_balance']==1 ? '-' : $row['price']; ?></td>
                        <td><?php echo $row['deduct_from_balance']==1 ? '-' : $row['duration']; ?></td>
                        <td width="90"><a
                                href="delete_flight_order.php?flight_purchase_id=<?php echo $row['flight_purchase_id'] . "&" . $str_query; ?>">
                                <button class="btn btn-mini btn-warning"><i class="icon icon-remove"></i> Cancel</button>
                            </a></td>
                        <script type="text/javascript">
                            $('#flightPurchaseId').val(<?=$row['flight_purchase_id']?>);
                        </script>
                    </tr>

                    <?php
                    $query2 = $db->prepare('SELECT * FROM flight_bookings WHERE flight_purchase_id = :flight_purchase_id');
                    $query2->bindParam(':flight_purchase_id', $row['flight_purchase_id']);
                    $query2->execute();
                    while($row2 = $query2->fetch()) {
                        ?>
                        <tr>
                            <td colspan="2"></td>
                            <td style="text-align: center;"><?=substr($row2['flight_time'],0,-3)?></td>
                            <td></td>
                            <td><?=$row2['duration']?></td>
                            <td><a href="delete_flight_order.php?booking_id=<?php echo $row2['id'] . "&" . $str_query; ?>">
                                    <button class="btn btn-mini btn-warning"><i class="icon icon-remove"></i> Cancel</button>
                                </a></td>
                        </tr>
                        <?php
                    }
                    ?>

                    <?php
                }
                ?>
                <tr>
                    <td colspan="3" style="text-align: right;">Totals:</td>
                    <td><?=$total_cost?></td>
                    <td colspan="2"><?=$total_duration?></td>
                </tr>
                </tbody>
            </table>
            <!--<button class="btn btn-mini btn-warning"><i class="icon icon-remove"></i> Cancel</button>-->
            <br>
            <a rel="facebox"
               href="checkout.php?pt=cash&
               invoice=<?php echo $_GET['invoice'] ?>&
               total=<?php echo $total_cost ?>&
               totalprof=<?php echo $asd ?>&
               cashier=<?php echo $_SESSION['SESS_FIRST_NAME'] ?>&
               savingflight=1&
               customerId=<?=$_GET['customer_id']?>">
                <button class="btn btn-success btn-large btn-block"><i class="icon icon-save icon-large"></i> PROCEED
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

    $('#flightOffer').on('change', function(e){
        var minutes = $(this).find('option:selected').data('duration');
        if(minutes > 30) {
            minutes = 30;
        }
        $('#spMinutes').html(minutes);
    }).trigger('change');

    $('#chkOnlySlotsWithDuration, #chkOnlyOfficeTimeSlots').on('change', function(e) {
        $('#datePicker').trigger('changeDate');
    });

    $("#customer").typeahead({
        onSelect: function(item) {
            $('#customerId').val(item.value);
            _getCustomerBookings(item.value);
        },
        ajax: {
            url: "api.php",
            timeout: 500,
            valueField: "customer_id",
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
                if (response.success == false) {
                    return false;
                }
                return response.data;
            }
        }
    }).val("<?=$_GET['customer_name']?>");

    var _getCustomerBookings = function(customerId, date) {
        $.ajax({
            url:'api.php',
            method: 'POST',
            data: {
                'call': 'getCustomerBookings',
                'customerId': customerId,
                'date':date
            },
            dataType: 'json',
            success: function(response) {
                if(response.success == 1) {
                    $('#divCustomerDetails').html(response.data);
                }
            }
        });
    };

    <?php
    // hack for auto selecting customer
    if($_GET['customer_id'] > 0) {
    ?>
        $('.typeahead.dropdown-menu').append('<li data-value="<?=$_GET['customer_id']?>" class="active"><a href="#"><?=$_GET['customer_name']?></a></li>');
        $('.typeahead.dropdown-menu li').click();
    <?php
    }
    ?>

    var _getTimeslots = function(flightDate, flightOfferId, duration, divToFillId) {

        if(duration == undefined) {
            duration = 30;
        }

        $.ajax({
            url:'api.php',
            method: 'POST',
            data: {
                'call': 'getTimeslotsForFlightDate',
                'flight_date': flightDate,
                'flight_offer_id': flightOfferId,
                'duration': duration,
                'show_slots_with_minutes_only': $('#chkOnlySlotsWithDuration').is(':checked') ? 1 : 0,
                'office_time_slots': $('#chkOnlyOfficeTimeSlots').is(':checked') ? 1 : 0
            },
            dataType: 'json',
            success: function(response) {
                if(response.success == 1) {
                    $(divToFillId).html(response.data);
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }
        });
    };

    $("#datePicker").datepicker({
        format: 'yyyy-mm-dd',
        // startDate: new Date()
    }).on('changeDate', function(e) {
        var pickedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
        $('#flightDate').val(pickedDate);
        _getTimeslots(pickedDate, $('#flightOffer').val(), $('#flightOffer option:selected').data('duration'), '#timeslots');

        if($('#flightOffer').val() == 0) {
            _getCustomerBookings(0, pickedDate);
        }

    }).datepicker('update', '<?php echo $_GET['date']?>')
        .trigger('changeDate');

    $('#btnFlightHistory').on('click', function(e) {
        e.preventDefault();
        var location = 'flight_history.php';
        if($('#customer').val() != '') {
            location += '?customerId='+$('#customerId').val()+'&customerName='+$('#customer').val();
            window.location = location;
        }
    });

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

        var flightTime = $(e.target).text();
        var selectedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
        var selectedTime = new Date(selectedDate+" "+flightTime);

        var officeStart = new Date(selectedDate+" 09:30");
        var officeClose = new Date(selectedDate+" 19:00");

        var beyondOfficeAllowed = '<?=$_SESSION['beyond_office_allowed']?>';

        if(beyondOfficeAllowed != '1' &&
            (selectedTime.getTime() < officeStart.getTime() || selectedTime.getTime() > officeClose.getTime())
        ) {
            bootbox.dialog({
                title: 'Enter password to book slot',
                message: '<div> \
                    <input type="password" id="txtPassword" /> \
                </div>',
                buttons: {
                    btn1: {
                        label: 'Verify',
                        className: 'btn-success',
                        callback: function (result) {
                            $.ajax({
                                url: 'api.php',
                                method: 'POST',
                                data: {
                                    'call': 'verifyPassword',
                                    'password': $('#txtPassword').val()
                                },
                                dataType: 'json',
                                success: function (response) {
                                    if (response.success == 1) {
                                        _bookSlot(flightTime);
                                    } else {
                                        alert('Wrong password');
                                    }
                                }
                            });
                        }
                    }
                }
            });
        } else {
            _bookSlot(flightTime);
        }
    });

    var _bookSlot = function(flightTime) {

        $('#flightTime').val(flightTime);

        var duration = $('#flightOffer option:selected').data('duration');
        $('#offerDuration').val(duration);

        if($('#flightOffer option:selected').text().indexOf('FTF') != -1) {
            var minutes = $('#flightOffer option:selected').data('duration');
            $('#flightPurchaseId').val('');
            $('#flightDuration').val(minutes);
            $('#formFlightTime').submit();

        } else {
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: {
                    'call': 'getDetailsForNewBookingModal',
                    'flightOfferId': $('#flightOffer').val(),
                    'flightPurchaseId': $('#flightPurchaseId').val(),
                    'customerId': $('#customerId').val()
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success == 1) {
                        var data = response.data;
                        _showSelectMinutesDialog(duration, data.unbooked_duration, data.balance, data.credit_time);
                    }
                }
            });
        }
    };

    var _showSelectMinutesDialog = function(duration, unbookedDuration, balance, credit_time) {

        unbookedDuration = unbookedDuration < 0 ? 0 : unbookedDuration;
        balance = balance < 0 ? 0 : balance;
        credit_time = credit_time < 0 ? 0 : credit_time;

        var dialog = bootbox.dialog({
            title: 'Enter minutes to fly',
            message: '<div> \
                <input type="text" id="txtMinutes" /> \
            </div>',
            buttons: {
                btn1: {
                    label: 'New Purchase',
                    className: 'btn-success',
                    callback: function (result) {
                        var minutes = $('#txtMinutes').val();
                        if(minutes !== null) {
                            if (minutes <= duration) {
                                $('#flightPurchaseId').val('');
                                $('#flightDuration').val(minutes);
                                $('#formFlightTime').submit();
                            } else {
                                alert('You can not assign more than ' + duration + ' minutes.');
                                return false;
                            }
                        }
                    }
                },
                btn2: {
                    label: 'Use Existing Purchase ('+unbookedDuration+')',
                    className: 'btn-info',
                    callback: function (result) {
                        var minutes = $('#txtMinutes').val();
                        if(minutes !== null) {
                            if (minutes <= unbookedDuration) {
                                $('#flightDuration').val(minutes);
                                $('#formFlightTime').submit();
                            } else {
                                alert('Existing purchase only has ' + unbookedDuration + ' minutes.');
                                return false;
                            }
                        }
                    }
                },
                // btn3: {
                //     label: 'Deduct from balance ('+balance+')',
                //     className: 'btn',
                //     callback: function (result) {
                //         var minutes = $('#txtMinutes').val();
                //         if(minutes !== null) {
                //             if (minutes <= balance) {
                //                 $('#useBalance').val(1);
                //                 $('#useCredit').val(0);
                //                 $('#flightPurchaseId').val('');
                //                 $('#flightDuration').val(minutes);
                //                 $('#formFlightTime').submit();
                //             } else {
                //                 alert('Balance does not have' + minutes + ' minutes.');
                //                 return false;
                //             }
                //         }
                //     }
                // },
                btn3: {
                    label: 'Deduct from Credit Time ('+credit_time+')',
                    className: 'btn',
                    callback: function (result) {
                        var minutes = $('#txtMinutes').val();
                        if(minutes !== null) {
                            if (minutes <= credit_time) {
                                    $('#useBalance').val(0);
                                    $('#useCredit').val(1);
                                    $('#flightPurchaseId').val('');
                                    $('#flightDuration').val(minutes);
                                    $('#creditDuration').val(credit_time);
                                    $('#formFlightTime').submit();

                            } else {
                                alert('Credit Balance does not have' + minutes + ' minutes.');
                                return false;
                            }
                        }
                    }
                }
            }
        });
    }

</script>

<script type="text/javascript">
    function deductFromBalance(duration, balance, flightofferID) {
        var dialog = bootbox.dialog({
            title: 'Enter minutes to fly',
            message: '<div> \
                <input type="text" id="txtMinutes" /> \
                \
                <input type="datetime-local" id="bookingTime" value="" /> \
            </div>',
            buttons: {
                btn1: {
                    label: 'Deduct from balance ('+balance+')',
                    className: 'btn-success',
                    callback: function (result) {
                        var minutes = $('#txtMinutes').val();
                        var selectedDateTime = $('#bookingTime').val();
                        var d = new Date(selectedDateTime);
                       // var selectedTime = toDateTime(d);

                      //  alert('Balance does not have' + toDateTime(d) );

                        if(minutes !== null) {
                            if (minutes <= balance) {
                                $('#flightDate').val(toGetDate(d));
                                $('#flightTime').val(toGetTime(d));
                                $('#useBalance').val(1);
                                $('#flightPurchaseId').val('');
                                $('#flightDuration').val(minutes);
                                $('#flightOffer').val(flightofferID);
                                $('#formFlightTime').submit();
                            } else {
                                alert('Balance does not have' + minutes + ' minutes.');
                                return false;
                            }
                        }
                    }
                }
            }
        });
    }

    function reschedule(flightBookingId) {
        var dialog = bootbox.dialog({
            title: 'Reschedule Flight Time',
            show: false,
            message: '<div> \
                <input type="date" data-date-inline-picker="true" id="bookingDate" value="" /> \
                Time: <label id="bookingTime" style="display: inline;;">00:00</label> <br/><br/> \
                <div id="datePickerInDialog"></div> \
                <div id="timeslotsInDialog"></div> \
            </div>',
            buttons: {
                btn1: {
                    label: 'Reschedule',
                    className: 'btn-success',
                    callback: function (result) {
                        var selectedDateTime = $("#bookingDate").val()+" "+$("#bookingTime").text();
                        var d = new Date(selectedDateTime);
                        var now = new Date();
                        if(d < now) {
                            alert('You cannot schedule in the past time');
                            return false;
                        } else {
                            $.ajax({
                                url: 'api.php',
                                method: 'POST',
                                data: {
                                    'call': 'rescheduleFlightTime',
                                    'flight_booking_id': flightBookingId,
                                    'flight_time': selectedDateTime
                                },
                                dataType: 'json',
                                success: function (response) {
                                    console.log(response);
                                    if (response.success == 1) {
                                        location.reload();
                                    }
                                }
                            });
                        }
                    }
                }
            }
        });

        dialog.on("shown.bs.modal", function() {
            $("#bookingDate").on('change', function(e) {
                _getTimeslots($("#bookingDate").val(), $('#flightOffer').val(), $('#flightOffer option:selected').data('duration'), '#timeslotsInDialog');
            });

            $('#timeslotsInDialog').on('click', '.label', function(e) {
                var flightTime = $(e.target).text();
                $('#bookingTime').text(flightTime);
            });

            webshim.setOptions('forms-ext', {
                replaceUI: 'auto',
                types: 'date',
                date: {
                    startView: 2,
                    inlinePicker: true,
                    classes: 'hide-inputbtns'
                }
            });
            webshim.polyfill('forms forms-ext');

        });

        dialog.modal('show');
    }

    function showTransferDialog(e) {
        e.preventDefault();
        var toCustomerId = 0;

        var dialog = bootbox.dialog({
            title: 'Transfer Credit',
            show: false,
            message: '<div> \
                <select id="customerInDialog"></select> \
                <input type="text" class="form-control" placeholder="Enter credit to transfer" id="credit_to_transfer" /> \
            </div>',
            buttons: {
                btn1: {
                    label: 'Transfer Credit',
                    className: 'btn-success',
                    callback: function (result) {

                        if($('#customerId').val() == '') {
                            alert('Select customer to transfer credit from');
                            return;
                        }


                        $.ajax({
                            url: 'api.php',
                            method: 'POST',
                            data: {
                                'call': 'transferCredit',
                                'from_customer_id': $('#customerId').val(),
                                'to_customer_id': $('#customerInDialog').val(),
                                'credit_to_transfer': $('#credit_to_transfer').val()
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.success == 1) {
                                    location.reload();
                                } else {
                                    alert(response.msg);
                                }
                            }
                        });
                    }
                }
            }
        });

        dialog.on("shown.bs.modal", function() {
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: {
                    'call': 'getCustomerOptions'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success == 1) {
                        $('#customerInDialog').html(response.data);
                    }
                }
            });
        });

        dialog.modal('show');
    }

    $('#btnTransferCredit').click(showTransferDialog);

function toGetDate(date) {
    var str = '';
    var year, month, day, hour, min;
    year = date.getUTCFullYear();
    month = date.getUTCMonth() + 1;
    month = month < 10 ? '0' + month : month;
    day = date.getUTCDate();
    day = day < 10 ? '0' + day : day;
    hour = date.getUTCHours();
    hour = hour < 10 ? '0' + hour : hour;
    min = date.getUTCMinutes();
    min = min < 10 ? '0' + min : min;
    
    str += year + '-' + month + '-' + day;
    return str;
}

function toGetTime(date) {
    var str = '';
    var hour, min;
    hour = date.getHours();
    hour = hour < 10 ? '0' + hour : hour;
    min = date.getMinutes();
    min = min < 10 ? '0' + min : min;
    
    str += ' ' + hour + ':' + min;
    return str;
}

</script>

<?php include('footer.php'); ?>
</html>

