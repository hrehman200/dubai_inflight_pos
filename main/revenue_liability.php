<?php
include('header.php');
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
                <i class="icon-bar-chart"></i> Revenue & Liability
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Revenue & Liability</li>
            </ul>

            <div>
                <div style="margin-top: -19px; margin-bottom: 21px;">
                    <a href="index.php">
                        <button class="btn btn-default btn-large" style="float: none;"><i
                                class="icon icon-circle-arrow-left icon-large"></i> Back
                        </button>
                    </a>
                </div>
                <form action="revenue_liability.php" method="get">
                    From : <input type="text" name="d1" style="width: 223px; padding:14px;" class="tcal" value=""/>
                    To: <input type="text" style="width: 223px; padding:14px;" name="d2" class="tcal" value=""/>
                    <!--<br/>
                    <input type="hidden" name="customerId" id="customerId" value="<?/*=$_GET['customerId']*/?>" />
                    <input type="text" class="form-contorl span4" placeholder="Customer Name" id="customer" name="customer" autocomplete="off" />-->

                    <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;" type="submit">
                        <i class="icon icon-search icon-large"></i> Search
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

                /**
                 * @param $arr
                 * @return mixed
                 */
                function sumTwoRows($arr) {
                    if (count($arr) > 1) {
                        for ($i = 1; $i < count($arr); $i++) {
                            $arr[0]['paid'] += $arr[$i]['paid'];
                            $arr[0]['minutes_used'] += $arr[$i]['minutes_used'];
                            $arr[0]['total_minutes'] += $arr[$i]['total_minutes'];
                        }

                        array_splice($arr, 1);
                    }
                    return $arr;
                }

                /**
                 * @param array $arr
                 * @param callable $key_selector
                 * @return array
                 */
                function array_group_by(array $arr, callable $key_selector) {
                    $result = array();
                    foreach ($arr as $i) {
                        $key = call_user_func($key_selector, $i);
                        $result[$key][] = $i;
                    }
                    return $result;
                }

                /**
                 * If search date is 15 May, purchase date is 15 May but credit-flight was taken on 16 May then
                 * credit-used should not be shown on 15 May
                 *
                 * @param $flight_time
                 * @param $date1
                 * @param $date2
                 * @return bool
                 */
                function isFlightTimeInsideSearchedDate($flight_time, $date1, $date2) {
                    $flight_time = (new DateTime($flight_time))->format('Y-m-d');
                    return (strtotime($flight_time) >= strtotime($date1) && strtotime($flight_time) <= strtotime($date2));
                }

                function getDataAndAggregate($package_name) {
                    global $db;

                    if($package_name == 'Skydivers' || $package_name == 'FTF') {
                        $join_with_discount = 'LEFT JOIN discounts d ON fp1.discount_id = d.id OR fp1.discount_id = 0';
                    } else {
                        $join_with_discount = 'INNER JOIN discounts d ON fp1.discount_id = d.id';
                    }

                    if($package_name == 'FTF') {
                        $package_check = " fpkg.package_name LIKE 'FTF%'";
                    } else if($package_name == 'RF - Repeat Flights') {
                        $package_check = " fpkg.package_name LIKE 'RF - Repeat Flights%'";
                    } else {
                        $package_check = " fpkg.id IN (6, 8)";
                    }

                    $sql = sprintf("SELECT
                            fp1.id AS flight_purchase_id,
                            fp1.deduct_from_balance,
                            fb1.id,
                            fb1.from_flight_purchase_id,
                            fb1.flight_time,
                            IFNULL(fb1.flight_time, NOW()+10) <= NOW() AS flight_taken,
                            s1.invoice_number,
                            s1.customer_id,
                            CASE WHEN(
                                (s1.mode_of_payment = 'credit_time' OR s1.mode_of_payment_1 = 'credit_time') AND fp1.deduct_from_balance = 2
                            ) THEN (fb1.duration * c.per_minute_cost) ELSE s1.amount
                            END AS paid,
                            CASE WHEN(
                                s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time' AND fp1.deduct_from_balance = 0
                            ) THEN fb1.duration ELSE 0
                            END AS minutes_used,
                            CASE WHEN(
                                s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time' AND fp1.deduct_from_balance = 0 
                            ) THEN fo1.duration ELSE 0
                            END AS total_minutes,
                            CASE WHEN(
                                s1.mode_of_payment = 'credit_time' OR s1.mode_of_payment_1 = 'credit_time' OR fp1.deduct_from_balance > 0
                            ) THEN fb1.duration ELSE 0
                            END AS credit_used
                        FROM
                            sales s1
                        INNER JOIN flight_purchases fp1 ON
                            s1.invoice_number = fp1.invoice_id
                        INNER JOIN flight_offers fo1 ON
                            fp1.flight_offer_id = fo1.id
                        INNER JOIN flight_packages fpkg ON
                            fo1.package_id = fpkg.id
                        LEFT JOIN flight_bookings fb1 ON
                            fb1.flight_purchase_id = fp1.id
                        INNER JOIN customer c ON
                            fp1.customer_id = c.customer_id
                        %s
                        WHERE
                            %s 
                            AND(
                                s1.mode_of_payment IN(
                                    'Cash',
                                    'Card',
                                    'Online',
                                    'Account',
                                    'credit_time',
                                    'credit_cash'
                                ) OR s1.mode_of_payment_1 IN(
                                    'Cash',
                                    'Card',
                                    'Online',
                                    'Account',
                                    'credit_time',
                                    'credit_cash'
                                )
                            ) AND(
                                s1.date >= :startDate AND s1.date <= :endDate
                            ) AND(
                                (customer_name != 'FDR' AND customer_name != 'MAINTENANCE' AND customer_name != 'inflight staff flying') OR customer_name IS NULL
                            ) AND d.category ", $join_with_discount, $package_check);

                    if($package_name == 'Skydivers' || $package_name == 'FTF' || $package_name == 'RF - Repeat Flights') {
                        $sql .= "NOT IN ('Presidential Guard', 'Navy Seal', 'Military')";
                    } else {
                        $sql .= "IN ('".$package_name."')";
                    }

                    if($package_name == 'FTF') {
                        $sql .= " AND fpkg.package_name LIKE 'FTF%'";
                    }

                    $sql .= " GROUP BY fp1.id, fb1.id";

                    $result = $db->prepare($sql);
                    $result->execute(array(
                        ':startDate' => $_GET['d1'],
                        ':endDate'   => $_GET['d2']
                    ));

                    $arr2 = $result->fetchAll(PDO::FETCH_ASSOC);

                    $arr_flight_purchase_ids = array_map(function($v) { return $v['flight_purchase_id'];}, $arr2);
                    $arr_flight_purchase_ids = array_unique($arr_flight_purchase_ids);

                    $arr_paid = array_group_by($arr2, function($v) { return $v['invoice_number']; });
                    $paid = array_reduce($arr_paid, function($carry, $item) {
                        $carry += $item[0]['paid'];
                        return $carry;
                    });

                    $arr_total_minutes = array_group_by($arr2, function($v) { return $v['flight_purchase_id']; });
                    $total_minutes = array_reduce($arr_total_minutes, function($carry, $item) {
                        $carry += $item[0]['total_minutes'];
                        return $carry;
                    });

                    $arr_minutes_used = array_group_by($arr2, function($v) { return $v['id']; });
                    $purchased_minutes_used = 0;
                    $total_credit_cost = 0;
                    $minutes_used = array_reduce($arr_minutes_used, function($carry, $item) use (&$purchased_minutes_used, &$total_credit_cost, $arr_flight_purchase_ids, $package_name) {
                        if($item[0]['flight_taken'] == 1) {
                            if ($item[0]['from_flight_purchase_id'] > 0 || $item[0]['deduct_from_balance'] == 2) {
                                if(isFlightTimeInsideSearchedDate($item[0]['flight_time'], $_GET['d1'], $_GET['d2'])) {
                                    $carry += $item[0]['credit_used'];

                                    if($item[0]['deduct_from_balance'] == 2) {
                                        $credit_cost_per_minute = getPerMinuteCostForCustomer($item[0]['customer_id']);
                                    } else {
                                        $credit_cost_per_minute = getPerMinuteCostOfPurchasedPackage($item[0]['from_flight_purchase_id']);
                                    }
                                    $total_credit_cost += $credit_cost_per_minute * $item[0]['credit_used'];
                                    // if credit used is from the same purchase in selected time range
                                    if (in_array($item[0]['from_flight_purchase_id'], $arr_flight_purchase_ids)) {
                                        $purchased_minutes_used += $item[0]['credit_used'];
                                    }
                                }
                            } else {
                                $carry += $item[0]['minutes_used'];
                                $purchased_minutes_used += $item[0]['minutes_used'];
                            }
                        }
                        return $carry;
                    });

                    $credit_used = array_reduce($arr_minutes_used, function($carry, $item) {
                        if(isFlightTimeInsideSearchedDate($item[0]['flight_time'], $_GET['d1'], $_GET['d2'])) {
                            $carry += $item[0]['credit_used'];
                        }
                        return $carry;
                    });

                    if($total_minutes > 0) {
                        $aed_per_paid_minute = $paid / $total_minutes;
                        $total_purchased_cost = $aed_per_paid_minute * $purchased_minutes_used;
                    }

                    $arr2 = [[
                        'package_name' => $package_name,
                        'paid' => $paid,
                        'total_minutes' => $total_minutes,
                        'minutes_used' => $minutes_used,
                        'credit_used' => $credit_used,
                        'purchased_minutes_used' => $purchased_minutes_used,
                        'aed_value' => $total_purchased_cost + $total_credit_cost
                    ]];

                    return $arr2;
                }

                /**
                 * @param $product_name
                 * @param $date1
                 * @param $date2
                 * @return mixed
                 */
                function getMerchandiseRevenue($product_name, $date1, $date2) {
                    global $db;

                    if($product_name == TYPE_MERCHANDISE) {
                        $query = $db->prepare('SELECT SUM(s.amount) AS paid
                            FROM sales s
                            INNER JOIN sales_order so ON s.invoice_number = so.invoice
                            INNER JOIN products p ON so.product = p.product_id 
                            WHERE 
                            (p.product_name NOT LIKE "%Video%" AND p.product_name NOT LIKE "%Helmet Rent%")
                            AND p.gen_name = ?
                            AND (s.date >= ? AND s.date <= ?)');
                        $query->execute([TYPE_MERCHANDISE, $date1, $date2]);
                        $row = $query->fetch(PDO::FETCH_ASSOC);

                    } else {
                        $query = $db->prepare('SELECT SUM(s.amount) AS paid
                            FROM sales s
                            INNER JOIN sales_order so ON s.invoice_number = so.invoice
                            INNER JOIN products p ON so.product = p.product_id 
                            WHERE p.product_name LIKE ? 
                            AND (s.date >= ? AND s.date <= ?)');
                        $query->execute(["%" . $product_name . "%", $date1, $date2]);
                        $row = $query->fetch(PDO::FETCH_ASSOC);
                    }

                    $arr2 = [[
                        'package_name' => $product_name == 'Video' ? 'Videos/Photos' : $product_name,
                        'paid' => $row['paid'],
                        'aed_value' => $row['paid']
                    ]];

                    return $arr2;
                }

                /**
                 * @param $packages
                 * @param $from
                 * @param $to
                 * @return array
                 */
                function getMinutesFlownInPackages($packages, $from, $to) {
                    global $db;

                    $packages = sprintf('"%s"', implode('","', $packages));

                    $sql = sprintf('SELECT fpkg.package_name, SUM(fb.duration) AS duration 
                        FROM flight_bookings fb
                        INNER JOIN flight_purchases fp ON fb.flight_purchase_id = fp.id
                        INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                        INNER JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                        WHERE fpkg.package_name IN (%s)
                          AND DATE(fb.flight_time) >= :from AND DATE(fb.flight_time) <= :to
                        GROUP BY fpkg.package_name', $packages);

                    $query = $db->prepare($sql);
                    $query->execute([
                        ':from' => $from,
                        ':to' => $to
                    ]);
                    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

                    $arr = [];
                    foreach($rows as $row) {
                        $arr[$row['package_name']] = $row['duration'];
                    }
                    return $arr;
                }

                /**
                 * @param $from
                 * @param $to
                 * @return array
                 */
                function getFlightDiscountsGiven($from, $to) {
                    global $db;

                    $query = $db->prepare('SELECT d.category, d.percent, SUM((d.percent*fp.price/100)) AS discount_value
                        FROM `flight_purchases` fp
                        INNER JOIN discounts d ON fp.discount_id = d.id
                        WHERE DATE(fp.created) >= ? AND DATE(fp.created) <= ?
                        GROUP BY d.category');
                    $query->execute([$from, $to]);
                    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
                    return $rows;
                }

                /**
                 * @param $from
                 * @param $to
                 * @return array
                 */
                function getMerchandiseDiscountsGiven($from, $to) {
                    global $db;

                    $query = $db->prepare('SELECT d.category, d.percent, SUM((d.percent*so.price/100)) AS discount_value 
                        FROM `sales_order` so
                        INNER JOIN discounts d ON so.discount_id = d.id
                        WHERE so.date >= ? AND so.date <= ? AND d.percent > 0
                        GROUP BY d.category');
                    $query->execute([$from, $to]);
                    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
                    return $rows;
                }
                ?>

                <div class="content" id="content">
                    <div style="font-weight:bold; text-align:center;font-size:14px;margin-bottom: 15px;">
                        Revenue & Liability from&nbsp;<?php echo $_GET['d1'] ?>&nbsp;to&nbsp;<?php echo $_GET['d2'] ?>
                    </div>

                    <table class="table table-striped" id="tblRnLSummary">
                        <tr>
                            <th>Package</th>
                            <th>Paid</th>
                            <th>Total Minutes</th>
                            <th>Minutes Used</th>
                            <th>AED Value</th>
                        </tr>
                        <?php

                        if($_POST['military'] == 1) {
                            $arr_revenue = json_decode(base64_decode($_POST['military_data']), true);

                        } else {
                            /** FTF */
                            $arr_revenue = getDataAndAggregate('FTF');

                            /** RF */
                            $arr2 = getDataAndAggregate('RF - Repeat Flights');
                            $arr_revenue = array_merge($arr_revenue, $arr2);

                            /** SKYDIVERS */
                            $arr2 = getDataAndAggregate('Skydivers');
                            $arr_revenue = array_merge($arr_revenue, $arr2);

                            $arr_military = [];
                            /** Military */
                            $arr2 = getDataAndAggregate('Military');
                            $arr_military = array_merge($arr_military, $arr2);

                            /** Navy Seal */
                            $arr2 = getDataAndAggregate('Navy Seal');
                            $arr_military = array_merge($arr_military, $arr2);

                            /** Presidential Guard */
                            $arr2 = getDataAndAggregate('Presidential Guard');
                            $arr_military = array_merge($arr_military, $arr2);

                            $arr_military_sum[0] = [
                                'package_name' => 'Military',
                                'paid' => array_sum(array_column($arr_military, 'paid')),
                                'total_minutes' => array_sum(array_column($arr_military, 'total_minutes')),
                                'minutes_used' => array_sum(array_column($arr_military, 'minutes_used')),
                                'aed_value' => array_sum(array_column($arr_military, 'aed_value'))
                            ];

                            $arr_revenue = array_merge($arr_revenue, $arr_military_sum);

                            /** HELMET RENT */
                            $arr2 = getMerchandiseRevenue('Helmet Rent', $_GET['d1'], $_GET['d2']);
                            $arr_revenue = array_merge($arr_revenue, $arr2);

                            /** VIDEO */
                            $arr2 = getMerchandiseRevenue('Video', $_GET['d1'], $_GET['d2']);
                            $arr_revenue = array_merge($arr_revenue, $arr2);

                            /** MERCHANDISE */
                            $arr2 = getMerchandiseRevenue(TYPE_MERCHANDISE, $_GET['d1'], $_GET['d2']);
                            $arr_revenue = array_merge($arr_revenue, $arr2);
                        }

                        foreach ($arr_revenue as $row) {
                            if($row['package_name'] == 'Skydivers') {
                                ?>
                                <!--<tr>
                                    <td colspan="6"><b>Experienced Return Flyers</b></td>
                                </tr>-->
                                <?php
                            }
                            ?>
                            <tr class="<?=$row['package_name']=='Military'?'military-row':''?>">
                                <td><b><?= $row['package_name'] ?></b></td>
                                <td><?= number_format($row['paid'], 1) ?></td>
                                <td><?= number_format($row['total_minutes']) ?></td>
                                <td><?= number_format($row['minutes_used']) ?></td>
                                <td><?= number_format($row['aed_value'], 2) ?></td>
                            </tr>
                            <?php
                        }

                        $arr_packages = json_encode(array_map(function($v) { return $v['package_name']; }, $arr_revenue));
                        $arr_paid = json_encode(array_map(function($v) { return round($v['aed_value'], 1); }, $arr_revenue));

                        ?>
                        <tr>
                            <td><b>Total:</b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'paid')), 1) ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'total_minutes'))) ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'minutes_used'))) ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'aed_value')), 2) ?></b></td>
                        </tr>
                    </table>

                    <form id="military-form" method="POST" action="<?=$_SERVER['REQUEST_URI']?>" target="_blank">
                        <input type="hidden" name="military" value="1" />
                        <input type="hidden" name="military_data" value="<?=base64_encode(json_encode($arr_military))?>" />
                    </form>

                    <div class="app">
                        <pie-chart></pie-chart>
                    </div>

                    <hr/>



                    <?php
                    if(!isset($_POST['military'])) {
                        ?>

                        <div class="row">
                            <div class="span10 offset1">
                                <table class="table">
                                    <tr>
                                        <th>Staff Flying</th>
                                        <th>Maintenance</th>
                                        <th>Marketing</th>
                                        <th>Giveaway</th>
                                        <th>Training IDP</th>
                                        <th>Training FITP</th>
                                        <th>Training Safety</th>
                                    </tr>
                                    <?php
                                    $arr_minutes_flown = getMinutesFlownInPackages(['Staff Flying', 'Maintenance', 'Giveaways', 'Marketing'], $_GET['d1'], $_GET['d2']);
                                    ?>
                                    <tr>
                                        <td><?=(int)$arr_minutes_flown['Staff Flying']?></td>
                                        <td><?=(int)$arr_minutes_flown['Maintenance']?></td>
                                        <td><?=(int)$arr_minutes_flown['Marketing']?></td>
                                        <td><?=(int)$arr_minutes_flown['Giveaways']?></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <br/>

                        <div class="row">
                            <div class="span5 offset1">
                                <table class="table">
                                    <tr>
                                        <th colspan="3" style="text-align: center;">Discount Given on Flying Time</th>
                                    </tr>
                                    <tr>
                                        <th>Percentage</th>
                                        <th>Categories</th>
                                        <th>Value Discounted</th>
                                    </tr>
                                    <?php
                                    $discounts_given = getFLightDiscountsGiven($_GET['d1'], $_GET['d2']);
                                    foreach($discounts_given as $row) {
                                        ?>
                                        <tr>
                                            <td><?=$row['percent']?>%</td>
                                            <td><?=$row['category']?></td>
                                            <td><?=number_format($row['discount_value'], 2)?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                            </div>
                            <div class="span5">
                                <table class="table">
                                    <tr>
                                        <th colspan="3" style="text-align: center;">Discount Given on Merchandise</th>
                                    </tr>
                                    <tr>
                                        <th>Percentage</th>
                                        <th>Categories</th>
                                        <th>Value Discounted</th>
                                    </tr>
                                    <?php
                                    $discounts_given = getMerchandiseDiscountsGiven($_GET['d1'], $_GET['d2']);
                                    foreach($discounts_given as $row) {
                                        ?>
                                        <tr>
                                            <td><?=$row['percent']?>%</td>
                                            <td><?=$row['category']?></td>
                                            <td><?=number_format($row['discount_value'], 2)?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>
                <div class="clearfix"></div>
            </div>
</body>

<script type="text/javascript">

    $('.military-row').click(function(e) {
        /*var win = window.open("<?=$_SERVER['REQUEST_URI']?>&military=1", '_blank');
        win.focus();*/

        $('#military-form').submit();
    });

    $("#customer").typeahead({
        onSelect: function(item) {
            $('#customerId').val(item.value);
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
    }).val("<?=$_GET['customer']?>")
        .on('change', function(e) {
            if($(this).val()=='') {
                $('#customerId').val('');
            }
        });

    function convertToCSV() {
        exportTableToCSV($('#tblRnLSummary'), 'rnl.csv');
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

<script type="text/javascript" src="js/chart.min.js"></script>
<script type="text/javascript" src="js/vue.min.js"></script>
<script type="text/javascript" src="js/vue-chartjs.min.js"></script>
<script type="text/javascript" src="js/chart.piecelabel.js"></script>
<script type="text/javascript">

    Vue.component('pie-chart', {
        extends: VueChartJs.Pie,
        mounted () {
            this.renderChart({
                labels: JSON.parse('<?=$arr_packages?>'),
                datasets: [
                    {
                        label: 'Data One',
                        data: JSON.parse('<?=$arr_paid?>'),
                        backgroundColor: ['#F7DF00', '#ca0813', '#287AEB', '#89A366', '#9F7371', '#72D84E', '#42C4F0']
                    }
                ]
            }, {
                responsive: true, maintainAspectRatio: false, pieceLabel: {
                    mode: 'percentage',
                    precision: 1,
                    fontSize: 18,
                    fontColor: '#fff',

                }
            })
        }
    });

    var vm = new Vue({
        el: '.app',
        data: {

        }
    });
</script>

<?php include('footer.php'); ?>
<style>
    .military-row {
        cursor:hand;
    }
</style>
</html>

