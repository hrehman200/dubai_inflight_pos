<html>
<head>
    <?php
    require_once('../connect.php');
    set_time_limit(180);
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
                <form action="collection_other.php" method="get">
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

                    <table class="table table-striped">
                        <tr>
                            <th>Package</th>
                            <th>Paid</th>
                            <th>Total Minutes</th>
                            <th>Minutes Used</th>
                            <th>Minutes Liability</th>
                        </tr>
                        <?php
                        $sql = "SELECT 
                                'FTF' AS package_name,
                                SUM(s1.mop_amount+s1.mop1_amount) AS paid,
                                SUM(fb1.duration) AS minutes_used,
                                SUM(fo1.duration) AS total_minutes
                                  FROM
                                    sales s1
                                  INNER JOIN
                                    flight_purchases fp1 ON s1.invoice_number = fp1.invoice_id
                                  INNER JOIN
                                    flight_offers fo1 ON fp1.flight_offer_id = fo1.id
                                  INNER JOIN 
                                    flight_packages fpkg ON fo1.package_id = fpkg.id
                                  LEFT JOIN
                                    flight_bookings fb1 ON fb1.flight_purchase_id = fp1.id
                                  INNER JOIN 
                                    customer c ON fp1.customer_id = c.customer_id  
                                WHERE fpkg.package_name LIKE 'FTF%'
                                  AND (s1.mode_of_payment IN ('Cash', 'Card', 'Online', 'Account', 'credit_time', 'credit_cash') 
                                       OR 
                                       s1.mode_of_payment_1 IN ('Cash', 'Card', 'Online', 'Account', 'credit_time', 'credit_cash'))
                                  AND (s1.date >= :startDate AND s1.date <= :endDate)  
                                  AND (c.customer_name != 'FDR' OR c.customer_name IS NULL)
                                GROUP BY package_name";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        $arr = $result->fetchAll();
                        if(count($arr) > 1) {
                            for ($i=1; $i<count($arr); $i++) {
                                $arr[0]['paid'] += $arr[$i]['paid'];
                                $arr[0]['minutes_used'] += $arr[$i]['minutes_used'];
                                $arr[0]['total_minutes'] += $arr[$i]['total_minutes'];
                            }

                            array_splice($arr, 1);
                        }

                        $sql = "SELECT 
                                fpkg.package_name,
                                SUM(s1.mop_amount+s1.mop1_amount) AS paid,
                                SUM(fb1.duration) AS minutes_used,
                                SUM(fo1.duration) AS total_minutes
                                  FROM
                                    sales s1
                                  INNER JOIN
                                    flight_purchases fp1 ON s1.invoice_number = fp1.invoice_id
                                  INNER JOIN
                                    flight_offers fo1 ON fp1.flight_offer_id = fo1.id
                                  INNER JOIN 
                                    flight_packages fpkg ON fo1.package_id = fpkg.id
                                  LEFT JOIN
                                    flight_bookings fb1 ON fb1.flight_purchase_id = fp1.id
                                  INNER JOIN 
                                    customer c ON fp1.customer_id = c.customer_id   
                                WHERE fpkg.id IN (6, 8)
                                  AND (s1.mode_of_payment IN ('Cash', 'Card', 'Online', 'Account', 'credit_time', 'credit_cash') 
                                       OR 
                                       s1.mode_of_payment_1 IN ('Cash', 'Card', 'Online', 'Account', 'credit_time', 'credit_cash'))
                                  AND (s1.date >= :startDate AND s1.date <= :endDate)  
                                  AND (c.customer_name != 'FDR' OR c.customer_name IS NULL)
                                GROUP BY fpkg.id";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        $arr = array_merge($arr, $result->fetchAll());

                        foreach ($arr as $row) {
                            ?>
                            <tr>
                                <td><b><?= $row['package_name'] ?></b></td>
                                <td><?= number_format($row['paid'], 1) ?></td>
                                <td><?= number_format($row['total_minutes']) ?></td>
                                <td><?= number_format($row['minutes_used']) ?></td>
                                <td><?= number_format($row['total_minutes'] - $row['minutes_used']) ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td><b>Total Credit Time</b></td>
                            <td colspan="4">
                        </tr>
                    </table>

                    <hr/>

                    <table class="table table-striped" style="background-color: white;" id="tblCollection">
                        <?php
                        $sql = "SELECT
                          *
                        FROM
                          (
                            (
                            SELECT so.name AS product_name,
                              so.product_code AS product_codes,
                              so.qty AS quantity,
                              0 AS deduct_from_balance,
                              0 AS from_flight_purchase_id,
                              vc.percent AS vat_percent,
                              so.qty AS total_quantity,
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
                              so.price AS unit_price,
                              so.name,
                              so.qty,
                              0 AS units_remaining,
                              0 AS amount_liability,
                              d.category AS discount_reason
                            FROM
                              sales s
                            INNER JOIN
                              sales_order so ON s.invoice_number = so.invoice
                            LEFT JOIN
                              vat_codes vc ON so.vat_code_id = vc.id
                            LEFT JOIN
                              discounts d ON so.discount_id = d.id
                            LEFT JOIN
                              customer c ON s.customer_id = c.customer_id
                          )
                        UNION ALL
                          (
                          SELECT fo1.offer_name AS product_name,
                            fo1.code AS product_codes,
                            fb1.duration AS quantity,
                            fp1.deduct_from_balance,
                            fb1.from_flight_purchase_id,
                            vc.percent AS vat_percent,
                            fo1.duration AS total_quantity,
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
                            fo1.price / fo1.duration AS unit_price,
                            fo1.offer_name,
                            fo1.duration AS qty,
                            fo1.duration - fb1.duration AS units_remaining,
                            0 AS amount_liability,
                            d.category AS discount_reason
                          FROM
                            sales s1
                          INNER JOIN
                            flight_purchases fp1 ON s1.invoice_number = fp1.invoice_id
                          INNER JOIN
                            flight_offers fo1 ON fp1.flight_offer_id = fo1.id
                          LEFT JOIN
                            flight_bookings fb1 ON fb1.flight_purchase_id = fp1.id
                          LEFT JOIN
                            vat_codes vc ON fp1.vat_code_id = vc.id
                          LEFT JOIN
                            discounts d on fp1.discount_id = d.id
                          INNER JOIN
                            customer c1 ON s1.customer_id = c1.customer_id
                        )
                          ) result
                        WHERE result.transaction_date >= :startDate AND result.transaction_date <= :endDate
                        AND (result.customer_name != 'FDR' OR result.customer_name IS NULL)
                        ORDER BY
                          result.transaction_date DESC, result.invoice_number";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        ?>
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
                            <th>VAT(%)</th>
                            <th>VAT(Amount)</th>
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
                        $prev_invoice_no = '';
                        $arr = $result->fetchAll();
                        foreach ($arr as $row) {

                            $price_paid = $row['amount'];

                            if($row['deduct_from_balance'] == 1 && $row['from_flight_purchase_id'] > 0) {
                                $remaining_units = getRemainingMinutesOfFlightPurchase($row['from_flight_purchase_id']);
                            } else {
                                $remaining_units = $row['total_quantity'] - $row['quantity'];
                            }

                            ?>
                            <tr>
                                <td><?= $row['transaction_date'] ?></td>
                                <td><?= $row['cashier'] ?></td>
                                <td><?= $row['invoice_number'] ?></td>
                                <td><?= $row['sale_type'] ?></td>
                                <td><?= $row['customer_name'] ?></td>
                                <td><?= $row['mode_of_payment'] . (($row['mode_of_payment_1'] != -1)?", ".$row['mode_of_payment_1']:'') ?></td>
                                <td><?php
                                    $temp = array_filter($arr, function($v) use ($row) {
                                        return $v['invoice_number'] == $row['invoice_number'];
                                    });
                                    echo count($temp);
                                    ?></td>
                                <td><?= $row['product_codes'] ?></td>
                                <td><?= $row['product_name'] ?></td>
                                <td>AED</td>
                                <td><?=$price_paid?></td>
                                <td><?= $row['mode_of_payment'] ?></td>
                                <td><?= $row['mop_amount'] ?></td>
                                <td><?= $row['mode_of_payment_1'] ?></td>
                                <td><?= $row['mop1_amount'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?php
                                    $unit_price = round($row['unit_price'], 2);
                                    $discount_value = round($unit_price * $row['discount'] / 100, 2);
                                    $unit_price_after_discount = $unit_price - $discount_value;
                                    echo $unit_price_after_discount;
                                    ?></td>

                                <td><?php
                                    $line_item_discount = $discount_value * ($row['quantity'] + $row['units_remaining']) ;
                                    echo $line_item_discount;
                                    /*$discount_value = round($row['amount'] * $row['discount'] / 100, 2);
                                    echo $row['discount'].'%';*/
                                    ?></td>
                                <td><?php
                                    if($row['sale_type'] == 'Merchandise') {
                                        $result2 = $db->prepare('SELECT COUNT(transaction_id) AS line_items FROM sales_order WHERE invoice = ?');
                                    } else {
                                        $result2 = $db->prepare('SELECT COUNT(id) AS line_items FROM flight_purchases WHERE invoice_id = ?');
                                    }
                                    $result2->execute(array($row['invoice_number']));
                                    $row2 = $result2->fetch();
                                    //echo $row2['line_items'];

                                    echo $row['discount_reason'];
                                    ?></td>
                                <td><?=round($row['unit_price'], 2)?></td>
                                <td><?= $row['vat_percent'].'%' ?></td>
                                <td><?
                                    $line_price = ($row['quantity'] + $row['units_remaining']) * $unit_price_after_discount;
                                    $vat_value = $line_price * $row['vat_percent'] / 100;
                                    echo $vat_value;
                                    ?></td>
                                <td>Inflight Dubai</td>
                                <td>Inflight Dubai</td>
                                <td>N/A</td>
                                <td>Sales</td>
                                <td>-</td>
                                <td><?= $row['product_code'] ?></td>
                                <td>POS</td>
                                <td><?=$row['quantity']?></td>
                                <td><?=$remaining_units ?></td>
                                <td><?php
                                    echo $unit_price_after_discount * $row['quantity'];
                                    ?>
                                </td>
                                <td><?php
                                    if($row['sale_type'] == 'Merchandise') {
                                        echo 0;
                                    } else {
                                        echo $unit_price_after_discount * ($remaining_units);
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