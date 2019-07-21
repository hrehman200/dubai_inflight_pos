<?php
include('header.php');
if (!$_GET['y']) {
    $_GET['y'] = 2018;
}
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
                <i class="icon-bar-chart"></i> Unconsumed Revenue
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Unconsumed Revenue</li>
            </ul>

            <div>
                <div style="margin-top: -19px; margin-bottom: 21px;">
                    <a href="<?= (null !== $_POST['pageType']) ? $_SERVER['REQUEST_URI'] : 'index.php' ?>">
                        <button class="btn btn-default btn-large" style="float: none;"><i
                                    class="icon icon-circle-arrow-left icon-large"></i> Back
                        </button>
                    </a>
                </div>

                <div class="content" id="content">
                    Year:
                    <select id="year">
                        <option <?= $_GET['y'] == 2017 ? 'selected' : '' ?> value="2017">Pre 2018</option>
                        <option <?= $_GET['y'] == 2018 ? 'selected' : '' ?> >2018</option>
                        <option <?= $_GET['y'] == 2019 ? 'selected' : '' ?> >2019</option>
                    </select>

                    <div class="app">
                        <pie-chart></pie-chart>
                    </div>

                    <table class="table table-striped" id="tblUnconsumedRev">
                        <tr>
                            <th width="">Package</th>
                            <th>Customer</th>
                            <th>Invoice No.</th>
                            <th width="100">Purchase Date</th>
                            <th width="100">Expiry Date</th>
                            <th>Expired Minutes</th>
                            <th>Revenue on Expired Minutes</th>
                        </tr>
                        <?php
                        $rows = getUnconsumedRevenueForYear($_GET['y']);
                        $total_min = 0;
                        $total_rev = 0;
                        $grouped = [];
                        foreach ($rows as $row) {
                            $total_min += $row['minutes'];
                            $total_rev += $row['cost'];
                            ?>
                            <tr>
                                <td><?= $row['offer_name'] ?></td>
                                <td><?= $row['customer_name'] ?></td>
                                <td><?= $row['invoice_id'] ?></td>
                                <td><?= $row['purchase_date'] ?></td>
                                <td><?= $row['expired_date'] ?></td>
                                <td><?= $row['minutes'] ?></td>
                                <td><?= $row['cost'] ?></td>
                            </tr>
                            <?php

                            $package_name = str_replace("\r\n", "", $row['package_name']);
                            if(stripos($row['discount_name'], 'military') !== false) {
                                $package_name = 'Military';
                            }
                            if(stripos($row['discount_name'], 'navy seal') !== false) {
                                $package_name = 'Navy Seal';
                            }
                            if(stripos($row['discount_name'], 'presidentail') !== false) {
                                $package_name = 'Presidential Guard';
                            }
                            if(stripos($row['package_name'], 'ftf') === 0) {
                                $package_name = 'FTF';
                            }
                            if(!array_key_exists($package_name, $grouped)) {
                                $grouped[$package_name] = $row['cost'];
                            } else {
                                $grouped[$package_name] += $row['cost'];
                            }

                        }
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: right"><b>Total: </b></td>
                            <td><b><?= $total_min ?></b></td>
                            <td><b><?= $total_rev ?></b></td>
                        </tr>
                    </table>

                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<script type="text/javascript" src="js/chart.min.js"></script>
<script type="text/javascript" src="js/vue.min.js"></script>
<script type="text/javascript" src="js/vue-chartjs.min.js"></script>
<script type="text/javascript" src="js/chart.piecelabel.js"></script>
<script type="text/javascript">

    var arr = JSON.parse('<?=json_encode(array_values($grouped))?>');
    var arrColors = [];
    for(var i in arr) {
        var randomColor = "#"+((1<<24)*Math.random()|0).toString(16);
        arrColors.push(randomColor);
    }

    Vue.component('pie-chart', {
        extends: VueChartJs.Pie,
        mounted () {
            this.renderChart({
                labels: JSON.parse('<?=json_encode(array_keys($grouped))?>'),
                datasets: [
                    {
                        label: 'Data One',
                        data: arr,
                        backgroundColor: arrColors
                    }
                ]
            }, {
                responsive: true,
                maintainAspectRatio: false,
                pieceLabel: {
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
<script type="text/javascript">

    $('#year').on('change', function (e) {
        window.location.href = 'unconsumed_revenue.php?y=' + $(this).val();
    });

    function convertToCSV() {
        exportTableToCSV($('#tblUnconsumedRev'), 'rnl.csv');
    }

    function exportTableToCSV($table, filename) {

        var $rows = $table.find('tr:has(td,th)'),

            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

            // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',

            // Grab text from table into CSV formatted string
            csv = '"' + $rows.map(function (i, row) {
                    var $row = $(row),
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
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        blob = new Blob([csvData], {type: 'text/csv;charset=utf8;'}); //new way
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
<style>
    .clickable-row {
        cursor: hand;
    }
</style>


