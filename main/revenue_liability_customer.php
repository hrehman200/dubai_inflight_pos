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

                    Liability till:
                    <select id='year' name="year" class="span2">
                        <option>2017</option>
                        <option>2018</option>
                    </select>

                    <select id='month' name="month" class="span2">
                        <option selected value='1'>Jan</option>
                        <option value='2'>Feb</option>
                        <option value='3'>Mar</option>
                        <option value='4'>Aprl</option>
                        <option value='5'>May</option>
                        <option value='6'>Jun</option>
                        <option value='7'>Jul</option>
                        <option value='8'>Aug</option>
                        <option value='9'>Sep</option>
                        <option value='10'>Oct</option>
                        <option value='11'>Nov</option>
                        <option value='12'>Dec</option>
                    </select>

                    <br/>
                    <input type="hidden" name="customerId" id="customerId" value="<?=$_GET['customerId']?>" />
                    <input type="text" class="form-contorl span6" placeholder="Customer Name" id="customer" name="customer" autocomplete="off" />

                    <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;" type="submit">
                        <i class="icon icon-search icon-large"></i> Search
                    </button>

                    <span id="spPre2018">
                        <input type="checkbox" id="chkPre2018" class="form-control" />
                        <label for="chkPre2018" style="display: inline;">Pre 2018</label>
                    </span>

                    <button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-info btn-large" onclick="convertToCSV()"><i
                                class="icon icon-columns icon-large"></i> Export</button>
                    <button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-success btn-large"><a href="javascript:Clickheretoprint()"><i
                                class="icon icon-print icon-large"></i> Print</a></button>
                </form>

                <div class="content" id="content">

                    <?php
                    //if($_GET['customerId'] > 0) {
                        ?>
                        <table class="table table-striped" style="background-color: white;" id="tblCustomerLiability">
                            <?php

                            if(!isset($_GET['month'])) {
                                $_GET['month'] = date('n');
                                $_GET['year'] = date('Y');
                            }

                            $sql = "
                                SELECT  s1.customer_id, s1.month, s1.year, SUM(s1.amount) AS purchased_amount, c.customer_name, c.credit_time, c.credit_cash, c.per_minute_cost, SUM(fc.minutes) AS credit_minutes,
                                c.expected_date
                                FROM `sales` s1
                                INNER JOIN customer c ON s1.customer_id = c.customer_id
                                LEFT JOIN flight_credits fc ON fc.customer_id = c.customer_id
                                WHERE YEAR(s1.date) <= :year AND MONTH(s1.date) <= :month AND s1.customer_id > 0";

                            if($_GET['customerId'] > 0) {
                                $sql .= sprintf(" AND c.customer_id = %d", $_GET['customerId']);
                            }

                            $sql .= " GROUP BY s1.customer_id
                                ORDER BY c.customer_name";

                            $result = $db->prepare($sql);
                            $result->execute(array(
                                ':year' => $_GET['year'],
                                ':month' => $_GET['month']
                            ));

                            $sql = "
                                SELECT s1.customer_id, SUM(fo.duration) AS minutes_purchased, SUM(fb.duration) AS minutes_used
                                FROM `sales` s1
                                INNER JOIN flight_purchases fp ON fp.customer_id = s1.customer_id
                                INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                                LEFT JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
                                WHERE YEAR(s1.date) <= :year AND MONTH(s1.date) <= :month AND s1.customer_id > 0";

                            if($_GET['customerId'] > 0) {
                                $sql .= sprintf(" AND s1.customer_id = %d", $_GET['customerId']);
                            }

                            $sql .= " GROUP BY s1.customer_id";

                            $result2 = $db->prepare($sql);
                            $result2->execute(array(
                                ':year' => $_GET['year'],
                                ':month' => $_GET['month']
                            ));

                            $arr1 = $result->fetchAll(PDO::FETCH_ASSOC);
                            $arr2 = $result2->fetchAll(PDO::FETCH_ASSOC);

                            foreach($arr1 as &$item1) {
                                $filtered = array_filter($arr2, function ($item2) use ($item1) {
                                    return $item2['customer_id'] == $item1['customer_id'];
                                });

                                $filtered = array_values($filtered);

                                if(count($filtered) > 0) {
                                    $item1 = array_merge($filtered[0], $item1);
                                }
                            }

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
                            $total_minutes = 0;
                            $total_price = 0;
                            if(count($arr1) > 0) {
                                foreach ($arr1 as $row) {
                                    $units_remaining = $row['credit_minutes'] + $row['credit_time'];
                                    if($row['minutes_used'] > 0) {
                                        $per_min_cost = $row['purchased_amount'] / $row['minutes_used'];
                                    } else {
                                        $per_min_cost = $row['purchased_amount'] / $row['minutes_purchased'];
                                    }
                                    $credit_minutes_liability = ($per_min_cost * $row['credit_minutes']) + ($row['per_minute_cost'] * $row['credit_time']);
                                    if(is_nan($credit_minutes_liability)) {
                                        $credit_minutes_liability = 0;
                                    }
                                    $total_minutes += $units_remaining;
                                    $total_price += $credit_minutes_liability;

                                    if($units_remaining <= 0 && $credit_minutes_liability <= 0 && $_GET['customerId'] <= 0) {
                                        continue;
                                    }

                                    if(strcasecmp($row['customer_name'], 'inflight staff flying') == 0 ||
                                        strcasecmp($row['customer_name'], 'fdr') == 0 ||
                                        strcasecmp($row['customer_name'], 'maintenance') == 0) {
                                        continue;
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $row['customer_name'] ?></td>
                                        <td><?= $row['customer_id'] ?></td>
                                        <td><?= number_format($units_remaining) ?></td>
                                        <td><?= number_format(round($credit_minutes_liability)) ?></td>
                                        <td><?php
                                            if(date('Y', strtotime($row['expected_date'])) < 2018) {
                                                echo $row['credit_time'];
                                            } else {
                                                echo 0;
                                            }?>
                                        </td>
                                        <td><?php
                                            if(date('Y', strtotime($row['expected_date'])) < 2018) {
                                                echo round($row['credit_cash'] * 5 / 105, 2);
                                            } else {
                                                echo 0;
                                            }?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align:right; padding-right: 50px;"><b>Total:</b></td>
                                    <td><b><?=number_format($total_minutes)?></b></td>
                                    <td><b><?=number_format($total_price)?></b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php
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
                    //}
                    ?>
                </div>
                <div class="clearfix"></div>
            </div>
</body>

<script type="text/javascript">

    $('#month').val('<?=$_GET['month']?>');
    $('#year').val('<?=$_GET['year']?>');

    $('#chkPre2018').on('change', function (e) {
        if($(this).is(':checked')) {
            $('#tblCustomerLiability tr th:nth-last-child(-n+2), #tblCustomerLiability tr td:nth-last-child(-n+2)').show();
        } else {
            $('#tblCustomerLiability tr th:nth-last-child(-n+2), #tblCustomerLiability tr td:nth-last-child(-n+2)').hide();
        }
    }).change();

    $('#year').on('change', function (e) {
        if($(this).val() < 2018) {
            $('#spPre2018')
                .find('#chkPre2018').prop('checked', false).change().end()
                .hide();
        } else {
            $('#spPre2018').show();
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
        exportTableToCSV($('#tblCustomerLiability'), 'rnl.csv');
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

