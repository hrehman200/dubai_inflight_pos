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

            <div id="maintable">
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
                            class="btn btn-success btn-large"><a href="javascript:Clickheretoprint()"><i
                                class="icon icon-print icon-large"></i> Print</a></button>
                </form>

                <?php
                if(!isset($_GET['d1'])) {
                    $first_day_this_month = date('m-01-Y');
                    $last_day_this_month  = date('m-t-Y');

                    $_GET['d1'] = $first_day_this_month;
                    $_GET['d2'] = $last_day_this_month;
                }
                ?>

                <div class="content" id="content">
                    <div style="font-weight:bold; text-align:center;font-size:14px;margin-bottom: 15px;">
                        Collection Report from&nbsp;<?php echo $_GET['d1'] ?>&nbsp;to&nbsp;<?php echo $_GET['d2'] ?>
                    </div>
                    <table class="table table-striped" style="background-color: white;">
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
                                  GROUP_CONCAT(so.name) AS product_name,
                                  GROUP_CONCAT(so.product_code) AS product_codes,
                                  SUM(so.qty) AS quantity,
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
                                  so.product,
                                  so.product_code,
                                  so.price,
                                  so.gen_name,
                                  so.name,
                                  so.qty,
                                  so.price,
                                  0 AS units_remaining,
                                  0 AS amount_liability
                                FROM
                                  sales s
                                INNER JOIN
                                  sales_order so ON s.invoice_number = so.invoice
                                LEFT JOIN
                                  customer c ON s.customer_id = c.customer_id
                                WHERE s.date >= :startDate AND s.date <= :endDate
                                GROUP BY
                                  s.transaction_id";

                        $result = $db->prepare($sql);
                        $result->execute(array(
                            'startDate' => $_GET['d1'],
                            'endDate' => $_GET['d2']
                        ));

                        while($row = $result->fetch()) {
                            ?>
                            <tr>
                                <td><?=$row['transaction_date']?></td>
                                <td><?=$row['cashier']?></td>
                                <td><?=$row['invoice_number']?></td>
                                <td><?=$row['sale_type']?></td>
                                <td><?=$row['customer_name']?></td>
                                <td><?=$row['mode_of_payment']. ($row['mode_of_payment_1']>0) ? ', '.$row['mode_of_payment_1'] : ''?></td>
                                <td><?=$row['product_name']?></td>
                                <td><?=$row['product_codes']?></td>
                                <td><?=$row['product_name']?></td>
                                <td>AED</td>
                                <td><?=$row['amount']?></td>
                                <td><?=$row['mode_of_payment']?></td>
                                <td><?=$row['mop_amount']?></td>
                                <td><?=$row['mode_of_payment_1']?></td>
                                <td><?=$row['mop1_amount']?></td>
                                <td><?=$row['quantity']?></td>
                                <td><?=$row['price']?></td>
                                <td><?=$row['discount']?></td>
                                <td>DiscountReason</td>
                                <td><?=$row['after_dis']?></td>
                                <td>Inflight Dubai</td>
                                <td>Margham Dubai</td>
                                <td>N/A</td>
                                <td>Sales</td>
                                <td>-</td>
                                <td><?=$row['product_name']?></td>
                                <td>POS</td>
                                <td><?=$row['amount']?></td>
                                <td><?=$row['units_remaining']?></td>
                                <td>Revenue On Consumed</td>
                                <td><?=$row['amount_liability']?></td>
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