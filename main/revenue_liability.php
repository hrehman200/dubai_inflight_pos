<?php
include('header.php');
ini_set('max_execution_time', 1800);
set_time_limit(1800);
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
                    <a href="<?=(null !== $_POST['pageType']) ? $_SERVER['REQUEST_URI'] : 'index.php'?>">
                        <button class="btn btn-default btn-large" style="float: none;"><i
                                class="icon icon-circle-arrow-left icon-large"></i> Back
                        </button>
                    </a>
                </div>
                <form id="formRnL" action="revenue_liability.php" method="get">
                    From : <input type="text" id="d1" name="d1" style="width: 223px; padding:14px;" class="tcal" value="" autocomplete="false"/>
                    To: <input type="text" style="width: 223px; padding:14px;" id="d2" name="d2" class="tcal" value="" autocomplete="false"/>
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

                        $clickable_rows = ['Military', 'FTF', 'Groupon', 'Cobone', 'Corporate Discount', 'B2B', 'Retail Revenue'];

                        // for current day we will need to use old scheme of getting data freshly
                        if(date('Y-m-d') == $_GET['d1'] && date('Y-m-d') == $_GET['d2']) {

                            if($_POST['pageType'] == 'Military') {
                                $arr_revenue = json_decode(base64_decode($_POST['military_data']), true);

                            } else if($_POST['pageType'] == 'FTF') {
                                // so that arr_ftf can be passed to next level
                                $arr_ftf = json_decode(base64_decode($_POST['ftf_data']), true);
                                $arr_revenue = $arr_ftf;

                            } else if($_POST['pageType'] == 'Retail Revenue') {
                                $arr_retail = json_decode(base64_decode($_POST['retail_data']), true);
                                $arr_revenue = $arr_retail;

                            } else if(in_array($_POST['pageType'], $clickable_rows)) {
                                $arr = json_decode(base64_decode($_POST['ftf_data']), true);
                                $arr = array_filter($arr, function($item) {
                                    return array_key_exists($_POST['pageType'], $item);
                                });
                                $arr = array_values($arr);
                                $arr_revenue = $arr[0][$_POST['pageType']];

                            } else {
                                $arr = getRnLForCurrentDay($_GET['d1'], $_GET['d2']);
                                $arr_revenue = $arr['arr_revenue'];
                                $arr_ftf = $arr['arr_ftf'];
                                $arr_military = $arr['arr_military'];
                                $arr_retail = $arr['arr_retail'];
                            }

                        } else if(isset($_POST['pageType'])) {
                            $arr_revenue = getRnL($_GET['d1'], $_GET['d2'], $_POST['pageType']);

                        } else {
                            $arr_revenue = getRnL($_GET['d1'], $_GET['d2']);

                            $arr_military = getRnL($_GET['d1'], $_GET['d2'], 'Military', 1);
                            $arr_retail = getRnL($_GET['d1'], $_GET['d2'], 'Retail Revenue', 1);

                            $arr_revenue = array_merge($arr_revenue, $arr_military);
                            $arr_revenue = array_merge($arr_revenue, $arr_retail);
                        }

                        foreach ($arr_revenue as $row) {
                            if($row['package_name'] == 'Retail Revenue') {
                                ?>
                                <tr>
                                    <td colspan="6" bgcolor="#eeeeee"><b>Revenue other than Tunnel</b></td>
                                </tr>
                                <?php
                            }

                            $display_title = $row['package_name'];
                            if($row['package_name']=='Military' && $_POST['pageType'] == 'Military') {
                                $display_title = 'Military Individuals';
                            }

                            ?>
                            <tr class="<?=in_array($row['package_name'], $clickable_rows)?'clickable-row':''?>" data-page-type="<?=$row['package_name']?>">
                                <td><b><?= $display_title  ?></b></td>
                                <td><?= number_format($row['paid']) ?></td>
                                <td><?= number_format($row['total_minutes']) ?></td>
                                <td><?= number_format($row['minutes_used']) ?></td>
                                <td><?
                                    if($row['package_name'] == 'Military' && $_POST['pageType'] != 'Military') {
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
                            if($_POST['pageType'] == 'Military' && $v['package_name'] == 'Military') {
                                return 'Military Individuals';
                            }
                            return $v['package_name'];
                        }, $arr_revenue));
                        $arr_paid = json_encode(array_map(function($v) { return round($v['aed_value'], 1); }, $arr_revenue));
                        ?>
                        <tr>
                            <td><b>Total:</b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'paid'))) ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'total_minutes'))) ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'minutes_used'))) ?></b></td>
                            <td><b><?
                                    $avg_min_sum = array_sum(array_column($arr_revenue, 'avg_per_min'));
                                    $avg_min_sum -= $military_avg_min;
                                    //echo number_format($avg_min_sum);
                                    ?></b></td>
                            <td><b><?= number_format(array_sum(array_column($arr_revenue, 'aed_value')), 2) ?></b></td>
                        </tr>
                    </table>

                    <form id="military-form" method="POST" action="<?=$_SERVER['REQUEST_URI']?>" target="_blank">
                        <input type="hidden" name="pageType" value="Military" />
                        <input type="hidden" name="military_data" value="<?=base64_encode(json_encode($arr_military))?>" />
                        <input type="hidden" name="ftf_data" value="<?=base64_encode(json_encode($arr_ftf))?>" />
                        <input type="hidden" name="retail_data" value="<?=base64_encode(json_encode($arr_retail))?>" />
                    </form>

                    <div class="app">
                        <pie-chart></pie-chart>
                    </div>

                    <hr/>

                    <?php
                    if(!isset($_POST['pageType'])) {
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

    $('#formRnL').on('submit', function(e) {
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        var yyyy = today.getFullYear();
        today = mm + '/' + dd + '/' + yyyy;
        if(($('#d1').val() == today || $('#d2').val() == today) && $('#d1').val() != $('#d2').val()) {
            bootbox.alert("If you are searching for today's data, make sure start date and end date match.");
            return false;
        }
    });

    $('.clickable-row').click(function(e) {
        $('input[name="pageType"]').val($(this).data('page-type'));
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

    var arr = JSON.parse('<?=$arr_paid?>');
    var arrColors = [];
    for(var i in arr) {
        var randomColor = "#"+((1<<24)*Math.random()|0).toString(16);
        arrColors.push(randomColor);
    }

    Vue.component('pie-chart', {
        extends: VueChartJs.Pie,
        mounted () {
            this.renderChart({
                labels: JSON.parse('<?=$arr_packages?>'),
                datasets: [
                    {
                        label: 'Data One',
                        data: JSON.parse('<?=$arr_paid?>'),
                        backgroundColor: arrColors //['#F7DF00', '#ca0813', '#287AEB', '#89A366', '#9F7371', '#72D84E', '#42C4F0', '#f4c141', '#4286f4', '#f441e5']
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
    .clickable-row {
        cursor:hand;
    }
</style>
</html>

