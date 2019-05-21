<html>
<?php
require_once('auth.php');
?>
<head>
    <title>
        POS
    </title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
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

        @media print {
            .sidebar-nav, .navbar, .span2, .breadcrumb, .contentheader, .btns, form {
                display: none;
            }
            .span10, #tblSalesReport {
                display: block;
                width: 120%;
                margin-left: -20%;
            }
        }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet" media="all">


    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="tcal.css"/>
    <script type="text/javascript" src="tcal.js"></script>
    <script src="js/bootbox.min.js" type="text/javascript"></script>
    <script language="javascript">
        function Clickheretoprint() {
            $('#tblSalesReport').closest('.span10').removeClass()
        }
    </script>


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
            var now       = new Date();
            var hours     = now.getHours();
            var minutes   = now.getMinutes();
            var seconds   = now.getSeconds()
            var timeValue = "" + ((hours > 12) ? hours - 12 : hours)
            if (timeValue == "0") timeValue = 12;
            timeValue += ((minutes < 10) ? ":0" : ":") + minutes
            timeValue += ((seconds < 10) ? ":0" : ":") + seconds
            timeValue += (hours >= 12) ? " P.M." : " A.M."
            document.clock.face.value = timeValue;
            timerID                   = setTimeout("showtime()", 1000);
            timerRunning              = true;
        }
        function startclock() {
            stopclock();
            showtime();
        }
        window.onload = startclock;
    </SCRIPT>

    <style>
        tr:nth-child(even) {background: #FFF}
    </style>
</head>
<body>
<?php include('navfixed.php'); ?>
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
                                <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="submit"
                                                                                  class="trans" name="face" value="">
                            </form>
                        </div>
                    </li>

                </ul>
            </div><!--/.well -->
        </div><!--/span-->
        <div class="span10">
            <div class="contentheader hidden-print">
                <i class="icon-bar-chart"></i> Sales Report
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Sales Report</li>
            </ul>

            <div style="margin-top: -19px; margin-bottom: 21px;" class="btns">
                <a href="index.php" class="btn btn-default btn-large" style="float: none;">
                    <i class="icon icon-circle-arrow-left icon-large"></i> Back
                </a>
                <button style="float:right; margin-right: 5px;" class="btn btn-success btn-large" onclick="window.print()">
                    Print
                </button>
                <button style="float:right; margin-right:5px;" class="btn btn-warning btn-large" onclick="convertToCSV()" id="exportCSV"/>
                    Export
                </button>
                <button style="margin-right:5px;" class="btn btn-info btn-large pull-right" id="btnVerified">Verified</button>
                <br><br>


            </div>
            <form action="salesreport.php" method="get">
                <center><strong>From : <input type="text" style="width: 223px; padding:3px;height: 30px;" name="d1"
                                              class="tcal" value=""/>
                        To: <input type="text"
                                   style="width: 223px; padding:3px;height: 30px;"
                                   name="d2" class="tcal" value=""/>
                        <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;margin-left:8px;"
                                type="submit"><i class="icon icon-search icon-large"></i> Search
                        </button>
                    </strong></center>
            </form>
            <div class="content" id="content">

                <?php
                $d1     = $_GET['d1'];
                $d2     = $_GET['d2'];

                if(strlen($d1) > 0 && $d1 != 0) {
                    if(strpos($d1, '/') !== false) {
                        $dt = DateTime::createFromFormat('m/d/Y', $d1);
                    } else {
                        $dt = DateTime::createFromFormat('Y-m-d', $d1);
                    }
                } else {
                    $dt = new DateTime('today');
                }
                $d1 = $dt->format('Y-m-d');

                if(strlen($d2) > 0 && $d2 != 0) {
                    if(strpos($d2, '/') !== false) {
                        $dt = DateTime::createFromFormat('m/d/Y', $d2);
                    } else {
                        $dt = DateTime::createFromFormat('Y-m-d', $d2);
                    }
                } else {
                    $dt = new DateTime('today');
                }
                $d2 = $dt->format('Y-m-d');
                ?>
                <table class="table table-bordered table-striped" id="tblSalesReport">
                    <thead>
                    <tr>
                        <th colspan="10" style="text-align: center;">
                            <h3>Sales Report from&nbsp;<?php echo date('M j, Y', strtotime($d1)) ?>&nbsp;to&nbsp;<?php echo date('M j, Y', strtotime($d2)) ?></h3>
                            <?php
                            if(isset($_GET['verified'])) {
                            ?>
                                <h4>Operator Name: <?=$_SESSION['SESS_FIRST_NAME']?></h4>
                            <?php
                            }
                            ?>
                        </th>
                    </tr>
                    <tr>
                        <th width="16%"> Invoice Number</th>
                        <th width="13%"> Transaction ID</th>
                        <th width="13%"> Transaction Date</th>
                        <th width="13%"> Mode of Payment 1</th>
                        <th width="13%"> Amount 1</th>
                        <th width="13%"> Mode of Payment 2</th>
                        <th width="13%"> Amount 2</th>
                        <th width="20%"> Customer Name</th>
                        <th width="20%"> Sale Type</th>
                        <th width="18%"> Amount</th>
                        <!--<th width="13%"> Profit</th>-->
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    include_once('../connect.php');

                    $sql = "SELECT s.*, c.customer_name FROM sales s
                        LEFT JOIN customer c ON s.customer_id = c.customer_id";

                    if($_SESSION['SESS_LAST_NAME'] == 'Operator') {
                        $sql .= " INNER JOIN user u ON s.cashier = u.name AND u.position = 'Operator' ";
                    }

                    $sql .= " WHERE date >= :a AND date <= :b";
                    
                    if($_SESSION['SESS_LAST_NAME'] == 'Operator') {
                        $sql .= sprintf(" AND u.name = '%s'", $_SESSION['SESS_FIRST_NAME']);
                    }
                    
                    $sql .= " AND (c.customer_name != 'FDR' OR c.customer_name IS NULL)
                    ORDER by transaction_id DESC";

                    $result = $db->prepare($sql);
                    $result->bindParam(':a', $d1);
                    $result->bindParam(':b', $d2);
                    $result->execute();
                    $total_sale   = 0;
                    $total_profit = 0;
                    $total_cash   = 0;
                    $total_card   = 0;
                    $total_account = 0;
                    $total_online = 0;

                    for ($i = 0; $row = $result->fetch(PDO::FETCH_ASSOC); $i++) {
                        $current_cost = $row['amount'];
                        $discount = $current_cost * $row['discount'] / 100.00;

                        $query = $db->prepare('SELECT transaction_id FROM sales_order WHERE invoice = ? AND gen_name = "Service" LIMIT 1');
                        $query->execute([$row['invoice_number']]);
                        $is_service = ($query->rowCount() > 0);

                        if ($row['sale_type'] == 'Merchandise' || $is_service) {
                            $invoiceHref = 'preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'] . '&payfirst=&paysecond=&d1=' . $d1 . '&d2=' . $d2;
                        } else {
                            $invoiceHref = 'flight_preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'];
                        }
                        $total_profit += $row['profit'];

                        if($row['mode_of_payment'] == 'Cash') {
                            $total_cash += $row['mop_amount'];
                            $total_sale += $row['mop_amount'];
                        }
                        if($row['mode_of_payment_1'] == 'Cash') {
                            $total_cash += $row['mop1_amount'];
                            $total_sale += $row['mop1_amount'];
                        }

                        if($row['mode_of_payment'] == 'Card') {
                            $total_card += $row['mop_amount'];
                            $total_sale += $row['mop_amount'];
                        }
                        if($row['mode_of_payment_1'] == 'Card') {
                            $total_card += $row['mop1_amount'];
                            $total_sale += $row['mop1_amount'];
                        }

                        if($row['mode_of_payment'] == 'Account') {
                            $total_account += $row['mop_amount'];
                            //$total_sale += $row['mop_amount'];
                        }
                        if($row['mode_of_payment_1'] == 'Account') {
                            $total_account += $row['mop1_amount'];
                            //$total_sale += $row['mop1_amount'];
                        }

                        if(strtolower($_SESSION['SESS_LAST_NAME']) == 'admin' || $_SESSION[SESS_MOCK_ROLE] == 'admin' ||
                            strtolower($_SESSION['SESS_LAST_NAME']) == 'account' || $_SESSION[SESS_MOCK_ROLE] == ROLE_ACCOUNT) {
                            if ($row['mode_of_payment'] == 'Online') {
                                $total_online += $row['mop_amount'];
                                $total_sale += $row['mop_amount'];
                            }
                            if ($row['mode_of_payment_1'] == 'Online') {
                                $total_online += $row['mop1_amount'];
                                $total_sale += $row['mop1_amount'];
                            }
                        }

                        ?>
                        <tr>
                            <td><a href='<?php echo $invoiceHref ?>'> <?php echo $row['invoice_number']; ?></td>
                            <td>STI-00<?php echo $row['transaction_id']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['mode_of_payment']; ?></td>
                            <td><?php echo $row['mop_amount']; ?></td>

                            <td><?php echo $row['mode_of_payment_1']; ?></td>
                            <td><?php echo $row['mop1_amount']; ?></td>

                            <td><?php echo ($row['customer_name']) ? $row['customer_name'] : $row['name']; ?></td>
                            <td><?= $row['sale_type'] ?></td>
                            <td><?= number_format($current_cost, 0) ?></td>
                            <!--<td><?= number_format($row['profilt']) ?></td>-->
                        </tr>
                        <?php
                    }
                    ?>

                    <tr>
                        <td colspan="9" style="text-align: right;"> <b>Total:</b></td>
                        <td colspan="1" style=""><b><?= number_format($total_sale, 0) ?></b></td>
                    </tr>
                    <tr>
                        <td colspan="9" style="text-align: right;"> <b>Cash:</b></td>
                        <td colspan="1" style=""><b><?= number_format($total_cash, 0) ?></b></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"> <b>Account:</b></td>
                        <td style=""><b><?= number_format($total_account, 0) ?></b></td>
                        <td colspan="7" style="text-align: right;"> <b>Card:</b></td>
                        <td colspan="1" style=""><b><?= number_format($total_card, 0) ?></b></td>
                    </tr>
                    <?php
                    if(strtolower($_SESSION['SESS_LAST_NAME']) == 'admin' || $_SESSION[SESS_MOCK_ROLE] == 'admin' ||
                        strtolower($_SESSION['SESS_LAST_NAME']) == 'account' || $_SESSION[SESS_MOCK_ROLE] == ROLE_ACCOUNT) {
                        ?>
                        <tr>
                            <td colspan="9" style="text-align: right;"><b>Online:</b></td>
                            <td colspan="1" style=""><b><?= number_format($total_online, 0) ?></b></td>
                        </tr>
                        <?php
                    }
                    ?>

                    <tr>
                        <td colspan="9" style="text-align: right;"><b>Souq:</b></td>
                        <td colspan="1" style="padding-top:10px;">__________________</td>
                    </tr>

                    <?php
                    if($_SESSION['SESS_LAST_NAME'] == 'Operator') {
                        ?>
                        <tr>
                            <td colspan="9" style="text-align: right;"><b>Operator:</b></td>
                            <td colspan="1" style=""><b><?= $_SESSION['SESS_FIRST_NAME'] ?></b></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan="9" style="text-align: right;"><b>Signature:</b></td>
                        <td colspan="1" style="padding-top:50px;">__________________</td>
                    </tr>

                    </tbody>

                </table>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

</body>
<script type="text/javascript">

    $('#btnVerified').on('click', function(e) {
        bootbox.prompt({
            title: "Select manager to email verified sales report:<br/><hr/> ",
            inputType: 'select',
            inputOptions: [
                {
                    text: 'Select...',
                    value: '',
                },
                {
                    text: 'Shah',
                    value: '18',
                },
                {
                    text: 'Freedy',
                    value: '16',
                },
                {
                    text: 'Mark',
                    value: '20',
                },
                {
                    text: 'Marija',
                    value: '15',
                }
            ],
            callback: function (userId) {
                $('.loading-gif').show();
                if (userId > 0) {
                    window.open('salesreport.php?verified=1&user_id='+userId);
                }
            }
        });
    });

    <?php
    if(isset($_GET['verified'])) {
        ?>
        $('#tblSalesReport').css('border-collapse', 'collapse');
        $('#tblSalesReport, #tblSalesReport th, #tblSalesReport td')
            .css('border', '1px solid grey');

        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                'call': 'emailSalesReportToManager',
                'userId': <?=$_GET['user_id']?>,
                'tableHtml': $('#tblSalesReport').parent().html()
            },
            dataType: 'json',
            success: function (response) {
                $('.loading-gif').hide();
                if (response.success == 1) {
                    alert(response.msg);
                }
            }
        });
    <?php
    }
    ?>


    function convertToCSV() {
        exportTableToCSV($('#tblSalesReport'), 'filename.csv');
    }

    function exportTableToCSV($table, filename) {

        // var $rows = $table.find('tr:has(td)'),

        var $rows       = $table.find('tr:has(td,th)'),

            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

            // actual delimiter characters for CSV format
            colDelim    = '","',
            rowDelim    = '"\r\n"',

            // Grab text from table into CSV formatted string
            csv         = '"' + $rows.map(function (i, row) {
                    var $row  = $(row),
                        // $cols = $row.find('td');
                        $cols = $row.find('td,th');

                    return $cols.map(function (j, col) {
                        var $col = $(col),
                            text = $col.text();

                        return text.replace('"', '""'); // escape double quotes

                    }).get().join(tmpColDelim);

                }).get().join(tmpRowDelim)
                    .split(tmpRowDelim).join(rowDelim)
                    .split(tmpColDelim).join(colDelim) + '"',

            // Data URI
            csvData     = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        blob       = new Blob([csvData], {type: 'text/csv;charset=utf8;'}); //new way
        var csvUrl = URL.createObjectURL(blob);

        $(this)
            .attr({
                'download': filename,
                'href': csvData,
                'target': '_blank'
            });

        var link = document.createElement("a");

        if (link.download !== undefined) { // feature detection
            // Browsers that support HTML5 download attribute
            link.setAttribute("href", csvData);
            link.setAttribute("download", filename);
            link.click();
        } else {
            alert('CSV export only works in Chrome, Firefox, and Opera.');
        }
    }
</script>
<?php include('footer.php'); ?>
</html>