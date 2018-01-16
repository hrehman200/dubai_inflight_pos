<!DOCTYPE html>
<head>
    <!-- js -->
    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
    <title>
        POS
    </title>
    <?php
    include_once('../connect.php');

    if(isset($_SESSION['CUSTOMER_FIRST_NAME']) && $_GET['invoice'] == '') {
        $page = $_SERVER['PHP_SELF'];
        header("Refresh:0; url=$page?invoice=RS-".createRandomPassword());
    }
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

        .sidebar-nav {
            padding: 9px 0;
        }

        #divCustomerDetails {
            font-size: 15px;
            /*overflow-y: scroll;
            max-height: 500px;
            width:28%;*/
        }

        .modalBookings {
            width: 70% !important;
            margin-left: -34%;
            margin-right: -34%;
            font-size: 14px;
        }

        .btn-large {
            margin: 10px 0;
            width: 300px;
            display: block;
        }

        .legend {
            list-style: none;
        }
        .legend .legend-item {
            float: left;
            margin-left: 10px;
        }
        .legend-colorBox {
            width: 1rem;
            height: 1rem;
            display: inline-block;
            background-color: blue;
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
            <?php
            if(isset($_SESSION['CUSTOMER_FIRST_NAME'])) {
                ?>
                <form action="store_save_flight_order.php" id="formFlightTime" method="post">

                    <input type="hidden" name="pt" value="<?php echo $_GET['id']; ?>"/>
                    <input type="hidden" name="invoice" value="<?php echo $_GET['invoice']; ?>"/>
                    <!--<input type="hidden" name="pkg_id" value="<?php /*echo $_GET['pkg_id']; */?>"/>-->
                    <input type="hidden" name="flightDate" id="flightDate" value=""/>
                    <input type="hidden" name="flightTime" id="flightTime" value=""/>
                    <input type="hidden" name="flightDuration" id="flightDuration" value=""/>
                    <input type="hidden" name="offerDuration" id="offerDuration" value=""/>
                    <input type="hidden" name="customerId" id="customerId" value="<?= $_SESSION['CUSTOMER_ID'] ?>"/>
                    <input type="hidden" name="flightPurchaseId" id="flightPurchaseId" value=""/>
                    <input type="hidden" name="useBalance" id="useBalance" value="0"/>
                    <input type="hidden" name="fromFlightPurchaseId" id="fromFlightPurchaseId" value=""/>

                    <input type="hidden" name="creditDuration" id="creditDuration" value=""/>
                    <input type="hidden" name="useCredit" id="useCredit" value="0"/>

                    <select class="span6" name="pkg_id" id="pkg_id">
                        <option value="0">Select a Flight Package</option>
                        <?php
                        $result = $db->prepare("SELECT * FROM flight_packages WHERE id IN (1,3)");
                        $result->execute();
                        for ($i = 0; $row = $result->fetch(); $i++) {
                            ?>
                            <option value="<?php echo $row['id']; ?>" <?php /*echo $_GET['pkg_id'] == $row['id'] ? 'selected' : ''*/ ?> >
                                <?php echo $row['package_name']; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>

                    <br>

                    <select class="span6" name="flightOffer" id="flightOffer">
                        <option value="0">Select a Flight Offer</option>
                        <?php
                        $result = $db->prepare("SELECT * FROM flight_offers WHERE package_id = :package_id AND status = 1 
                            AND offer_name NOT LIKE '%Upsale%'");
                        $result->execute(array('package_id' => $_GET['pkg_id']));
                        for ($i = 0; $row = $result->fetch(); $i++) {
                            ?>
                            <option value="<?php echo $row['id']; ?>"
                                    data-duration="<?php echo $row['duration']; ?>" <?php /*echo $_GET['offer_id'] == $row['id'] ? 'selected' : ''*/ ?> >
                                <?php echo $row['offer_name']; ?> - <?php echo $row['code']; ?>
                                - AED<?php echo $row['price']; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                    <input type="hidden" name="date" value="<?php echo date("m/d/y"); ?>"/>

                    <br/>

                    <button class="btn btn-info span2" style="margin-right: 25px; margin-left:0;" id="btnFlightHistory">
                        Flight History
                    </button>

                    <br/><br/>

                    <div class="row">
                        <div class="span3" style="margin-left:25px;">
                            <div class="divCalendar">
                                <div id="datePicker"></div>
                                <button class="btn" id="btnBookings">Bookings (<span id="spBookings">0</span>)</button>
                            </div>
                        </div>

                        <div class="span5">
                            <div class="divCalendar">
                                <input type="checkbox" id="chkOnlySlotsWithDuration" name="chkOnlySlotsWithDuration"
                                       value="1"
                                       checked class="hidden"/>
                                <label style="display: inline;" for="chkOnlySlotsWithDuration">
                                    <input type="text" class="input-mini" id="txtOfferMinutes" disabled />
                                    minutes</label>
                                <br/>

                                <!--<input type="checkbox" id="chkClassSession" name="chkClassSession" class="class-session"
                                       value="1"/>
                                <label style="display: inline;" for="chkClassSession" class="class-session">Class Session
                                    <span id="spClassPeople" style="padding-left:25px;">
                                    <input type="text" class="input-mini" id="txtClassPeople" name="txtClassPeople"
                                           value="0"/> people
                                    <button id="btnAddClassSession" class="btn btn-small">Add</button>
                                </span>
                                </label>
                                <br/>-->

                                <input type="checkbox" id="chkOnlyOfficeTimeSlots" name="chkOnlyOfficeTimeSlots" value="1"
                                       unchecked style='display : none;'/>
                                <label style="display: none;" for="chkOnlyOfficeTimeSlots">Show office time slots
                                    only</label>
                                <br/>

                                <div id="timeslots">
                                </div>

                                <ul class="legend">
                                    <li class="legend-item">
                                        <span class="legend-colorBox" style="background-color: green;"></span>
                                        <span class="Legend-label">Unbooked</span>
                                    </li>
                                    <li class="legend-item">
                                        <span class="legend-colorBox" style="background-color: red;"></span>
                                        <span class="Legend-label">Booked</span>
                                    </li>
                                </ul>

                            </div>

                        </div>

                        <div class="span3" id="divCustomerDetails">
                            <h4>Returns and Experience Flyer’s rates, please call 800Inflight or send email at (info@inflightdubai.com)</h4>
                        </div>
                    </div>

                </form>

                <div style="bottom:10px; background-color: #eeeeee; padding:10px;" class="span11">
                    <table class="table table-bordered" id="resultTable" data-responsive="table">
                        <thead>
                        <tr>
                            <th> Offer Code</th>
                            <th> Package</th>
                            <th> Flight Offer</th>
                            <th> Price</th>
                            <!--<th> Discount</th>-->
                            <th> VAT</th>
                            <th> Minutes</th>
                            <th> Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        $id = $_GET['invoice'];

                        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $str_query = parse_url($url, PHP_URL_QUERY);

                        $result = $db->prepare("SELECT fp.id AS flight_purchase_id, fp.deduct_from_balance, fp.class_people, fp.discount, fp.discount_id, vc.percent,
                      fo.code, fpkg.package_name, fo.offer_name, fo.price, fo.duration 
                      FROM flight_purchases fp
                      LEFT JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                      LEFT JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                      LEFT JOIN vat_codes vc ON fp.vat_code_id = vc.id
                      LEFT JOIN discounts d ON fp.discount_id = d.id
                      WHERE fp.invoice_id= :invoiceId");
                        $result->bindParam(':invoiceId', $id);
                        $result->execute();

                        $total_cost = 0;
                        $total_duration = 0;
                        $current_price = 0;
                        while ($row = $result->fetch()) {
                            if ($row['deduct_from_balance'] == 0) {
                                if ($row['class_people'] > 0) {
                                    $current_price = $row['price'] + (CLASS_SESSION_COST * $row['class_people']);
                                } else {
                                    $current_price = $row['price'];
                                }
                                $total_cost += $current_price;
                            }
                            $total_duration += $row['duration'];
                            ?>
                            <tr class="record">
                                <td><?php echo $row['code']; ?></td>
                                <td><?php echo $row['package_name']; ?></td>
                                <td><?php echo $row['deduct_from_balance'] == 1 ? $row['offer_name'] . ' (Deduct from balance)' : $row['offer_name']; ?></td>
                                <td class="tdAmount">
                                    <?php
                                    if ($row['deduct_from_balance'] == 1) {
                                        echo '-';
                                    } else {
                                        echo $current_price;
                                    }
                                    ?></td>
                                <!--<td>
                                    <?php
/*                                    $discount_percent = $row['discount'];
                                    $discount_amount = $discount_percent * $current_price / 100;
                                    $total_cost -= $discount_amount;
                                    */?>
                                    <select class="discountPercent"
                                            data-transaction-id="<?/*= $row['flight_purchase_id'] */?>">
                                        <option value="0" data-percent="0">None</option>
                                        <?php
/*                                        $query = $db->query(sprintf('SELECT * FROM discounts WHERE type = "%s" AND status=1', TYPE_SERVICE));
                                        $query->execute();
                                        while ($row2 = $query->fetch()) {
                                            $selected = (($row['discount_id'] == $row2['id']) ? 'selected' : '');
                                            echo sprintf('<option value="%d" %s data-percent="%.2f">%s (%.0f%%)</option>', $row2['id'], $selected, $row2['percent'], $row2['category'], $row2['percent']);
                                        }
                                        */?>
                                    </select>
                                    (<span class="discountAmount">-<?/*= $discount_amount */?></span>)
                                </td>-->
                                <td>
                                    <?php
                                    $vat_percent = $row['percent'];
                                    $current_price -= $discount_amount;
                                    $vat_amount = $vat_percent * $current_price / 100;
                                    ?>
                                    <span id="vatAmount">(<?= $row['percent'] ?>%)</span>
                                    <span id="vatPercent"><?= $vat_amount ?></span>
                                </td>
                                <td><?php echo $row['deduct_from_balance'] == 1 ? '-' : $row['duration']; ?></td>
                                <td width="90"><a
                                            href="delete_flight_order.php?flight_purchase_id=<?php echo $row['flight_purchase_id'] . "&" . $str_query; ?>">
                                        <button class="btn btn-mini btn-warning"><i class="icon icon-remove"></i> Cancel
                                        </button>
                                    </a></td>
                                <script type="text/javascript">
                                    $('#flightPurchaseId').val(<?=$row['flight_purchase_id']?>);
                                </script>
                            </tr>

                            <?php
                            $query2 = $db->prepare('SELECT * FROM flight_bookings WHERE flight_purchase_id = :flight_purchase_id');
                            $query2->bindParam(':flight_purchase_id', $row['flight_purchase_id']);
                            $query2->execute();
                            while ($row2 = $query2->fetch()) {
                                ?>
                                <tr>
                                    <td colspan="2"></td>
                                    <td style="text-align: center;"><?= substr($row2['flight_time'], 0, -3) ?></td>
                                    <td></td>
                                    <td></td>
                                    <td><?= $row2['duration'] ?></td>
                                    <td>
                                        <!--<a href="delete_flight_order.php?booking_id=<?php /*echo $row2['id'] . "&" . $str_query; */?>">
                                            <button class="btn btn-mini btn-warning"><i class="icon icon-remove"></i>
                                                Cancel
                                            </button>
                                        </a>--></td>
                                </tr>
                                <?php
                            }
                            ?>

                            <?php
                        }
                        ?>
                        <tr>
                            <td colspan="3" style="text-align: right;">Totals:</td>
                            <td><?= $total_cost ?></td>
                            <td></td>
                            <td colspan="2"><?= $total_duration ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <br>

                    <div align="center">
                        <button class="btn btn-success btn-large btn-block btnCheckout">
                            <i class="icon icon-save icon-large"></i>
                            PROCEED
                        </button>
                    </div>

                    <div class="clearfix"></div>
                </div>
                <?php
            } else {
                ?>
                <div class="" align="center">
                    <br>
                    <img src="<?=BASE_URL?>main/img/inflight_logo.png" width="350" style="margin-left:90px;" /><br>

                    <button class="btn btn-large btnRegister" data-link="customer_add.php">Signup (New Customer)</button>
                    <button class="btn btn-large btnLogin" data-link="customer_login.php">Login</button>
                    <button class="btn btn-large btnForgotPass" data-link="customer_forgotpass.php">Forgot Password</button>

                </div>
            <?php
            }
            ?>

        </div>
    </div>
</div>

<form id="payment_form" action="https://testsecureacceptance.cybersource.com/pay" method="post">
    <input type="hidden" name="access_key" value="3eb56338e7c33eeb80e937d4c10421fc">
    <input type="hidden" name="profile_id" value="INFDU01"><!--Your Profile Id-->
    <input type="hidden" name="transaction_uuid" value="<?php echo uniqid() ?>">
    <input type="hidden" name="signed_field_names" value="access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency">

    <input type="hidden" name="unsigned_field_names">
    <input type="hidden" name="signed_date_time" value="<?php echo gmdate("Y-m-d\TH:i:s\Z"); ?>">
    <input type="hidden" name="locale" value="en">
    
    <input type="hidden" name="transaction_type" value="sale" />
    <input type="hidden" name="reference_number" value="<?=$_GET['invoice']?>" />
    <input type="hidden" name="amount" value="<?=$total_cost?>" />
    <input type="hidden" name="currency" value="AED" />
</form>

</body>

<script type="text/javascript">

    $('.btnCheckout').on('click', function(e) {
        e.preventDefault();

        var amount = $('#payment_form').find('input[name="amount"]').val();
        if(amount > 0) {
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: {
                    'call': 'getSignature',
                    'data': $('#payment_form').serializeArray(),
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success == 1) {
                        $('#payment_form')
                            .find('#signature').remove().end()
                            .append('<input type="hidden" id="signature" name="signature" value="' + response.data + '" />')
                            .submit();
                    }
                }
            });
        } else {
            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: './store_savesales.php',
                data: {
                    req_reference_number: '<?=$_GET['invoice']?>',
                    req_amount: 0
                },
                complete: function(response) {
                    window.location.href = 'flight_preview.php?invoice=<?=$_GET['invoice']?>';
                }
            });
        }
    });

    var _setMinutes = function () {
        var minutes = $('#flightOffer').find('option:selected').data('duration');
        if (minutes > 30) {
            minutes = 30;
        }
        $('#txtOfferMinutes').val(minutes);
    };

    var _getFlightOffers = function(packageId) {
        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                'call': 'getFlightOffers',
                'packageId': packageId,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == 1) {

                    var offers = response.data;
                    $('#flightOffer option').not(':first').remove();
                    for(var i in offers) {
                        $('#flightOffer').append('<option value="'+offers[i].id+'" \
                        data-duration="'+offers[i].duration+'" '+(offers[i].id == "<?=$_GET["offer_id"]?>" ? "selected":"")+' > \
                            '+offers[i].offer_name+' - '+offers[i].code+' - AED'+offers[i].price+'\
                            </option>');
                    }
                }
            }
        });
    };

    $('#pkg_id').on('change', function (e) {
        if ($(this).val() == 0) {
            $('#timeslots').html('');
        }

        var packageId = $(this).val()
        _getFlightOffers(packageId);

    }).trigger('change');

    $('#flightOffer').on('change', function (e) {
        _setMinutes();
        if ($(this).val() == 0) {
            $('#timeslots').html('');
            $('.divCalendar').hide();
        } else {
            $('.divCalendar').show();
        }

        if ($('#flightOffer option:selected').text().indexOf('FTF') != -1) {
            $('#txtOfferMinutes').prop('disabled', true);
        } else {
            $('#txtOfferMinutes').prop('disabled', false);
        }

    }).trigger('change');

    // class session not for FTF
    if ($('#pkgName').text().indexOf('FTF') != -1 && $('#pkgName').text().toLowerCase().indexOf('class session') == -1) {
        $('.class-session').hide();
    } else {
        $('.class-session').show();
    }

    $('#chkOnlySlotsWithDuration, #chkOnlyOfficeTimeSlots').on('change', function (e) {
        $('#datePicker').trigger('changeDate');
    });

    $('#txtOfferMinutes').on('keyup', function (e) {
        var pickedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
        _getTimeslots(pickedDate, $('#flightOffer').val(), $('#txtOfferMinutes').val(), '#timeslots');

    }).on('blur', function (e) {
        if ($(this).val() == '') {
            _setMinutes();
        }
    });

    $('#chkClassSession').on('change', function (e) {
        if ($(this).is(':checked')) {
            $('#spClassPeople').show();
            $('#timeslots').hide();
        } else {
            $('#spClassPeople').hide();
            $('#timeslots').show();
        }
    }).trigger('change');

    $("#customer").typeahead({
        onSelect: function (item) {
            $('#customerId').val(item.value);
            //_getCustomerBookings(item.value);
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
    }).val("<?=$_GET['customer_name']?>")
        .on('change', function (e) {
            if ($(this).val() == '') {
                $('#customerId').val('');
                //$('#divCustomerDetails').html('');
                $('#timeslots').html('');
            }
        });

    var _getCustomerBookings = function (customerId, date) {
        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                'call': 'getCustomerBookings',
                'customerId': customerId,
                'date': date
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == 1) {
                    $('#spBookings').html(response.bookings);
                    $('#divCustomerDetails').html(response.data.table2);
                    $('#spCreditTime').html(response.credit_time);
                    $('.btn-transfer, .btn-reschedule, .btn-cancel').remove();
                    $('#divCustomerDetails table tr').find('th:eq(7), td:eq(7)').hide();
                }
            }
        });
    };

    <?php
    // hack for auto selecting customer
    if($_GET['customer_id'] > 0) {
    ?>
    $('.typeahead.dropdown-menu').append('<li data-value="<?=$_GET['customer_id']?>" class="active"><a href="javascript:;"><?=$_GET['customer_name']?></a></li>');
    $('.typeahead.dropdown-menu li').click();
    <?php
    }
    ?>

    var _getTimeslots = function (flightDate, flightOfferId, duration, divToFillId) {

        if ((flightOfferId == 0 || $('#customerId').val() == '') && divToFillId == '#timeslots') {
            $(divToFillId).html('');
            //return;
        }

        if (duration == undefined || duration == '') {
            duration = 30;
        }

        if (flightDate == '') {
            $('#datePicker').datepicker('update', new Date())
                .trigger('changeDate');
            return;
        }

        $.ajax({
            url: 'api.php',
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
            success: function (response) {
                if (response.success == 1) {
                    $(divToFillId).html(response.data);
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }
        });
    };

    $("#datePicker").datepicker({
        format: 'yyyy-mm-dd',
        startDate: new Date()
    }).on('changeDate', function (e) {
        var pickedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
        $('#flightDate').val(pickedDate);
        _getTimeslots(pickedDate, $('#flightOffer').val(), $('#txtOfferMinutes').val(), '#timeslots');
        //_getCustomerBookings($('#customerId').val(), pickedDate);

    }).datepicker('update', '<?php echo $_GET['date']?>')
        .trigger('changeDate');

    $('#btnBookings').on('click', function (e) {
        e.preventDefault();

        var pickedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');

        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                'call': 'getCustomerBookings',
                'customerId': 0,
                'date': pickedDate
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == 1) {
                    var dialog = bootbox.dialog({
                        title: 'Bookings',
                        message: response.data.table,
                        className: 'modalBookings'
                    });
                }
            }
        });
    });

    $('#btnFlightHistory').on('click', function (e) {
        e.preventDefault();
        var location = 'store_flight_history.php';
        if ($('#customer').val() != '') {
            window.location = location;
        }
    });

    $('#timeslots').on('click', '.label', function (e) {

        var flightTime = $(e.target).text();
        var selectedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
        var selectedTime = new Date(selectedDate + " " + flightTime);

        var officeStart = new Date(selectedDate + " 09:30");
        var officeClose = new Date(selectedDate + " 19:00");

        var unlocked = $(this).data('unlocked');
        var remainingMinutes = $(this).data('remaining-minutes');

        if (remainingMinutes <= 0) {
            alert('No minutes in this slot');
            return;
        }

        if (unlocked == 0 &&
            (selectedTime.getTime() < officeStart.getTime() || selectedTime.getTime() > officeClose.getTime())) {
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
                                    'password': $('#txtPassword').val(),
                                    'slotTime': selectedDate + " " + flightTime
                                },
                                dataType: 'json',
                                success: function (response) {
                                    if (response.success == 1) {
                                        $(e.target).data('unlocked', 1);
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

    $('#btnPurchaseViaCredit').on('click', function (e) {
        e.preventDefault();

        if ($('#customerId').val() > 0 && $('#flightOffer').val() > 0) {
            deductFromCreditTime($('#customerId').val(), $('#spCreditTime').text(), $('#flightOffer').val(), $('#flightOffer option:selected').data('duration'));
        } else {
            alert('Please select offer and customer first.');
        }
    });

    var _bookSlot = function (flightTime) {

        if($('#flightOffer').val() == 0) {
            bootbox.alert('Please select Flight Package and Offer first');
            return;
        }

        $('#flightTime').val(flightTime);

        var duration = $('#flightOffer option:selected').data('duration');
        $('#offerDuration').val(duration);

        if ($('#flightOffer option:selected').text().indexOf('FTF') != -1 ) {
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
                        _showSelectMinutesDialog(duration, data.unbooked_duration, data.balance, data.credit_time, flightTime);
                    }
                }
            });
        }
    };

    var _showSelectMinutesDialog = function (duration, unbookedDuration, balance, credit_time, flightTime) {

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
                    label: 'Process',
                    className: 'btn-success',
                    callback: function (result) {
                        var minutes = parseInt($('#txtMinutes').val());
                        if (minutes !== null) {
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
                    label: 'Use Existing Purchase (' + unbookedDuration + ')',
                    className: 'btn-info',
                    callback: function (result) {
                        var minutes = parseInt($('#txtMinutes').val());
                        if (minutes !== null) {
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
                /*btn3: {
                 label: 'Deduct from Pre-Opening Deals ('+credit_time+')',
                 className: 'btn',
                 callback: function (result) {
                 submitDeductFromCreditTime($('#customerId').val(), credit_time, $('#flightOffer').val(), duration, flightTime, true);
                 }
                 }*/
            }
        });
    };

    $('#btnAddClassSession').click(function (e) {
        e.preventDefault();
        if ($('#flightOffer').val() != 0 && $('#customerId').val() != '' && $('#txtClassPeople').val() > 0) {
            $('#formFlightTime').submit();
        } else {
            alert('Please select offer and customer');
        }
    });

    function deductFromCreditTime(customer_id, credit_time, flight_offer_id, flight_minutes) {
        var dialog = bootbox.dialog({
            title: 'Deduct from Pre Opening Deals',
            message: getDateTimeSlotPickerHtml() + '<br/><input type="text" id="txtMinutes" placeholder="Enter minutes to fly" />',
            buttons: {
                btn3: {
                    label: 'Deduct from Pre-Opening Deals(' + credit_time + ')',
                    className: 'btn',
                    callback: function (result) {
                        submitDeductFromCreditTime(customer_id, credit_time, flight_offer_id, flight_minutes, null, false);
                    }
                }
            }
        });

        dialog.on("shown.bs.modal", onDateTimeSlotPickerDialogShown);
        dialog.modal('show');
    }

    function submitDeductFromCreditTime(customer_id, credit_time, flight_offer_id, flight_minutes, flight_time, is_new_purchasee_form) {
        var minutes = parseInt($('#txtMinutes').val());
        if (minutes !== null && minutes != '' && minutes != 0) {

            if (minutes > flight_minutes) {
                alert('Flight offer does not have ' + minutes + ' minutes. Choose another offer.');
                return false;
            }

            if (flight_offer_id == '' || flight_offer_id == 'undefined' || flight_offer_id == 'null' || flight_offer_id == 0) {
                alert('Please select Flight Offer');
                return false;
            }

            if (minutes <= credit_time) {
                $('#customerId').val(customer_id);
                $('#useBalance').val(0);
                $('#useCredit').val(1);
                $('#flightPurchaseId').val('');
                if (is_new_purchasee_form) {
                    var pickedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
                    $('#flightDate').val(pickedDate);
                    $('#flightTime').val(flight_time);
                } else {
                    $('#flightDate').val($('#bookingDate').val());
                    $('#flightTime').val($('#bookingTime').text());
                }
                $('#flightDuration').val(minutes);
                $('#flightOffer').val(flight_offer_id);

                if ($('#flightDate').val() == '') {
                    alert('Please select a date');
                    return false;
                }

                $('#formFlightTime').submit();

            } else {
                alert('Pre-Opening does not have' + minutes + ' minutes.');
                return false;
            }
        } else {
            alert('Please enter some minutes');
            return false;
        }
    }

    function deductFromBalance(duration, balance, flightOfferId, fromFlightPurchaseId) {
        var dialog = bootbox.dialog({
            title: 'Enter minutes to fly',
            show: false,
            message: getDateTimeSlotPickerHtml() + '<br/><input type="text" id="txtMinutes" placeholder="Enter minutes to fly" />',
            buttons: {
                btn1: {
                    label: 'Deduct from balance (' + balance + ')',
                    className: 'btn-success',
                    callback: function (result) {
                        var minutes = parseInt($('#txtMinutes').val());
                        balance = parseInt(balance);

                        if (minutes !== null) {
                            if (minutes <= balance) {
                                $('#flightDate').val($('#bookingDate').val());
                                $('#flightTime').val($('#bookingTime').text());
                                $('#useBalance').val(1);
                                $('#fromFlightPurchaseId').val(fromFlightPurchaseId);
                                $('#flightPurchaseId').val('');
                                $('#flightDuration').val(minutes);
                                $('#flightOffer').val(flightOfferId);

                                // for cases when we dont have the offer in select
                                if ($('#flightOffer').val() != flightOfferId) {
                                    $('#flightOffer').append('<option value="' + flightOfferId + '">Dummy</option>')
                                        .val(flightOfferId);
                                }
                                $('#formFlightTime').submit();
                            } else {
                                alert('Balance does not have ' + minutes + ' minutes.');
                                return false;
                            }
                        }
                    }
                }
            }
        });

        dialog.on("shown.bs.modal", onDateTimeSlotPickerDialogShown);
        dialog.modal('show');
    }

    function getDateTimeSlotPickerHtml() {
        return '<div> \
                <input type="date" data-date-inline-picker="true" id="bookingDate" value="" /> \
                Time: <label id="bookingTime" style="display: inline;;">00:00</label> <br/><br/> \
                <div id="datePickerInDialog"></div> \
                <div id="timeslotsInDialog"></div> \
            </div>';
    }

    function onDateTimeSlotPickerDialogShown() {
        $("#bookingDate").on('change', function (e) {
            _getTimeslots($("#bookingDate").val(), $('#flightOffer').val(), $('#txtOfferMinutes').val(), '#timeslotsInDialog');
        });

        $('#timeslotsInDialog').on('click', '.label', function (e) {
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
    }

    function reschedule(flightBookingId) {
        var dialog = bootbox.dialog({
            title: 'Reschedule Flight Time',
            show: false,
            message: getDateTimeSlotPickerHtml(),
            buttons: {
                btn1: {
                    label: 'Reschedule',
                    className: 'btn-success',
                    callback: function (result) {
                        var selectedDateTime = $("#bookingDate").val() + " " + $("#bookingTime").text();
                        var d = new Date(selectedDateTime);
                        var now = new Date();
                        if (false/*d < now*/) {
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
                                    if (response.success == 1) {
                                        var url = updateQueryStringParameter(window.location.href, 'customer_id', $('#customerId').val());
                                        url = updateQueryStringParameter(url, 'customer_name', $('#customer').val());
                                        window.location.href = url;
                                    }
                                }
                            });
                        }
                    }
                }
            }
        });

        dialog.on("shown.bs.modal", onDateTimeSlotPickerDialogShown);
        dialog.modal('show');
    }

    function cancelFlight(flightBookingId, elem) {

        if (flightBookingId == '') {
            return;
        }

        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                'call': 'cancelFlight',
                'flight_booking_id': flightBookingId,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == 1) {
                    var pickedDate = $("#datePicker").data('datepicker').getFormattedDate('yyyy-mm-dd');
                    //_getCustomerBookings($('#customerId').val(), pickedDate);
                } else {
                    alert(response.msg);
                }
            }
        });
    }

    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        }
        else {
            return uri + separator + key + "=" + value;
        }
    }

    var _onDiscountPercentChange = function (e) {
        var quantity = $(e.target).parents('tr').find('.tdQty').text();
        var totalAmount = $(e.target).parents('tr').find('.tdAmount').text();
        var discountPercent = $(e.target).find('option:selected').data('percent');
        var discountAmount = discountPercent * totalAmount / 100;
        $(e.target).parents('tr').find('.discountAmount').text('-' + discountAmount.toFixed(2));

        var transactionId = $(e.target).data('transaction-id');

        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                'call': 'saveDiscount',
                'discount': discountPercent,
                'discount_id': $(e.target).val(),
                'transaction_id': transactionId,
                'saving_flight': 1
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == 1) {
                    window.location.href = window.location.href;
                } else {
                    alert(response.msg);
                }
            }
        });
    };

    $('.discountPercent').on('change', _onDiscountPercentChange);


</script>

<?php include('footer.php'); ?>
</html>

