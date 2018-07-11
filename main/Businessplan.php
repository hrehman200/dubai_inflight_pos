<?php
include('header.php');

$csv = array();

// check there are no errors
if(isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == 0){
    $name = $_FILES['csvFile']['name'];    // 2018.csv
    $year = str_ireplace('.csv', '', $name);    // 2018
    $ext = strtolower(end(explode('.', $_FILES['csvFile']['name'])));
    $type = $_FILES['csvFile']['type'];
    $tmpName = $_FILES['csvFile']['tmp_name'];

    // check the file is a csv
    if($ext === 'csv'){
        if(($handle = fopen($tmpName, 'r')) !== FALSE) {
            // necessary if a large csv file
            set_time_limit(0);

            $row = 0;
            $parent_entity = '';
            $parent_entity_id = 0;

            while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if($row > 0) {  // ignore header
                    if(stripos($data[0], 'total') === false) {
                        if ($data[0] != '' && $data[2] == '') {
                            $parent_entity = $data[0];
                            $parent_entity_id = getParentEntityId($parent_entity);

                        } else if($data[0] != '') {
                            $entity_name = $data[0];
                            for ($col = 2; $col <= 13; $col++) {
                                $entity_id = getBusinessEntityId($entity_name, $parent_entity_id);
                                $month = date("M", mktime(0, 0, 0, $col - 1, 10));
                                updateBusinessEntityValue($entity_id, $year, $month, $data[$col]);
                            }
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
        }
    }
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
            <div class="contentheader">
                <i class="icon-group"></i> Business Plan
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Business Plan</li>
            </ul>

            <form id="bpForm" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
                <input type="hidden" name="monthIndex" id="monthIndex" value="<?= $_REQUEST['monthIndex'] ?>"/>
                <select id="year" name="year">
                    <?php
                    for ($year = 2017; $year <= 2027; $year++) {
                        echo sprintf('<option %s>%d</option>', $_REQUEST['year'] == $year ? 'selected' : '', $year);
                    }
                    ?>
                </select>

                From:
                <select id="fromMonth" name="fromMonth">
                    <option>Jan</option>
                    <option>Feb</option>
                    <option>Mar</option>
                    <option>Apr</option>
                    <option>May</option>
                    <option>Jun</option>
                    <option>Jul</option>
                    <option>Aug</option>
                    <option>Sep</option>
                    <option>Oct</option>
                    <option>Nov</option>
                    <option>Dec</option>
                </select>

                To:
                <select id="toMonth" name="toMonth">
                    <option>Jan</option>
                    <option>Feb</option>
                    <option>Mar</option>
                    <option>Apr</option>
                    <option>May</option>
                    <option>Jun</option>
                    <option>Jul</option>
                    <option>Aug</option>
                    <option>Sep</option>
                    <option>Oct</option>
                    <option>Nov</option>
                    <option>Dec</option>
                </select>

                <div class="pull-right">
                    <label for="chk1" >Budget</label>
                    <input type="radio" id="chk1" name="chkSheetFormat" value="1" />
                    <label for="chk2" >Actual</label>
                    <input type="radio" id="chk2" name="chkSheetFormat" value="2" />
                    <label for="chk3" >Reconsilation</label>
                    <input type="radio" id="chk3" name="chkSheetFormat" value="3" />
    
                    <button class="btn btnUploadCsv"> <i class="icon-upload icon-large"></i> Upload CSV </button>
                    <input type="file" id="csvFile" name="csvFile" style="display:none">

                    <Button type="button" onclick="convertToCSV()" id="exportCSV" class="btn btn-info">
                        <i class="icon-plus-sign icon-large"></i> Export
                    </button>
                </div>

            </form>

            <table class="table table-bordered">

                <?php
                $year               = isset($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');
                $month_index        = (int)$_REQUEST['monthIndex'];
                $all_months         = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
                $month_placeholders = rtrim(str_repeat('?, ', count($all_months)), ', ');
                $from_month         = isset($_REQUEST['fromMonth']) ? $_REQUEST['fromMonth'] : 'Jan';
                $to_month           = isset($_REQUEST['toMonth']) ? $_REQUEST['toMonth'] : 'Dec';
                $from_month_index   = array_search($from_month, $all_months);
                $to_month_index     = array_search($to_month, $all_months);
                $months             = array_slice($all_months, $from_month_index, $to_month_index+1);
                $start_end_dates    = [];
                foreach($months as $m) {
                    $start_end_dates[] = getStartEndDateFromMonthYear($year, $m);
                }
                $year_start_date    = "{$year}-01-01";
                $year_end_date      = "{$year}-12-31";


                function getFYEstimatedForEntity($entity_name, $entity_id = null) {

                    global $db, $all_months, $month_placeholders, $year;

                    $query = "SELECT SUM(value) AS value FROM business_plan_yearly bpy
                      INNER JOIN business_plan_entities bpe ON bpy.business_plan_entity_id = bpe.id
                      WHERE ";
                    if ($entity_id != null) {
                        $query .= "bpe.id = " . $entity_id;
                    } else {
                        $query .= "bpe.name = '" . $entity_name . "'";
                    }
                    $query .= " AND bpy.month IN ($month_placeholders) AND year = ?";
                    $stmt  = $db->prepare($query);
                    $stmt->execute(array_merge($all_months, array($year)));
                    $row = $stmt->fetch();

                    return round($row['value'], 1);
                }

                function getFlightSaleForMonth($offer_name, $month, $year) {
                    global $db;
                    $query = "SELECT SUM(s.amount) AS amount FROM sales s
                        INNER JOIN flight_purchases fp ON s.invoice_number = fp.invoice_id
                        INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                        WHERE fo.offer_name LIKE '%" . $offer_name . "%'
                        AND s.month = :month
                        AND s.year = :year";

                    $stmt = $db->prepare($query);
                    $stmt->execute(array(
                        ':month' => $month,
                        ':year'  => $year
                    ));
                    $row = $stmt->fetch();

                    return round($row['amount'], 1);
                }

                function getFlightSaleForYear($offer_name, $year) {
                    global $db, $month_placeholders, $all_months;
                    $stmt = $db->prepare("SELECT SUM(s.amount) AS amount FROM sales s
                        INNER JOIN flight_purchases fp ON s.invoice_number = fp.invoice_id
                        INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                        WHERE fo.offer_name LIKE '%" . $offer_name . "%'
                        AND s.month IN ($month_placeholders)
                        AND s.year = ?");
                    $stmt->execute(array_merge($all_months, array($year)));
                    $row = $stmt->fetch();

                    return round($row['amount'], 1);
                }

                // --------------- MERCHANDISE ----------------
                function getMerchandiseSale($month, $year) {
                    global $db;
                    $stmt_merchandise = $db->prepare("SELECT SUM(amount) AS amount FROM sales WHERE sale_type = 'Merchandise'
                      AND month = :month AND year = :year");
                    $stmt_merchandise->execute(array(
                        ':month' => $month,
                        ':year'  => $year
                    ));
                    $row = $stmt_merchandise->fetch();

                    return round($row['amount'], 1);
                }

                $arr_merhandise = [];
                foreach($months as $m) {
                    $arr_merhandise[] = getMerchandiseSale($m, $year);
                }

                $stmt = $db->prepare("SELECT SUM(amount) AS amount FROM sales WHERE sale_type = 'Merchandise'
                  AND month IN ($month_placeholders) AND year = ?");
                $stmt->execute(array_merge($all_months, array($year)));
                $row               = $stmt->fetch();
                $total_merchandise = $row['amount'];

                $fy_estimated_merchandise = getFYEstimatedForEntity(null, 9);

                $arr_values = [];

                // --------------- FTF ----------------
                foreach($months as $m) {
                    $arr_values['FTF'][] = getFlightSaleForMonth('FTF', $m, $year);
                }
                $arr_values['FTF']['total'] = getFlightSaleForYear('FTF', $year);
                $arr_values['FTF']['totalEstimated'] = getFYEstimatedForEntity('FTF');

                // --------------- COGS Merchandise ----------------
                $fy_estimated_merchandise_cogs = getFYEstimatedForEntity(null, 24);

                // --------------- SKyDivers ----------------
                foreach($months as $m) {
                    $arr_values['Skydivers'][] = getFlightSaleForMonth('Skydivers', $m, $year);
                }
                $arr_values['Skydivers']['total'] = getFlightSaleForYear('Skydivers', $year);
                $arr_values['Skydivers']['totalEstimated'] = getFYEstimatedForEntity('SkyDivers');

                // Presidential Guard
                for($i=0; $i<count($start_end_dates); $i++) {
                    $arr_values[PRESIDENTIAL_GUARD][] = getDataAndAggregate(PRESIDENTIAL_GUARD, $start_end_dates[$i]['start'], $start_end_dates[$i]['end'])[0]['paid'];
                    $arr_values[NAVY_SEAL][] = getDataAndAggregate(NAVY_SEAL, $start_end_dates[$i]['start'], $start_end_dates[$i]['end'])[0]['paid'];
                    $arr_values[MILITARY_INDIVIDUALS][] = getDataAndAggregate(MILITARY_INDIVIDUALS, $start_end_dates[$i]['start'], $start_end_dates[$i]['end'])[0]['paid'];
                }
                $arr_values[PRESIDENTIAL_GUARD]['total'] = getDataAndAggregate(PRESIDENTIAL_GUARD, $year_start_date, $year_end_date)[0]['paid'];
                $arr_values[PRESIDENTIAL_GUARD]['totalEstimated'] = getFYEstimatedForEntity(PRESIDENTIAL_GUARD);
                $arr_values[NAVY_SEAL]['total'] = getDataAndAggregate(NAVY_SEAL, $year_start_date, $year_end_date)[0]['paid'];
                $arr_values[NAVY_SEAL]['totalEstimated'] = getFYEstimatedForEntity(NAVY_SEAL);
                $arr_values[MILITARY_INDIVIDUALS]['total'] = getDataAndAggregate(MILITARY_INDIVIDUALS, $year_start_date, $year_end_date)[0]['paid'];
                $arr_values[MILITARY_INDIVIDUALS]['totalEstimated'] = getFYEstimatedForEntity(MILITARY_INDIVIDUALS);
                ?>

                <thead id="tblHead">
                <tr>
                    <th>
                    </th>
                    <?php
                    foreach($months as $m) {
                        ?>
                        <th class="budget"><?= $_REQUEST['year'] ?><br/><?= $m ?></th>
                        <th class="actual"><?= $_REQUEST['year'] ?><br/><?= $m ?></th>
                    <?php
                    }
                    ?>
                    <th class="budget"><?= $_REQUEST['year'] ?><br/>FY Total Estimated</th>
                    <th class="actual"><?= $_REQUEST['year'] ?><br/>FY Total</th>
                    <th><?= $_REQUEST['year'] ?><br/>Deviation</th>
                </tr>
                </thead>
                <tbody>
                <?php

                function getMonthValue($needle, $arr) {
                    $index = array_search($needle, array_map(function ($v) {
                        return $v['month'];
                    }, $arr));

                    return $index !== false ? $arr[$index]['value'] : 0;
                }

                function getNPercentOf($n, $total) {
                    return round($n * $total / 100, 1);
                }

                $stmt_merchandise = $db->prepare("SELECT * FROM business_plan_entities WHERE parent_id = 0");
                $stmt_merchandise->execute();
                while ($row = $stmt_merchandise->fetch()) {
                    ?>
                    <tr class="rowParent">
                        <td>
                            <button class="btn btn-small btn-secondary btnParentRow" data-parent-id="<?= $row['id'] ?>">
                                +
                            </button>
                            <b><span><?= $row['name'] ?></span></b></td>
                        <?php
                        foreach($months as $m) {
                            ?>
                            <td class="budget"></td>
                            <td class="actual"></td>
                            <?php
                        }
                        ?>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php
                    $sql     = "SELECT bpe.id, bpe.parent_id, bpe.name, bpy.month, bpy.value
                      FROM business_plan_entities bpe
                      LEFT JOIN business_plan_yearly bpy ON bpy.business_plan_entity_id = bpe.id AND bpy.year = :years
                      WHERE bpe.parent_id = :parentId";
                    $result2 = $db->prepare($sql);
                    $arr     = array(
                        ':parentId' => $row['id'],
                        ':years'    => $_REQUEST['year']
                    );
                    $result2->execute($arr);

                    $arr_to_display = array();
                    while ($row2 = $result2->fetch()) {
                        $arr_to_display[$row2['name']][] = array(
                            'month'     => $row2['month'],
                            'value'     => $row2['value'],
                            'id'        => $row2['id'],
                            'parent_id' => $row2['parent_id']
                        );
                    }

                    $is_cogs = ($row['name'] == 'Cost of Goods Sold (COGS)');

                    foreach ($arr_to_display as $entity_name => $arr_monthwise_data) {
                        ?>
                        <tr class="row_<?= $arr_monthwise_data[0]['parent_id'] ?>">
                            <td><?= $entity_name ?></td>

                            <?php
                            for($i=0; $i<count($months); $i++) {
                                ?>
                                <td class="budget">
                                    <?= getMonthValue($months[$i], $arr_monthwise_data) ?>
                                </td>
                                <td class="actual">
                                    <?php
                                    switch ($entity_name) {
                                        case 'Merchandise':
                                            if ($is_cogs) {
                                                echo getNPercentOf(30, $arr_merhandise[$i]);
                                            } else {
                                                echo $arr_merhandise[$i];
                                            }
                                            break;
                                        default:
                                            echo $arr_values[$entity_name][$i];
                                    }
                                    ?>
                                </td>
                                <?php
                            }
                            ?>

                            <td class="fyEstimted budget">
                                <?php
                                switch ($entity_name) {
                                    case 'Merchandise':
                                        if ($is_cogs) {
                                            echo $fy_estimated_merchandise_cogs;
                                        } else {
                                            echo $fy_estimated_merchandise;
                                        }
                                        break;
                                    default:
                                        echo $arr_values[$entity_name]['totalEstimated'];
                                }
                                ?>
                            </td>
                            <td class="actual">
                                <?php
                                switch ($entity_name) {
                                    case 'Merchandise':
                                        if ($is_cogs) {
                                            $total_merchandise = getNPercentOf(30, $total_merchandise);
                                        }
                                        echo $total_merchandise;
                                        break;
                                    default:
                                        echo $arr_values[$entity_name]['total'];
                                }
                                ?>
                            </td>
                            <td class="derivation">
                                <?php
                                switch ($entity_name) {
                                    case 'Merchandise':
                                        if ($is_cogs) {
                                            echo round($fy_estimated_merchandise_cogs - $total_merchandise, 1);
                                        } else {
                                            echo round($fy_estimated_merchandise - $total_merchandise, 1);
                                        }
                                        break;
                                    default:
                                        echo $arr_values[$entity_name]['totalEstimated'] - $arr_values[$entity_name]['total'];
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr class="rowTotal" data-parent-id="<?= $row['id'] ?>">
                        <td data-index="0"><b>Total</b></td>
                        <?php
                        $count = 0;
                        for($i=0; $i<count($months)*2; $i+=2) {
                            echo sprintf('<td data-index="%d" class="budget"></td>', $i+1);
                            echo sprintf('<td data-index="%d" class="actual"></td>', $i+2);
                            $count+=2;
                        }
                        ?>
                        <td data-index="<?=$count+1?>"></td>
                        <td data-index="<?=$count+2?>"></td>
                        <td data-index="<?=$count+3?>"></td>
                    </tr>
                    <tr>
                        <td colspan="<?=count($months)+4?>">&nbsp;</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function convertToCSV() {
        exportTableToCSV($('#resultTable'), 'filename.csv');
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

        // $(this)
        //     .attr({
        //     'download': filename,
        //         'href': csvData,
        //         'target': '_blank'
        // });
    }
</script>


</body>
<?php include('footer.php'); ?>

</html>

<style>
    select{
        width: 100px;
    }

    label {
        display: inline;
    }

    div * {
        vertical-align: baseline;
    }

    th, td {
        background-color: white;
    }

    .actual {
        background-color: #ceffc2
    }

    .budget {
        background-color: #fff6b4
    }

    .rowParent td, .rowTotal td {
        background-color: lightgrey;
        font-weight: bold;
    }
</style>

<script type="text/javascript">

    $('#year, #fromMonth, #toMonth').on('change', function (e) {
        $(e.target).parent().submit();
    });

    $('#fromMonth').prop('selectedIndex', <?=$from_month_index?>);
    $('#toMonth').prop('selectedIndex', <?=$to_month_index?>);

    $('.btnParentRow').on('click', function (e) {
        var parentId = $(this).data('parent-id');
        $('.row_' + parentId).toggleClass('hidden');
    });

    $('input[type="text"]').off('blur').on('blur', function (e) {
        var tdIndex   = $(e.target).data('index');
        var monthYear = $('#tblHead th:eq(' + tdIndex + ')').html();
        monthYear     = monthYear.split("<br>");
        var month     = monthYear[1];
        var year      = $('#year').val();
        var entityId  = $(e.target).data('entity-id');
        var value     = $(e.target).val();

        if (value != '') {
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: {
                    'call': 'saveBusinessPlanRow',
                    'entityId': entityId,
                    'month': month,
                    'year': year,
                    'value': value
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success == 1) {
                        _recalculate();
                    }
                }
            });
        }
    });

    var _recalculate = function () {
        $('.rowTotal td').each(function (index, obj) {
            var _index = $(this).data('index');
            if (_index != 0) {
                var rowTotal = $(this).parent();
                var parentId = rowTotal.data('parent-id');
                var prevRows = rowTotal.prevAll('.row_' + parentId);
                var sum      = 0;
                $(prevRows).find('td:eq(' + _index + ')').each(function (index2, obj2) {
                    var input = $(obj2).find('input[type="text"]');
                    var value = 0;
                    if (input.length > 0) {
                        value = $(input).val();
                    } else {
                        value = $(obj2).html();
                    }
                    console.log(obj2);
                    value = Number(value);
                    sum += value;
                });
                $(obj).html(sum.toFixed(1));
            }
        });
    };

    _recalculate();

    $('td:contains("EBITDA")').css('background-color', 'yellow')
        .siblings().css('background-color', 'yellow');

    $('input[name="chkSheetFormat"]').on('change', function(e) {
        console.log($(this).val());
        switch(Number($(this).val())) {
            case 1:
                $('.budget').show();
                $('.actual').hide();
                break;
            case 2:
                $('.budget').hide();
                $('.actual').show();
                break;
            case 3:
                $('.budget').show();
                $('.actual').show();
                break;
        }
    });

    $('.btnUploadCsv').on('click', function (e) {
        e.preventDefault();
        $('#csvFile').click();
    });

    $('#csvFile').on('change', function () {
        if($('#csvFile').val()) {
            $('#bpForm').submit();
        }
    });

</script>