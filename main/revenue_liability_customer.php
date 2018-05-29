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
                <i class="icon-bar-chart"></i> Revenue & Liability per Customer
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Revenue & Liability per Customer</li>
            </ul>

            <div>
                <div style="margin-top: -19px; margin-bottom: 21px;">
                    <a href="index.php">
                        <button class="btn btn-default btn-large" style="float: none;"><i
                                class="icon icon-circle-arrow-left icon-large"></i> Back
                        </button>
                    </a>
                </div>
                <form action="<?=$_SERVER['PHP_SELF']?>" method="get">
                    From : <input type="text" name="d1" style="width: 223px; padding:14px;" class="tcal" value="<?=$_GET['d1']?>" autocomplete="0"/>
                    To: <input type="text" style="width: 223px; padding:14px;" name="d2" class="tcal" value="<?=$_GET['d2']?>" autocomplete="0"/>

                    <br/>
                    <input type="hidden" name="customerId" id="customerId" value="<?=$_GET['customerId']?>" />
                    <input type="text" class="form-contorl span6" placeholder="Customer Name" id="customer" name="customer" autocomplete="off" />

                    <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;" type="submit">
                        <i class="icon icon-search icon-large"></i> Search
                    </button>
                    <input type="checkbox" id="chkPre2018" class="form-control" />
                    <label for="chkPre2018" style="display: inline;">Pre 2018</label>

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
                ?>

                <div class="content" id="content">

                    <?php
                    if($_GET['customerId'] > 0) {
                        ?>
                        <table class="table table-striped" style="background-color: white;" id="tblCustomerLiability">
                            <?php
                            $sql = "
                              SELECT 
                                c1.customer_name, c1.customer_id, c1.expected_date,
                                SUM(fc.minutes) AS credit_minutes,
                                IFNULL(c1.credit_time,0) AS credit_time,
                                IFNULL(c1.credit_time,0) * c1.per_minute_cost AS credit_time_liability,
                                IFNULL(c1.credit_cash,0) AS credit_cash,
                                SUM(IFNULL(fb1.duration, 0)) AS units_consumed,
                                SUM(s1.amount) AS revenue_on_consumed,
                                SUM(fo1.duration) AS units_purchased
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
                            WHERE 
                             (s1.date >= :startDate AND s1.date <= :endDate)
                            AND ((customer_name != 'FDR' AND customer_name != 'MAINTENANCE' AND customer_name != 'inflight staff flying') OR customer_name IS NULL)
                            AND s1.customer_id = :customerId
                            AND fp1.status = 1
                            GROUP BY c1.customer_id
                            ORDER BY c1.customer_name";

                            $result = $db->prepare($sql);
                            $result->execute(array(
                                ':customerId' => $_GET['customerId'],
                                ':startDate' => $_GET['d1'],
                                ':endDate' => $_GET['d2']
                            ));

                            ?>
                            <tr>
                                <th>Customer Name</th>
                                <th>Customer ID</th>
                                <th>Total Mins Liability</th>
                                <th>AED Liability</th>
                                <th>Mins Pre 2018 Avail</th>
                                <th>VAT on Pre 2018</th>
                            </tr>
                            <?php
                            $arr_revenue = $result->fetchAll(PDO::FETCH_ASSOC);

                            if(count($arr_revenue) > 0) {
                                foreach ($arr_revenue as $row) {
                                    $units_remaining = $row['credit_minutes'] + $row['credit_time'];
                                    if($row['units_consumed'] > 0) {
                                        $per_min_cost = $row['revenue_on_consumed'] / $row['units_consumed'];
                                    } else {
                                        $per_min_cost = $row['revenue_on_consumed'] / $row['units_purchased'];
                                    }
                                    $credit_minutes_liability = ($per_min_cost * $row['credit_minutes']) + $row['credit_time_liability'];
                                    ?>
                                    <tr>
                                        <td><?= $row['customer_name'] ?></td>
                                        <td><?= $row['customer_id'] ?></td>
                                        <td><?= number_format($units_remaining) ?></td>
                                        <td><?= number_format(round($credit_minutes_liability)) ?></td>
                                        <td><?php
                                            if(date('Y', strtotime($row['expected_date'])) <= 2018) {
                                                echo $row['credit_time'];
                                            } else {
                                                echo 0;
                                            }?>
                                        </td>
                                        <td><?php
                                            if(date('Y', strtotime($row['expected_date'])) <= 2018) {
                                                echo round($row['credit_cash'] * 5 / 105, 2);
                                            } else {
                                                echo 0;
                                            }?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" align="center">No record found</td>
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

    $('#chkPre2018').on('change', function (e) {
        if($(this).is(':checked')) {
            $('#tblCustomerLiability th:gt(3), #tblCustomerLiability td:gt(3)').show();
        } else {
            $('#tblCustomerLiability th:gt(3), #tblCustomerLiability td:gt(3)').hide();
        }
    }).change();

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

<?php include('footer.php'); ?>
</html>

