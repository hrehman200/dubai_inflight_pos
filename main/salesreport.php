<html>
<?php
require_once('auth.php');
?>
<head>
    <title>
        POS
    </title>
    <link href="css/bootstrap.css" rel="stylesheet">

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
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">


    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="tcal.css"/>
    <script type="text/javascript" src="tcal.js"></script>
    <script language="javascript">
        function Clickheretoprint() {
            var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
            disp_setting += "scrollbars=yes,width=700, height=400, left=100, top=25";
            var content_vlue = document.getElementById("content").innerHTML;

            var docprint = window.open("", "", disp_setting);
            docprint.document.open();
            docprint.document.write('</head><body onLoad="self.print()" style="width: 700px; font-size:11px; font-family:arial; font-weight:normal;">');
            docprint.document.write(content_vlue);
            docprint.document.close();
            docprint.focus();
        }
    </script>


    <script language="javascript" type="text/javascript">
        /* Visit http://www.yaldex.com/ for full source code
         and get more free JavaScript, CSS and DHTML scripts! */
        <
        !--Begin
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
        // End -->
    </SCRIPT>
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
            <div class="contentheader">
                <i class="icon-bar-chart"></i> Sales Report
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Sales Report</li>
            </ul>

            <div style="margin-top: -19px; margin-bottom: 21px;">
                <a href="index.php">
                    <button class="btn btn-default btn-large" style="float: none;"><i
                            class="icon icon-circle-arrow-left icon-large"></i> Back
                    </button>
                </a>
                <button style="float:right;" class="btn btn-success btn-mini"><a href="javascript:Clickheretoprint()">
                        Print</button>
                </a> <br><br>
                <button style="float:right;" class="btn btn-success btn-mini" onclick="convertToCSV()" id="exportCSV"/>
                <i class="icon-plus-sign icon-large"></i> Export </button>
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
                    $dt = DateTime::createFromFormat('m/d/Y', $d1);
                } else {
                    $dt = new DateTime('first day of this month');
                }
                $d1 = $dt->format('Y-m-d');

                if(strlen($d2) > 0 && $d2 != 0) {
                    $dt = DateTime::createFromFormat('m/d/Y', $d2);
                } else {
                    $dt = new DateTime('last day of this month');
                }
                $d2 = $dt->format('Y-m-d');
                ?>

                <div style="font-weight:bold; text-align:center;font-size:14px;margin-bottom: 15px;">
                    Sales Report from&nbsp;<?php echo $d1 ?>&nbsp;to&nbsp;<?php echo $d2 ?>
                </div>
                <table class="table table-bordered" id="resultTable" data-responsive="table" style="text-align: left;">
                    <thead>
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
                    
                    $sql .= " ORDER by transaction_id DESC";

                    $result = $db->prepare($sql);
                    $result->bindParam(':a', $d1);
                    $result->bindParam(':b', $d2);
                    $result->execute();
                    $total_sale   = 0;
                    $total_profit = 0;

                    for ($i = 0; $row = $result->fetch(); $i++) {
                        $current_cost = $row['amount'];
                        $discount = $current_cost * $row['discount'] / 100.00;
                        //$current_cost -= $discount;

                        if ($row['sale_type'] == 'Service') {
                            $invoiceHref = 'flight_preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'];
                        } else {
                            $invoiceHref = 'preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'] . '&payfirst=&paysecond=&d1=' . $d1 . '&d2=' . $d2;
                        }
                        $total_sale += $current_cost;
                        $total_profit += $row['profit'];

                        //$invoiceHref = 'flight_preview.php?invoice='.$row['invoice_number'].'&sale_type='.$row['sale_type'];

                        ?>
                        <tr class="record">
                            <td><a href='<?php echo $invoiceHref ?>'> <?php echo $row['invoice_number']; ?></td>
                            <td>STI-00<?php echo $row['transaction_id']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['mode_of_payment']; ?></td>
                            <td><?php echo $row['mop_amount']; ?></td>

                            <td><?php echo $row['mode_of_payment_1']; ?></td>
                            <td><?php echo $row['mop1_amount']; ?></td>

                            <td><?php echo ($row['customer_name']) ? $row['customer_name'] : $row['name']; ?></td>
                            <td><?= $row['sale_type'] ?></td>
                            <td><?= number_format($current_cost, 2) ?></td>
                            <!--<td><?= number_format($row['profilt']) ?></td>-->
                        </tr>
                        <?php
                    }
                    ?>

                    </tbody>
                    <thead>
                    <tr>
                        <th colspan="6" style="border-top:1px solid #999999"> Total:</th>
                        <th colspan="1" style="border-top:1px solid #999999"><?= number_format($total_sale) ?></th>
                        <!--<th colspan="1" style="border-top:1px solid #999999"><?= number_format($total_profit) ?></th>-->
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

</body>
<script src="js/jquery.js"></script>
<script type="text/javascript">
    $(function () {

        $(".delbutton").click(function () {

//Save the link in a variable called element
            var element = $(this);

//Find the id of the link that was clicked
            var del_id = element.attr("id");

//Built a url to send
            var info = 'id=' + del_id;
            if (confirm("Sure you want to delete this update? There is NO undo!")) {

                $.ajax({
                    type: "GET",
                    url: "deletesales.php",
                    data: info,
                    success: function () {

                    }
                });
                $(this).parents(".record").animate({backgroundColor: "#fbc7c7"}, "fast")
                    .animate({opacity: "hide"}, "slow");

            }

            return false;

        });

    });
</script>
<script type="text/javascript">

    function convertToCSV() {
        exportTableToCSV($('#resultTable'), 'filename.csv');
    }
</script>


<script type="text/javascript">
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

        // $(this)
        //     .attr({
        //     'download': filename,
        //         'href': csvData,
        //         'target': '_blank'
        // });
    }
</script>
<?php include('footer.php'); ?>
</html>