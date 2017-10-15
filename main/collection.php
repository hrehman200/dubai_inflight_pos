<html>
<head>
    <?php
    require_once('../connect.php');
    ?>
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

    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>

</head>
<body>
<?php
include('navfixed.php');
?>


<div class="container-fluid">
    <div class="row-fluid">
        <div class="span2">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <?php
                    include "side-menu.php";
                    ?>
            </div>
        </div><!--/span-->
        <div class="span10">
            <div class="contentheader">
                <i class="icon-bar-chart"></i> Collection Report
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Collection Report</li>
            </ul>

            <div>
                <div style="margin-top: -19px; margin-bottom: 21px;">
                    <a href="index.php">
                        <button class="btn btn-default btn-large" style="float: none;"><i
                                class="icon icon-circle-arrow-left icon-large"></i> Back
                        </button>
                    </a>
                </div>
                <form action="collection.php" method="get">
                    From : <input type="text" name="d1" style="width: 223px; padding:14px;" class="tcal" value=""/> To:
                    <input type="text" style="width: 223px; padding:14px;" name="d2" class="tcal" value=""/>
                    <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;" type="submit"><i
                            class="icon icon-search icon-large"></i> Search
                    </button>
                    <button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-info btn-large" onclick="convertToCSV()"><i
                                class="icon icon-columns icon-large"></i> Export</button>
                    <button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-success btn-large"><a href="javascript:Clickheretoprint()"><i
                                class="icon icon-print icon-large"></i> Print</a></button>
                </form>

                <?php

                if(isset($_GET['d1'])  && $_GET['d1'] != '' && $_GET['d1'] != '0'){
                    $_GET['d1'] = date('Y-m-d', strtotime($_GET['d1']));
                } else {
                    $_GET['d1'] = date('Y-m-01');
                }

                if(isset($_GET['d2']) && $_GET['d2'] != '' && $_GET['d2'] != '0'){
                    $_GET['d2'] = date('Y-m-d', strtotime($_GET['d2']));
                } else {
                    $_GET['d2'] = date('Y-m-t');
                }
                ?>

                <div class="content" id="content">
                    <div style="font-weight:bold; text-align:center;font-size:14px;margin-bottom: 15px;">
                        Collection Report from&nbsp;<?php echo $_GET['d1'] ?>&nbsp;to&nbsp;<?php echo $_GET['d2'] ?>
                    </div>
                    <table class="table table-striped" style="background-color: white;" id="tblCollection">
                        <tr>
                            <th>Transaction Date</th>
                            <th>Operator Name</th>
                            <th>TRX_CLASS Invoice No.</th>
                            <th>TRX_TYPE</th>
                            <th>Customer Name</th>
                            <th>Mode of Payment</th>
                            <th>LINE_NUMBER</th>
                            <th>Item Code</th>
                            <th>Item Description</th>
                            <th>CURRENCY</th>
                            <th>Total</th>
                            <th>1st Mode of Payment</th>
                            <th>Paid by 1st MOP</th>
                            <th>2nd Mode of Payment</th>
                            <th>Paid By 2nd MOP</th>
                            <th>QUANTITY</th>
                            <th>UNIT_PRICE_Sold</th>
                            <th>Discount</th>
                            <th>DiscountReason</th>
                            <th>Unit Price Before Discount</th>
                            <th>OPERATING_UNIT_NAME</th>
                            <th>Store / Location</th>
                            <th>N/A</th>
                            <th>INV_Transaction Type</th>
                            <th>INV Source Document Number</th>
                            <th>Item Code</th>
                            <th>Location</th>
                            <th>Unit Consumed</th>
                            <th>Units Remaining</th>
                            <th>Revenue On Consumed</th>
                            <th>Amount Laibility</th>
                        </tr>

                        <?php
                        $sql = "SELECT
                          *
                        FROM
                          (
                            (
                            SELECT
                              GROUP_CONCAT(so.name) AS product_name,
                              GROUP_CONCAT(so.product_code) AS product_codes,
                              GROUP_CONCAT(so.qty) AS quantity,
                              s.date AS transaction_date,
                              s.cashier,
                              s.invoice_number,
                              s.sale_type,
                              s.mode_of_payment,
                              s.mop_amount,
                              s.mode_of_payment_1,
                              s.mop1_amount,
                              s.amount,
                              s.discount,
                              s.after_dis,
                              c.customer_name,
                              so.product_code,
                              GROUP_CONCAT(so.price) AS price,
                              so.name,
                              so.qty,
                              0 AS units_remaining,
                              0 AS amount_liability
                            FROM
                              sales s
                            INNER JOIN
                              sales_order so ON s.invoice_number = so.invoice
                            LEFT JOIN
                              customer c ON s.customer_id = c.customer_id
                            GROUP BY
                              s.transaction_id
                          )
                        UNION
                          (
                          SELECT
                            GROUP_CONCAT(fo1.offer_name) AS product_name,
                            GROUP_CONCAT(fo1.code) AS product_codes,
                            SUM(fb1.duration) AS quantity,
                            s1.date AS transaction_date,
                            s1.cashier,
                            s1.invoice_number,
                            s1.sale_type,
                            s1.mode_of_payment,
                            s1.mop_amount,
                            s1.mode_of_payment_1,
                            s1.mop1_amount,
                            s1.amount,
                            s1.discount,
                            s1.after_dis,
                            c1.customer_name,
                            fo1.code AS product_code,
                            fo1.price/fo1.duration AS price,
                            fo1.offer_name,
                            SUM(fo1.duration) AS qty,
                            0 AS units_remaining,
                            0 AS amount_liability
                          FROM
                            sales s1
                          INNER JOIN
                            flight_purchases fp1 ON s1.invoice_number = fp1.invoice_id
                          INNER JOIN
                            flight_offers fo1 ON fp1.flight_offer_id = fo1.id
                          INNER JOIN
                            flight_bookings fb1 ON fb1.flight_purchase_id = fp1.id
                          INNER JOIN
                            customer c1 ON s1.customer_id = c1.customer_id
                          GROUP BY
                            s1.transaction_id
                        )
                          ) result
                        WHERE result.transaction_date >= :startDate AND result.transaction_date <= :endDate
                        ORDER BY
                          result.transaction_date DESC";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        while ($row = $result->fetch()) {
                            $arr_unit_consumed = explode(",", $row['quantity']);
                            $arr_unit_remaining = [];
                            foreach($arr_unit_consumed as $uc) {
                                $arr_unit_remaining[] = $uc;
                            }

                            $price_paid = round($row['amount'] - ($row['amount'] * $row['discount'] / 100), 0);

                            $arr_price = explode(",", $row['price']);
                            $unit_price_after_discount = [];
                            foreach($arr_price as $price) {
                                $unit_price_after_discount[] = round($price - ($price * $row['discount'] / 100), 2);
                            }

                            ?>
                            <tr>
                                <td><?= $row['transaction_date'] ?></td>
                                <td><?= $row['cashier'] ?></td>
                                <td><?= $row['invoice_number'] ?></td>
                                <td><?= $row['sale_type'] ?></td>
                                <td><?= $row['customer_name'] ?></td>
                                <td><?= $row['mode_of_payment'] . (($row['mode_of_payment_1'] != -1)?", ".$row['mode_of_payment_1']:'') ?></td>
                                <td><?= $row['product_name'] ?></td>
                                <td><?= $row['product_codes'] ?></td>
                                <td><?= $row['product_name'] ?></td>
                                <td>AED</td>
                                <td><?= $price_paid?></td>
                                <td><?= $row['mode_of_payment'] ?></td>
                                <td><?= $row['mop_amount'] ?></td>
                                <td><?= $row['mode_of_payment_1'] ?></td>
                                <td><?= $row['mop1_amount'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= implode(",", $unit_price_after_discount)?></td>
                                <td><?= $row['discount'] ?></td>
                                <td>Discount Reason</td>
                                <td><?= $row['price'] ?></td>
                                <td>Inflight Dubai</td>
                                <td>Margham Dubai</td>
                                <td>N/A</td>
                                <td>Sales</td>
                                <td>-</td>
                                <td><?= $row['product_name'] ?></td>
                                <td>POS</td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= ($row['sale_type'] != 'Merchandise') ? implode(",", $arr_unit_remaining) : 0?></td>
                                <td><?php
                                    $revenue_consumed = 0;
                                    for($i=0; $i<count($unit_price_after_discount); $i++) {
                                        $revenue_consumed += round($unit_price_after_discount[$i] * $arr_unit_consumed[$i], 2);
                                    }
                                    echo $revenue_consumed;
                                    ?>
                                </td>
                                <td><?php
                                    if($row['sale_type'] != 'Merchandise') {
                                        $liability = 0;
                                        for($i=0; $i<count($unit_price_after_discount); $i++) {
                                            $liability += round($unit_price_after_discount[$i] * $arr_unit_remaining[$i], 2);
                                        }
                                        echo $liability;
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>

                    </table>
                </div>
                <div class="clearfix"></div>
            </div>
</body>
<?php include('footer.php'); ?>

</html>

<script type="text/javascript">

    function convertToCSV() {
        exportTableToCSV($('#tblCollection'), 'collection.csv');
    }

    function exportTableToCSV($table, filename) {

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