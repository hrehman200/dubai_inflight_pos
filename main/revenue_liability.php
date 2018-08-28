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
                    <a href="<?=($_POST['military']==1) ? $_SERVER['REQUEST_URI'] : 'index.php'?>">
                        <button class="btn btn-default btn-large" style="float: none;"><i
                                class="icon icon-circle-arrow-left icon-large"></i> Back
                        </button>
                    </a>
                </div>
                <form action="revenue_liability.php" method="get">
                    From : <input type="text" name="d1" style="width: 223px; padding:14px;" class="tcal" value="" autocomplete="false"/>
                    To: <input type="text" style="width: 223px; padding:14px;" name="d2" class="tcal" value="" autocomplete="false"/>
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
                            <th>Avg/Min</th>
                            <th>AED Value</th>
                        </tr>
                        <?php

                        if($_POST['military'] == 1) {
                            $arr_revenue = json_decode(base64_decode($_POST['military_data']), true);

                        } else {
                            /** FTF */
                            $arr_revenue = getDataAndAggregate('FTF', $_GET['d1'], $_GET['d2']);

                            /** FT - Upsale */
                            $arr2 = getDataAndAggregate('FT - Upsale', $_GET['d1'], $_GET['d2']);
                            $arr_revenue = array_merge($arr_revenue, $arr2);

                            /** RF */
                            $arr2 = getDataAndAggregate('RF - Repeat Flights', $_GET['d1'], $_GET['d2']);
                            $arr_revenue = array_merge($arr_revenue, $arr2);

                            /** SKYDIVERS */
                            $arr2 = getDataAndAggregate('Skydivers', $_GET['d1'], $_GET['d2']);
                            $arr_revenue = array_merge($arr_revenue, $arr2);

                            $arr_military = [];
                            /** Military */
                            $arr2 = getDataAndAggregate('Military', $_GET['d1'], $_GET['d2']);
                            $arr_military = array_merge($arr_military, $arr2);

                            /** Navy Seal */
                            $arr2 = getDataAndAggregate('Navy Seal', $_GET['d1'], $_GET['d2']);
                            $arr_military = array_merge($arr_military, $arr2);

                            /** Presidential Guard */
                            $arr2 = getDataAndAggregate('Presidential Guard', $_GET['d1'], $_GET['d2']);
                            $arr_military = array_merge($arr_military, $arr2);

                            /** Sky god */
                            $arr2 = getDataAndAggregate('Sky god%', $_GET['d1'], $_GET['d2']);
                            $arr_military = array_merge($arr_military, $arr2);

                            $arr_military_sum[0] = [
                                'package_name' => 'Military',
                                'paid' => array_sum(array_column($arr_military, 'paid')),
                                'total_minutes' => array_sum(array_column($arr_military, 'total_minutes')),
                                'minutes_used' => array_sum(array_column($arr_military, 'minutes_used')),
                                'aed_value' => array_sum(array_column($arr_military, 'aed_value')),
                                'avg_per_min' => array_sum(array_column($arr_military, 'avg_per_min')),
                            ];

                            $arr_revenue = array_merge($arr_revenue, $arr_military_sum);

                            // just for heading
                            $arr_revenue[] = ['package_name' => 'Other Revenue'];

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
                            if($row['package_name'] == 'Other Revenue') {
                                ?>
                                <tr>
                                    <td colspan="6" bgcolor="#eeeeee"><b><?=$row['package_name']?></b></td>
                                </tr>
                                <?php
                                continue;
                            }
                            ?>
                            <tr class="<?=$row['package_name']=='Military'?'military-row':''?>">
                                <td><b><?= $row['package_name'] == 'Military' && $_POST['military'] == 1 ? 'Military Individuals' : $row['package_name']  ?></b></td>
                                <td><?= number_format($row['paid'], 1) ?></td>
                                <td><?= number_format($row['total_minutes']) ?></td>
                                <td><?= number_format($row['minutes_used']) ?></td>
                                <td><?
                                    if($row['package_name'] == 'Military' && $_POST['military'] != 1) {
                                        $military_avg_min = $row['avg_per_min'];
                                    } else {
                                        echo number_format($row['avg_per_min'], 2);
                                    }
                                    ?>
                                </td>
                                <td><?= number_format($row['aed_value'], 2) ?></td>
                            </tr>
                            <?php
                        }

                        $arr_packages = json_encode(array_map(function($v) {
                            if(isset($_POST['military']) && $v['package_name'] == 'Military') {
                                return 'Military Individuals';
                            }
                            return $v['package_name'];
                        }, $arr_revenue));
                        $arr_paid = json_encode(array_map(function($v) { return round($v['aed_value'], 1); }, $arr_revenue));
                        ?>
                        <tr>
                            <td><b>Total:</b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'paid')), 1) ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'total_minutes'))) ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'minutes_used'))) ?></b></td>
                            <td><b><?
                                    $avg_min_sum = array_sum(array_column($arr_revenue, 'avg_per_min'));
                                    $avg_min_sum -= $military_avg_min;
                                    echo number_format($avg_min_sum);
                                    ?></b></td>
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
                            <div class="span10 offset1">
                                <table class="table">
                                    <tr>
                                        <th>FTF</th>
                                        <th>Used (min)</th>
                                        <th>Experienced</th>
                                        <th>Used (min)</th>
                                        <th>Military</th>
                                        <th>Used (min)</th>
                                    </tr>
                                    <tr>
                                        <td>Weekend</td>
                                        <td><?=(int)getMinutesFlownInPackages(['FTF'], $_GET['d1'], $_GET['d2'], 'weekends')['FTF']?></td>
                                        <td>Weekend</td>
                                        <td><?php
                                            $flown = getMinutesFlownInPackages(['Skydivers'], $_GET['d1'], $_GET['d2'], 'weekends');
                                            echo (int)$flown['Skydivers'] + (int)$flown['Experienced-Return Flyers '];
                                        ?></td>
                                        <td>Weekend</td>
                                        <td><?=(int)getMinutesFlownInPackages(['Military'], $_GET['d1'], $_GET['d2'], 'weekends')['Military']?></td>
                                    </tr>
                                    <tr>
                                        <td>Weekday</td>
                                        <td><?=(int)getMinutesFlownInPackages(['FTF'], $_GET['d1'], $_GET['d2'], 'weekdays')['FTF']?></td>
                                        <td>Weekday</td>
                                        <td><?php
                                            $flown = getMinutesFlownInPackages(['Skydivers'], $_GET['d1'], $_GET['d2'], 'weekdays');
                                            echo (int)$flown['Skydivers'] + (int)$flown['Experienced-Return Flyers '];
                                        ?></td>
                                        <td>Weekday</td>
                                        <td><?=(int)getMinutesFlownInPackages(['Military'], $_GET['d1'], $_GET['d2'], 'weekdays')['Military']?></td>
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

