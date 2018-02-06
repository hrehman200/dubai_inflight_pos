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
                    <br/>
                    <input type="hidden" name="customerId" id="customerId" value="<?=$_GET['customerId']?>" />
                    <input type="text" class="form-contorl span4" placeholder="Customer Name" id="customer" name="customer" autocomplete="off" />

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
                ?>

                <div class="content" id="content">
                    <div style="font-weight:bold; text-align:center;font-size:14px;margin-bottom: 15px;">
                        Revenue & Liability from&nbsp;<?php echo $_GET['d1'] ?>&nbsp;to&nbsp;<?php echo $_GET['d2'] ?>
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
                        $arr = sumTwoRows($arr);

                        /** SKYDIVERS */
                        $sql = "SELECT
                            'Skydivers' AS package_name,
                            SUM(paid) AS paid,
                            SUM(minutes_used) AS minutes_used,
                            SUM(total_minutes) AS total_minutes
                        FROM
                            (
                            SELECT
                                s1.amount AS paid,
                                SUM(CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fb1.duration ELSE 0
                                  END) AS minutes_used,
                                SUM(
                                    CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fo1.duration ELSE 0
                                  END) AS total_minutes
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
                        LEFT JOIN discounts d ON
                            (fp1.discount_id = d.id OR d.id IS NULL)
                        WHERE
                            fpkg.id IN(6, 8) AND(
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
                                c.customer_name != 'FDR' OR c.customer_name IS NULL
                            ) AND d.category NOT IN(
                                'Military',
                                'Navy Seal',
                                'Presidential Guard'
                            )
                        GROUP BY
                            s1.invoice_number
                        ) AS tbl";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        $arr2 = $result->fetchAll();
                        $arr2 = sumTwoRows($arr2);
                        $arr = array_merge($arr, $arr2);

                        /** Military */
                        $sql = "SELECT
                            'Military' AS package_name,
                            SUM(paid) AS paid,
                            SUM(minutes_used) AS minutes_used,
                            SUM(total_minutes) AS total_minutes
                        FROM
                            (
                            SELECT
                                s1.amount AS paid,
                                SUM(CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fb1.duration ELSE 0
                                  END) AS minutes_used,
                                SUM(
                                    CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fo1.duration ELSE 0
                                  END
                                ) AS total_minutes
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
                        LEFT JOIN discounts d ON
                            fp1.discount_id = d.id
                        WHERE
                            fpkg.id IN(6, 8) AND(
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
                                c.customer_name != 'FDR' OR c.customer_name IS NULL
                            ) AND d.category IN(
                                'Military'
                            )
                        GROUP BY
                            s1.invoice_number
                        ) AS tbl";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        $arr2 = $result->fetchAll();
                        $arr2 = sumTwoRows($arr2);
                        $arr = array_merge($arr, $arr2);

                        /** Navy Seal */
                        $sql = "SELECT
                            'Navy Seal' AS package_name,
                            SUM(paid) AS paid,
                            SUM(minutes_used) AS minutes_used,
                            SUM(total_minutes) AS total_minutes
                        FROM
                            (
                            SELECT
                                s1.amount AS paid,
                                SUM(CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fb1.duration ELSE 0
                                  END) AS minutes_used,
                                SUM(
                                    CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fo1.duration ELSE 0
                                  END
                                ) AS total_minutes
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
                        LEFT JOIN discounts d ON
                            fp1.discount_id = d.id
                        WHERE
                            fpkg.id IN(6, 8) AND(
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
                                c.customer_name != 'FDR' OR c.customer_name IS NULL
                            ) AND d.category IN(
                                'Navy Seal'
                            )
                        GROUP BY
                            s1.invoice_number
                        ) AS tbl";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        $arr2 = $result->fetchAll();
                        $arr2 = sumTwoRows($arr2);
                        $arr = array_merge($arr, $arr2);

                        /** Presidential Guard */
                        $sql = "SELECT
                            'Presidential Guard' AS package_name,
                            SUM(paid) AS paid,
                            SUM(minutes_used) AS minutes_used,
                            SUM(total_minutes) AS total_minutes
                        FROM
                            (
                            SELECT
                                s1.amount AS paid,
                                SUM(CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fb1.duration ELSE 0
                                  END) AS minutes_used,
                                SUM(
                                    CASE WHEN(
                                        s1.mode_of_payment != 'credit_time' AND s1.mode_of_payment_1 != 'credit_time'
                                    ) THEN fo1.duration ELSE 0
                                  END
                                ) AS total_minutes
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
                        LEFT JOIN discounts d ON
                            fp1.discount_id = d.id
                        WHERE
                            fpkg.id IN(6, 8) AND(
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
                                c.customer_name != 'FDR' OR c.customer_name IS NULL
                            ) AND d.category IN(
                                'Presidential Guard'
                            )
                        GROUP BY
                            s1.invoice_number
                        ) AS tbl";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            ':startDate' => $_GET['d1'],
                            ':endDate'   => $_GET['d2']
                        ));

                        $arr2 = $result->fetchAll();
                        $arr2 = sumTwoRows($arr2);
                        $arr = array_merge($arr, $arr2);

                        foreach ($arr as $row) {
                            if($row['package_name'] == 'Skydivers') {
                                ?>
                                <tr>
                                    <td colspan="5"><b>Experienced Return Flyers</b></td>
                                </tr>
                                <?php
                            }
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
                    </table>

                    <hr/>

                    <?php
                    if($_GET['customerId'] > 0) {
                        ?>
                        <table class="table table-striped" style="background-color: white;" id="tblCollection">
                            <?php
                            $sql = "
                              SELECT 
                                c1.customer_name,
                                IFNULL(fc.minutes,0)+IFNULL(c1.credit_time,0) AS units_remaining,
                                SUM(fb1.duration) AS units_consumed,
                                SUM(s1.amount) AS revenue_on_consumed
                              FROM
                                sales s1
                              INNER JOIN
                                flight_purchases fp1 ON s1.invoice_number = fp1.invoice_id
                              INNER JOIN
                                flight_offers fo1 ON fp1.flight_offer_id = fo1.id
                              LEFT JOIN
                                flight_credits fc ON fc.customer_id = fp1.customer_id
                              LEFT JOIN
                                flight_bookings fb1 ON fb1.flight_purchase_id = fp1.id
                              LEFT JOIN
                                vat_codes vc ON fp1.vat_code_id = vc.id
                              LEFT JOIN
                                discounts d on fp1.discount_id = d.id
                              INNER JOIN
                                customer c1 ON s1.customer_id = c1.customer_id
                            WHERE s1.date <= :endDate
                            AND (customer_name != 'FDR' OR customer_name IS NULL)
                            AND s1.customer_id = :customerId
                            AND fp1.status = 1
                            GROUP BY c1.customer_id
                            ORDER BY c1.customer_name";

                            $result = $db->prepare($sql);
                            $result->execute(array(
                                ':endDate' => $_GET['d2'],
                                ':customerId' => $_GET['customerId']
                            ));

                            ?>
                            <tr>
                                <th>Customer Name</th>
                                <th>Unit Consumed</th>
                                <th>Units Remaining</th>
                                <th>Revenue On Consumed</th>
                                <th>Amount Laibility</th>
                            </tr>
                            <?php
                            $arr = $result->fetchAll();
                            foreach ($arr as $row) {

                                ?>
                                <tr>
                                    <td><?= $row['customer_name'] ?></td>
                                    <td><?= $row['units_consumed'] ?></td>
                                    <td><?= $row['units_remaining'] ?></td>
                                    <td><?php
                                        $per_min_cost = $row['revenue_on_consumed'] / $row['units_consumed'];
                                        $liability = $per_min_cost * $row['units_remaining'];
                                        echo number_format($row['revenue_on_consumed'], 1);
                                        ?></td>
                                    <td><?= number_format($liability, 1) ?></td>
                                </tr>
                                <?php
                            }
                            ?>

                        </table>
                        <?php
                    }
                    ?>
                </div>
                <div class="clearfix"></div>
            </div>
</body>

<script type="text/javascript">


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

<?php include('footer.php'); ?>
</html>