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
            <div class="contentheader hidden-print">
                <i class="icon-bar-chart"></i> End of Day Report
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">End of Day Report</li>
            </ul>

            <div style="margin-top: -19px; margin-bottom: 21px;" class="btns">

                <a href="index.php" class="btn btn-default btn-large" style="float: none;">
                    <i class="icon icon-circle-arrow-left icon-large"></i> Back
                </a>
                <button style="float:right; margin-right: 5px;" class="btn btn-success btn-large" onclick="window.print()">
                    Print
                </button>
                <button style="float:right; margin-right:5px;" class="btn btn-warning btn-large" onclick="convertToCSV()" id="exportCSV"/>
                Export
                </button>

                <a href="collect_meraas.php?verified=1" style="float:right; margin-right: 5px;" class="btn btn-info btn-large btnVerified" target="_blank" />
                    Verified
                </a>
                <br><br>


            </div>
            <form action="collect_meraas.php" method="get">
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
                ?>
                <table class="table table-bordered table-striped" id="tblSalesReport" style="text-align: left;">
                    <thead>
                    <tr>
                        <th colspan="11" style="text-align: center;">
                            <h3>End of Day Report from&nbsp;<?php echo date('M j, Y', strtotime($d1)) ?>&nbsp;to&nbsp;<?php echo date('M j, Y', strtotime($d2)) ?></h3>
                        </th>
                    </tr>
                    <tr>
                        <th width="16%"> Invoice Number</th>
                        <th width="13%"> Transaction Date</th>
                        <th width="13%"> Cash</th>
                        <th width="13%"> Card</th>
                        <th width="13%"> Online</th>
                        <th width="13%"> Customer ID</th>
                        <th width="20%"> Customer Name</th>
                        <th width="20%"> Service</th>
                        <th width="20%"> Merchandise</th>
                        <th width="18%"> VAT</th>
                        <th width="18%"> Net of VAT</th>
                    
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
                    $sql .= ' AND s.amount > 0 AND 
                        (s.mode_of_payment !="Account" AND s.mode_of_payment_1 != "Account" AND s.mode_of_payment !="credit_time" AND s.mode_of_payment_1 != "credit_time") ';
                    $sql .= " AND (c.customer_name != 'FDR' OR c.customer_name IS NULL)
                    ORDER by transaction_id DESC";

                    $result = $db->prepare($sql);
                    $result->bindParam(':a', $d1);
                    $result->bindParam(':b', $d2);
                    $result->execute();
                    $total_sale   = 0;
                    $total_profit = 0;
                    $total_cash   = 0;
                    $total_card   = 0;
                    $total_account = 0;
                    $total_online = 0;

                    for ($i = 0; $row = $result->fetch(PDO::FETCH_ASSOC); $i++) {
                        $current_cost = round($row['amount'], 0);
                        $discount = $current_cost * $row['discount'] / 100.00;

                        $query = $db->prepare('SELECT SUM(amount) AS totalService,
                        SUM(amount) - (SUM(amount) * SUM(discount) / 100) AS discountedService
                        FROM sales_order WHERE invoice = ? AND gen_name = "Service" LIMIT 1');
                        $query->execute([$row['invoice_number']]);
                        $row2 = $query->fetch();
                        $is_service_merchandise = ($row2['discountedService'] > 0);

                        $query = $db->prepare('SELECT SUM(amount) AS totalMerchandise,
                        SUM(amount) - (SUM(amount) * SUM(discount) / 100) AS discountedMerchandise
                        FROM sales_order WHERE invoice = ? AND gen_name = "Merchandise" LIMIT 1');
                        $query->execute([$row['invoice_number']]);
                        $row3 = $query->fetch();
                        $is_merchandise = ($row3['discountedMerchandise'] > 0);

                        $is_only_service = (!$is_merchandise && !$is_service_merchandise);

                        if ($is_only_service) {
                            $invoiceHref = 'flight_preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'];

                        } else {
                            $invoiceHref = 'preview.php?invoice=' . $row['invoice_number'] . '&sale_type=' . $row['sale_type'] . '&payfirst=&paysecond=&d1=' . $d1 . '&d2=' . $d2;
                        }
                        $total_sale += $current_cost;
                        $total_profit += $row['profit'];

                        if($row['mode_of_payment'] == 'Cash') {
                            $total_cash += $row['mop_amount'];
                        }
                        if($row['mode_of_payment_1'] == 'Cash') {
                            $total_cash += $row['mop1_amount'];
                        }

                        if($row['mode_of_payment'] == 'Card') {
                            $total_card += $row['mop_amount'];
                        }
                        if($row['mode_of_payment_1'] == 'Card') {
                            $total_card += $row['mop1_amount'];
                        }

                        if($row['mode_of_payment'] == 'Account') {
                            $total_account += $row['mop_amount'];
                        }
                        if($row['mode_of_payment_1'] == 'Account') {
                            $total_account += $row['mop1_amount'];
                        }

                        if($row['mode_of_payment'] == 'Online') {
                            $total_online += $row['mop_amount'];
                        }
                        if($row['mode_of_payment_1'] == 'Online') {
                            $total_online += $row['mop1_amount'];
                        }

                        ?>
                        <tr>
                            <td><a href='<?php echo $invoiceHref ?>'> <?php echo $row['invoice_number']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php  
                            if ($row['mode_of_payment']=='Cash') echo $row['mop_amount'];
                            if ($row['mode_of_payment_1']=='Cash') echo $row['mop1_amount'];?></td>
                            <td><?php
                            if ($row['mode_of_payment']=='Card') echo $row['mop_amount'];
                            if ($row['mode_of_payment_1']=='Card') echo $row['mop1_amount'];?></td>
                            <td><?php
                            if ($row['mode_of_payment']=='Online') echo $row['mop_amount'];
                            if ($row['mode_of_payment_1']=='Online') echo $row['mop1_amount'];?></td>
                            <td><?php
                            echo ($row['customer_id']) ? $row['customer_id'] : $row['customer_id']; ?></td>
                            <td><?php
                            echo ($row['customer_name']) ? $row['customer_name'] : $row['name']; ?></td>
                            <td><?php
                                if($is_only_service) {
                                    echo ($row['mop_amount'] + $row['mop1_amount']);

                                } else if($is_merchandise || $is_service_merchandise) {
                                    echo round($row2['discountedService'],2);
                                }
                            ?></td> 
                            <td><?php
                            if ($is_merchandise || $is_service_merchandise) {
                                echo round($row3['discountedMerchandise'], 2);
                            }
                            ?></td> 
                            
                            <td>
                                       <?php
                                        $vat_percent = "5%";
                                        $VAT = $row['amount'] *$vat_percent/105;
                                        //$vat_amount  = $vat_percent * $current_amount_w_discount / 105;
                                        echo number_format($VAT, 2);
                                        ?></td>

                           <td><?= number_format($current_cost-$VAT, 2); ?></td>
                            
                        </tr>
                        <?php
                    
                    }
                    ?>

                    <tr>
                        <td colspan="9" style="text-align: right;"> <b>Total:</b></td>
                        <td colspan="1" style=""><b><?= number_format($total_sale, 1) ?></b></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="9" style="text-align: right;"> <b>Cash:</b></td>
                        <td colspan="1" style=""><b><?= number_format($total_cash, 1) ?></b></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="9" style="text-align: right;"> <b>Card:</b></td>
                        <td colspan="1" style=""><b><?= number_format($total_card, 1) ?></b></td>
                        <td></td>
                    </tr>
                    <!--<tr>
                        <td colspan="9" style="text-align: right;"> <b>Account:</b></td>
                        <td colspan="1" style=""><b><?= number_format($total_account, 1) ?></b></td>
                    </tr>-->
                    <?php
                    if(strtolower($_SESSION['SESS_LAST_NAME']) == 'admin' || strtolower($_SESSION['SESS_LAST_NAME']) == ROLE_ACCOUNT
                        || $_SESSION[SESS_MOCK_ROLE] == ROLE_ACCOUNT) {
                        ?>
                        <tr>
                            <td colspan="9" style="text-align: right;"><b>Online:</b></td>
                            <td colspan="1" style=""><b><?= number_format($total_online, 1) ?></b></td>
                            <td></td>
                        </tr>
                        <?php
                    }
                    ?>

                   <!-- <tr>
                       <td colspan="9" style="text-align: right;"><b>Souq:</b></td>
                        <td colspan="1" style="padding-top:10px;">__________________</td>
                    </tr>-->

                    <?php
                    if($_SESSION['SESS_LAST_NAME'] == 'Operator' || $_SESSION[SESS_MOCK_ROLE] == ROLE_OPERATOR) {
                        ?>
                        <tr>
                            <td colspan="9" style="text-align: right;"><b>Operator:</b></td>
                            <td colspan="1" style=""><b><?= $_SESSION['SESS_FIRST_NAME'] ?></b></td>
                            <td></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan="9" style="text-align: right;"><b>Signature:</b></td>
                        <td colspan="1" style="padding-top:50px;">__________________</td>
                        <td></td>
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

    $(function() {

        <?php
        if(isset($_GET['verified'])) {
        ?>
        $('#tblSalesReport').css('border-collapse', 'collapse');
        $('#tblSalesReport, #tblSalesReport th, #tblSalesReport td')
            .css('border', '1px solid grey');

        $.ajax({
            url: 'api.php',
            method: 'POST',
            data: {
                'call': 'emailSalesReportToAdmin',
                'tableHtml': $('#tblSalesReport').parent().html()
            },
            dataType: "json",
            success: function (response) {
                alert('Email sent');
                window.top.close();
            },
        });
        <?php
        }
        ?>
    });

    function convertToCSV() {
        exportTableToCSV($('#tblSalesReport'), 'filename.csv');
    }

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
    }
</script>
<?php include('footer.php'); ?>
</html>