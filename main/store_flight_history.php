<html>
<?php
include_once('../connect.php');
session_start();
?>
<head>
    <title>
        POS
    </title>
    <link href="css/bootstrap_dark.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">


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

    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>

    <link rel="stylesheet" type="text/css" href="css/bootstrap-datepicker.standalone.css" />
    <script src="js/bootstrap-datepicker.min.js" type="text/javascript"></script>

</head>
<body>
<?php include('store_top_nav.php'); ?>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="contentheader">
                <i class="icon-bar-chart"></i> Flight History
            </div>

            <div style="margin-top:50px;">

                <button class="btn btn-default btn-mini" style="float: none;" onclick="javascript:history.back();"><i
                        class="icon icon-circle-arrow-left icon-large"></i> Back
                </button>
                <!--<button style="float:right;" class="btn btn-danger btn-mini" onclick="javascript:Clickheretoprint();">
                        <i class="icon-print icon-large"></i> Print
                </button>
                <button style="float:right;" class="btn btn-danger btn-mini" onclick="convertToCSV()" id="exportCSV">
                <i class="icon-plus-sign icon-large"></i> Export </button>-->
                <br><br>


            </div>
            <form action="store_flight_history.php" method="get">
                <div>
                    <strong>
                        <input type="hidden" id="customerId" name="customerId" value="<?=$_SESSION['CUSTOMER_ID']?>" />

                        From :
                        <input type="text" style="width: 223px; padding:3px;height: 30px;" id="startDate" name="startDate" value="<?=$_GET['startDate']?>"/>
                        To:
                        <input type="text" style="width: 223px; padding:3px;height: 30px;" id="endDate" name="endDate" value="<?=$_GET['endDate']?>"/>

                        <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;margin-left:8px;"
                                type="submit"><i class="icon icon-search icon-large"></i> Search
                        </button>
                    </strong>
                </div>
            </form>
            <div class="content" id="content">

                <table class="table table-bordered table-striped" data-responsive="table">
                    <thead>
                    <tr>
                        <th> Invoice No.</th>
                        <th> Customer</th>
                        <th> Package</th>
                        <th> Flight Offer</th>
                        <th> Price</th>
                        <th> Paid</th>
                        <th> Minutes</th>
                        <th> Purchase Date </th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php

                    $sql = "SELECT fp.id AS flight_purchase_id, fp.deduct_from_balance, fo.code, fpkg.package_name, fo.offer_name, fp.price, fo.duration, c.customer_name, DATE_FORMAT(fp.created,'%b %d, %Y') AS created,
                          fb.duration AS booking_duration,
                          s.after_dis,
                          s.invoice_number
                          FROM flight_purchases fp
                          LEFT JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                          LEFT JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                          LEFT JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
                          INNER JOIN sales s ON fp.invoice_id = s.invoice_number
                          INNER JOIN customer c ON fp.customer_id = c.customer_id ";

                    $where = array();

                    if ($_GET['startDate'] != '' && $_GET['endDate'] != '') {
                        $where[] = sprintf("(
                            (DATE(fp.created) >= '%s' AND DATE(fp.created) <= '%s')
                                OR
                            (DATE(fb.flight_time) >= '%s' AND DATE(fb.flight_time) <= '%s'))",
                            $_GET['startDate'], $_GET['endDate'], $_GET['startDate'], $_GET['endDate']);
                    }

                    if ($_SESSION['CUSTOMER_ID'] > 0) {
                        $where[] = sprintf('fp.customer_id = %d', $_SESSION['CUSTOMER_ID']);
                    }

                    if (count($where) > 0) {
                        $sql .= ' WHERE ' . implode(" AND ", $where);
                    }

                    $sql .= ' GROUP BY fp.id';

                    //print_r($sql);
                    //exit();

                    $result = $db->query($sql);

                    $total_cost     = 0;
                    $total_paid = 0;
                    $total_duration = 0;
                    while ($row = $result->fetch()) {
                        if($row['deduct_from_balance']==0) {
                            $total_cost += $row['price'];
                            $total_duration += $row['duration'];
                            $total_paid += $row['after_dis'];
                        }
                        ?>
                        <tr>
                            <td><a href="flight_preview.php?invoice=<?=$row['invoice_number']?>" target="_blank"><?=$row['invoice_number']?></a></td>
                            <td><?php echo $row['customer_name']; ?></td>
                            <td><?php echo $row['package_name']; ?></td>
                            <td><?php echo $row['deduct_from_balance']>0 ? $row['offer_name'].' (Deduct from balance)' : $row['offer_name'] ; ?></td>
                            <td><?php echo $row['deduct_from_balance']>0 ? '-' : number_format($row['price']); ?></td>
                            <td><?=$row['after_dis']?></td>
                            <td><?php echo $row['deduct_from_balance']>0 ? $row['booking_duration'] :$row['duration']; ?></td>
                            <td><?= $row['created'] ?></td>
                        </tr>

                        <?php
                        $query2 = $db->prepare('SELECT * FROM flight_bookings WHERE flight_purchase_id = :flight_purchase_id');
                        $query2->bindParam(':flight_purchase_id', $row['flight_purchase_id']);
                        $query2->execute();
                        while ($row2 = $query2->fetch()) {
                            ?>
                            <tr>
                                <td colspan="3"></td>
                                <td style="text-align: center; font-size:12px;"><b>Flight time: </b><?= substr($row2['flight_time'], 0, -3) ?></td>
                                <td></td>
                                <td><?= $row2['duration'] ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <?php
                        }
                        ?>

                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: right;">Totals:</td>
                        <td><b></b><?= number_format($total_cost) ?></b></td>
                        <td><b><?=number_format($total_paid)?></b></td>
                        <td colspan="3"><b><?= $total_duration ?></b></td>
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

    $("#startDate").datepicker({
        format: 'yyyy-mm-dd'
    });

    $("#endDate").datepicker({
        format: 'yyyy-mm-dd'
    });

    function convertToCSV() {
        exportTableToCSV($('#resultTable'), 'filename.csv');
    }

    function exportTableToCSV($table, filename) {

        // var $rows = $table.find('tr:has(td)'),

        var $rows = $table.find('tr:has(td,th)'),

        // Temporary delimiter characters unlikely to be typed by keyboard
        // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

        // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',

        // Grab text from table into CSV formatted string
            csv = '"' + $rows.map(function (i, row) {
                    var $row = $(row),
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
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        blob = new Blob([csvData], {type: 'text/csv;charset=utf8;'}); //new way
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
<?php include('store_footer.php'); ?>
</html>