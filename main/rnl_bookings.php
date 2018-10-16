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
                <i class="icon-bar-chart"></i> Revenue & Liability - Bookings
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Revenue & Liability - Bookings</li>
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

                    <strong>From : <input type="text" style="width: 223px; padding:3px;height: 30px;" name="d1"
                                          class="tcal" value=""/>
                        To: <input type="text"
                                   style="width: 223px; padding:3px;height: 30px;"
                                   name="d2" class="tcal" value=""/>
                        <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;margin-left:8px;"
                                type="submit"><i class="icon icon-search icon-large"></i> Search
                        </button>
                    </strong>

                    <!--<br/>
                    <input type="hidden" name="customerId" id="customerId" value="<?/*=$_GET['customerId']*/?>" />
                    <input type="text" class="form-contorl span6" placeholder="Customer Name" id="customer" name="customer" autocomplete="off" />

                    <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;" type="submit">
                        <i class="icon icon-search icon-large"></i> Search
                    </button>-->

                    <button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-info btn-large" onclick="convertToCSV()"><i
                                class="icon icon-columns icon-large"></i> Export</button>
                    <!--<button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-success btn-large"><a href="javascript:Clickheretoprint()"><i
                                class="icon icon-print icon-large"></i> Print</a></button>-->
                </form>

                <div class="content" id="content">

                    <?php
                    //if($_GET['customerId'] > 0) {
                        ?>
                        <table class="table table-striped" style="background-color: white;" id="tblCustomerLiability">
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

                            $sql = "SELECT 
                                  DATE(fb1.flight_time) AS flight_time, s1.invoice_number, c.customer_name, c.customer_id, fpkg.package_name, d.category, fo1.code, fo1.offer_name, fb1.duration,
                                  fb1.from_flight_purchase_id, fb1.flight_purchase_id, fp1.deduct_from_balance
                                FROM
                                    sales s1
                                INNER JOIN flight_purchases fp1 ON
                                    s1.invoice_number = fp1.invoice_id
                                INNER JOIN flight_offers fo1 ON
                                    fp1.flight_offer_id = fo1.id
                                INNER JOIN flight_packages fpkg ON
                                    fo1.package_id = fpkg.id
                                INNER JOIN flight_bookings fb1 ON
                                    fb1.flight_purchase_id = fp1.id
                                INNER JOIN customer c ON
                                    fp1.customer_id = c.customer_id
                                LEFT JOIN discounts d ON
                                    fp1.discount_id = d.id
                                WHERE DATE(fb1.flight_time) >= ? AND DATE(fb1.flight_time) <= ?
                                AND customer_name NOT IN ('inflight staff flying', 'FDR', 'Maintenance', 'Training Inflight', 'Blocked for special things')
                                AND package_name NOT IN ('FDR', 'Marketing', 'Giveaways', 'Staff Flying')";
                                //AND d.category NOT IN ('Staff Flying')";

                            $sql .= " ORDER BY fb1.flight_time ASC";

                            $result = $db->prepare($sql);
                            $result->execute(array(
                                $d1,
                                $d2
                            ));

                            ?>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No.</th>
                                <th>Customer Name</th>
                                <th>Customer ID</th>
                                <th>Category</th>
                                <th>Item Code</th>
                                <th>Item Description</th>
                                <th>Minutes Consumed</th>
                                <th>Revenue in AED</th>
                                <th>VAT</th>
                            </tr>
                            <?php
                            $total_minutes = 0;
                            $total_price = 0;
                            if($result->rowCount() > 0) {
                                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

                                    $category = $row['category'];
                                    if(empty($category)) {
                                        $category = $row['package_name'];
                                    }

                                    $pre_2018_vat = 0;

                                    if ($row['from_flight_purchase_id'] > 0 || $row['deduct_from_balance'] == 2) {
                                        if ($row['deduct_from_balance'] == 2) {
                                            if ($category == NAVY_SEAL && strtotime($row['flight_time']) >= strtotime('2018-05-01')) {
                                                $per_minute_cost = getPerMinuteCostForCustomer($row['customer_id'], $d1, $d2);
                                            } else {
                                                $per_minute_cost = getPerMinuteCostForCustomer($row['customer_id']);
                                                $pre_2018_vat = round($row['duration'] * $per_minute_cost * 5 / 105, 2);
                                            }
                                        } else {
                                            $per_minute_cost = getPerMinuteCostOfPurchasedPackage($row['from_flight_purchase_id']);
                                        }
                                    } else {
                                        $per_minute_cost = getPerMinuteCostOfPurchasedPackage($row['flight_purchase_id']);
                                    }

                                    $total_minutes += $row['duration'];
                                    $current_price = $row['duration'] * $per_minute_cost;
                                    $total_price += $current_price;
                                    ?>
                                    <tr>
                                        <td><?= $row['flight_time'] ?></td>
                                        <td><?= $row['invoice_number'] ?></td>
                                        <td><?= $row['customer_name'] ?></td>
                                        <td><?= $row['customer_id'] ?></td>
                                        <td><?= $category ?></td>
                                        <td><?= $row['code'] ?></td>
                                        <td><?= $row['offer_name'] ?></td>
                                        <td><?= $row['duration'] ?></td>
                                        <td><?= number_format(round($current_price, 2));?></td>
                                        <td><?= $pre_2018_vat ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td colspan="7" style="text-align:right; padding-right: 50px;"><b>Total:</b></td>
                                    <td><b><?=number_format($total_minutes)?></b></td>
                                    <td><b><?=number_format($total_price)?></b></td>
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
        </div>
    </div>
</div>

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

