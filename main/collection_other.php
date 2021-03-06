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
                            <!--<th>Paid</th>-->
                            <th>Total Minutes</th>
                            <th>Minutes Used</th>
                            <!--<th>Minutes Liability</th>-->
                        </tr>
                        <?php
                        /** FTF */
                        $arr_ftf = getDataAndAggregate('FTF', $_GET['d1'], $_GET['d2']);

                        /** UP-Sale */
                        $arr2 = getDataAndAggregate('UP-Sale', $_GET['d1'], $_GET['d2']);
                        //$arr_ftf = array_merge($arr_ftf, $arr2);

                        $arr_ftf[0] = [
                            'package_name' => 'FTF',
                            'paid' => array_sum(array_column($arr_ftf, 'paid')),
                            'total_minutes' => array_sum(array_column($arr_ftf, 'total_minutes')),
                            'minutes_used' => array_sum(array_column($arr_ftf, 'minutes_used')),
                            'aed_value' => array_sum(array_column($arr_ftf, 'aed_value')),
                            'avg_per_min' => array_sum(array_column($arr_ftf, 'avg_per_min')),
                        ];

                        $arr_exp_return_flyers = [];

                        /** SKYDIVERS */
                        $arr2 = getDataAndAggregate('Skydivers', $_GET['d1'], $_GET['d2']);
                        $arr_exp_return_flyers = array_merge($arr_exp_return_flyers, $arr2);

                        /** Military */
                        $arr2 = getDataAndAggregate('Military', $_GET['d1'], $_GET['d2']);
                        $arr_exp_return_flyers = array_merge($arr_exp_return_flyers, $arr2);

                        /** Navy Seal */
                        $arr2 = getDataAndAggregate('Navy Seal', $_GET['d1'], $_GET['d2']);
                        $arr_exp_return_flyers = array_merge($arr_exp_return_flyers, $arr2);

                        /** Presidential Guard */
                        $arr2 = getDataAndAggregate('Presidential Guard', $_GET['d1'], $_GET['d2']);
                        $arr_exp_return_flyers = array_merge($arr_exp_return_flyers, $arr2);

                        /** Sky god */
                        $arr2 = getDataAndAggregate('Sky god%', $_GET['d1'], $_GET['d2']);
                        $arr_exp_return_flyers = array_merge($arr_exp_return_flyers, $arr2);

                        $arr_exp_return_flyers[0] = [
                            'package_name' => 'Experienced Return Flyers',
                            'paid' => array_sum(array_column($arr_exp_return_flyers, 'paid')),
                            'total_minutes' => array_sum(array_column($arr_exp_return_flyers, 'total_minutes')),
                            'minutes_used' => array_sum(array_column($arr_exp_return_flyers, 'minutes_used')),
                            'aed_value' => array_sum(array_column($arr_exp_return_flyers, 'aed_value')),
                            'avg_per_min' => array_sum(array_column($arr_exp_return_flyers, 'avg_per_min')),
                        ];
                        ?>
                        <tr>
                            <td><?=$arr_ftf[0]['package_name']?></td>
                            <td><?= number_format($arr_ftf[0]['total_minutes']) ?></td>
                            <td><?= number_format($arr_ftf[0]['minutes_used']) ?></td>
                        </tr>
                        <tr>
                            <td><?=$arr_exp_return_flyers[0]['package_name']?></td>
                            <td><?= number_format($arr_exp_return_flyers[0]['total_minutes']) ?></td>
                            <td><?= number_format($arr_exp_return_flyers[0]['minutes_used']) ?></td>
                        </tr>
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
                              0 AS flight_time,
                              vc.percent AS vat_percent,
                              so.qty AS total_quantity,
                              s.date AS transaction_date,
                              s.cashier,
                              s.invoice_number,
                              so.gen_name AS sale_type,
                              s.mode_of_payment,
                              s.mop_amount,
                              s.mode_of_payment_1,
                              s.mop1_amount,
                              s.amount,
                              so.discount,
                              s.after_dis,
                              c.customer_name,
                              so.product_code,
                              so.price AS unit_price,
                              so.name,
                              so.qty,
                              0 AS units_remaining,
                              0 AS amount_liability,
                              d.category AS discount_reason,
                              0 AS class_people,
                              so.gen_name,
                              0 AS per_minute_cost
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
                            fb1.flight_time,
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
                            d.percent AS discount,
                            s1.after_dis,
                            c1.customer_name,
                            fo1.code AS product_code,
                            fp1.price AS unit_price,
                            fo1.offer_name,
                            fo1.duration AS qty,
                            fo1.duration - fb1.duration AS units_remaining,
                            0 AS amount_liability,
                            d.category AS discount_reason,
                            fp1.class_people,
                            'Service' AS gen_name,
                            c1.per_minute_cost
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
                        WHERE 
                          ((result.transaction_date >= :startDate AND result.transaction_date <= :endDate) OR (result.flight_time >= :startDate AND result.flight_time <= :endDate))
                        AND ((result.customer_name != 'FDR' AND customer_name != 'MAINTENANCE' AND  customer_name != 'inflight_staff_flying')   OR result.customer_name IS NULL)
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
                            <th>Line Item Amount</th>
                            <th>1st Mode of Payment</th>
                            <th>Paid by 1st MOP</th>
                            <th>2nd Mode of Payment</th>
                            <th>Paid By 2nd MOP</th>
                            <th>Quantity Total</th>
                            <th>Quantity Line Item</th>
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
                        $arr = $result->fetchAll(PDO::FETCH_ASSOC);
                        $units_consumed = 0;
                        foreach ($arr as $row) {

                            $mode_of_payment = $row['mode_of_payment'] . (($row['mode_of_payment_1'] != -1)?", ".$row['mode_of_payment_1']:'');

                            if($prev_invoice_no != $row['invoice_number']) {
                                $units_consumed = 0;
                            }
                            $prev_invoice_no = $row['invoice_number'];
                            $price_paid = $row['amount'];

                            // for cases where user purchased flight, but didn't book time to fly
                            if($row['quantity'] === null) {
                                $row['quantity'] = 0; //$row['total_quantity'];
                            }

                            $booking_duration = $row['quantity'];

                            if($row['deduct_from_balance'] == 1 && $row['from_flight_purchase_id'] > 0) {
                                $remaining_units = getRemainingMinutesOfFlightPurchase($row['from_flight_purchase_id']);

                            } else if($row['class_people'] > 0) {
                                if($row['quantity'] == 0) {
                                    $row['quantity'] = $row['class_people'];
                                } else {
                                    $remaining_units = $row['total_quantity'] - $row['quantity'];
                                }

                            } else {
                                $units_consumed += $booking_duration;
                                $remaining_units = $row['total_quantity'] - $units_consumed;
                            }

                            $is_flying_merchandise = (strpos($row['product_name'], 'Helmet Rent') !== false ||
                                $row['product_name'] == 'Rental Helmet Full Day' ||
                                strpos($row['product_name'], 'Video') !== false ||
                                strpos($row['product_name'], 'Photo') !== false
                            );

                            if($row['sale_type'] == 'Merchandise' || $is_flying_merchandise) {
                                $unit_price = round($row['unit_price'], 2);
                            } else if($row['class_people'] > 0 && $booking_duration == 0) {
                                $unit_price = CLASS_SESSION_COST;
                            } else if($mode_of_payment == 'Account'){
                                $unit_price = $row['per_minute_cost'];
                            } else {
                                $unit_price = round($row['unit_price']/$row['total_quantity'], 2);
                            }
                            $unit_discount = round($unit_price * $row['discount'] / 100, 2);
                            $unit_price_after_discount = $unit_price - $unit_discount;

                            $line_item_price_after_discount = round($row['quantity'] * $unit_price_after_discount, 2);

                            if($mode_of_payment == 'credit_time') {
                                $unit_price = 0;
                                $unit_price_after_discount = 0;
                                $line_item_price_after_discount = 0;
                            }

                            ?>
                            <tr>
                                <td><?= $row['transaction_date'] ?></td>
                                <td><?= $row['cashier'] ?></td>
                                <td><?= $row['invoice_number'] ?></td>
                                <td><?= $row['gen_name'] ?></td>
                                <td><?= $row['customer_name'] ?></td>
                                <td><?= $mode_of_payment ?></td>
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
                                <td><?=$line_item_price_after_discount?></td>
                                <td><?= $row['mode_of_payment'] ?></td>
                                <td><?= $row['mop_amount'] ?></td>
                                <td><?= $row['mode_of_payment_1'] ?></td>
                                <td><?= $row['mop1_amount'] ?></td>
                                <td><?= $row['qty'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?=$unit_price_after_discount?></td>
                                <td><?php
                                    /*$line_item_discount = $unit_discount * ($row['quantity'] + $row['units_remaining']) ;
                                    echo $line_item_discount;*/

                                    echo $unit_discount;

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
                                <td><?=$unit_price?></td>
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
                                <td><?=$booking_duration?></td>
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