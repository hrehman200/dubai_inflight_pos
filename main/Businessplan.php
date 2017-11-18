<html>
<head>
    <title>
        POS
    </title>
    <?php
    require_once('../connect.php');
    ?>
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

        .table td {
            background-color: white;
        }

        .rowTotal td {
            font-weight: bold;
        }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">


    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <!--sa poip up-->
    <script src="jeffartagame.js" type="text/javascript" charset="utf-8"></script>
    <script src="js/application.js" type="text/javascript" charset="utf-8"></script>
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>
    <script src="src/facebox.js" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('a[rel*=facebox]').facebox({
                loadingImage: 'src/loading.gif',
                closeImage: 'src/closelabel.png'
            })
        })
    </script>
</head>


<script language="javascript" type="text/javascript">
    /* Visit http://www.yaldex.com/ for full source code
     and get more free JavaScript, CSS and DHTML scripts! */
    var timerID      = null;
    var timerRunning = false;
    function stopclock() {
        if (timerRunning)
            clearTimeout(timerID);
        timerRunning = false;
    }
    function showtime() {
        var now       = new Date();
        var hours     = now.getHours();
        var minutes   = now.getMinutes();
        var seconds   = now.getSeconds()
        var timeValue = "" + ((hours > 12) ? hours - 12 : hours)
        if (timeValue == "0") timeValue = 12;
        timeValue += ((minutes < 10) ? ":0" : ":") + minutes
        timeValue += ((seconds < 10) ? ":0" : ":") + seconds
        timeValue += (hours >= 12) ? " P.M." : " A.M."
        document.clock.face.value = timeValue;
        timerID                   = setTimeout("showtime()", 1000);
        timerRunning              = true;
    }
    function startclock() {
        stopclock();
        showtime();
    }
    window.onload = startclock;
    // End -->
</SCRIPT>
<body>
<?php include('navfixed.php'); ?>
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

            <Button type="button" onclick="convertToCSV()" id="exportCSV" class="btn btn-info"
                    style="float:right; width:230px; height:35px;">
                <i class="icon-plus-sign icon-large"></i> Export
            </button>

            <form id="bpForm" method="get" action="<?= $_SERVER['PHP_SELF'] ?>">
                <input type="hidden" name="monthIndex" id="monthIndex" value="<?= $_GET['monthIndex'] ?>"/>
                <select id="year" name="year">
                    <?php
                    for ($year = 2017; $year <= 2027; $year++) {
                        echo sprintf('<option %s>%d</option>', $_GET['year'] == $year ? 'selected' : '', $year);
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

            </form>

            <table class="table table-striped table-bordered">

                <?php
                $year               = isset($_GET['year']) ? $_GET['year'] : date('Y');
                $month_index        = (int)$_GET['monthIndex'];
                $all_months         = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
                $month_placeholders = rtrim(str_repeat('?, ', count($all_months)), ', ');
                $from_month         = isset($_GET['fromMonth']) ? $_GET['fromMonth'] : 'Jan';
                $to_month           = isset($_GET['toMonth']) ? $_GET['toMonth'] : 'Dec';
                $from_month_index   = array_search($from_month, $all_months);
                $to_month_index     = array_search($to_month, $all_months);
                $months             = array_slice($all_months, $from_month_index, $to_month_index+1);

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

                // --------------- FTF ----------------
                $arr_ftf = [];
                foreach($months as $m) {
                    $arr_ftf[] = getFlightSaleForMonth('FTF', $m, $year);
                }
                $total_ftf        = getFlightSaleForYear('FTF', $year);
                $fy_estimated_ftf = getFYEstimatedForEntity('FTF');

                // --------------- COGS Merchandise ----------------
                $fy_estimated_merchandise_cogs = getFYEstimatedForEntity(null, 24);

                // --------------- SKyDivers ----------------
                $arr_skydivers = [];
                foreach($months as $m) {
                    $arr_skydivers[] = getFlightSaleForMonth('Skydivers', $m, $year);
                }
                $total_skydivers        = getFlightSaleForYear('Skydivers', $year);
                $fy_estimated_skydivers = getFYEstimatedForEntity('SkyDivers');
                ?>

                <thead id="tblHead">
                <tr>
                    <th>
                    </th>
                    <?php
                    foreach($months as $m) {
                        ?>
                        <th><?= $_GET['year'] ?><br/><?= $m ?></th>
                        <th><?= $_GET['year'] ?><br/><?= $m ?></th>
                    <?php
                    }
                    ?>
                    <th><?= $_GET['year'] ?><br/>FY Total</th>
                    <th><?= $_GET['year'] ?><br/>FY Total</th>
                    <th><?= $_GET['year'] ?><br/>Deviation</th>
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
                    <tr>
                        <td>
                            <button class="btn btn-small btn-secondary btnParentRow" data-parent-id="<?= $row['id'] ?>">
                                +
                            </button>
                            <b><span><?= $row['name'] ?></span></b></td>
                        <?php
                        foreach($months as $m) {
                            ?>
                            <td></td>
                            <td></td>
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
                        ':years'    => $_GET['year']
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
                                <td><input type="text" class="input-small"
                                           data-entity-id="<?= $arr_monthwise_data[0]['id'] ?>"
                                           data-index="<?=$i+1?>"
                                           value="<?= getMonthValue($months[$i], $arr_monthwise_data) ?>"/>
                                </td>
                                <td>
                                    <?php
                                    switch ($entity_name) {
                                        case 'Merchandise':
                                            if ($is_cogs) {
                                                echo getNPercentOf(30, $arr_merhandise[$i]);
                                            } else {
                                                echo $arr_merhandise[$i];
                                            }
                                            break;
                                        case 'FTF':
                                            echo $arr_ftf[$i];
                                            break;
                                        case 'SkyDivers':
                                            echo $arr_skydivers[$i];
                                            break;
                                    }
                                    ?>
                                </td>
                                <?php
                            }
                            ?>

                            <td class="fyEstimted">
                                <?php
                                switch ($entity_name) {
                                    case 'Merchandise':
                                        if ($is_cogs) {
                                            echo $fy_estimated_merchandise_cogs;
                                        } else {
                                            echo $fy_estimated_merchandise;
                                        }
                                        break;
                                    case 'FTF':
                                        echo $fy_estimated_ftf;
                                        break;
                                    case 'SkyDivers':
                                        echo $fy_estimated_skydivers;
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                switch ($entity_name) {
                                    case 'Merchandise':
                                        if ($is_cogs) {
                                            $total_merchandise = getNPercentOf(30, $total_merchandise);
                                        }
                                        echo $total_merchandise;
                                        break;
                                    case 'FTF':
                                        echo $total_ftf;
                                        break;
                                    case 'SkyDivers':
                                        echo $total_skydivers;
                                        break;
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
                                    case 'FTF':
                                        echo round($fy_estimated_ftf - $total_ftf, 1);
                                        break;
                                    case 'SkyDivers':
                                        echo $fy_estimated_skydivers - $total_skydivers;
                                        break;
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
                            echo sprintf('<td data-index="%d"></td>', $i+1);
                            echo sprintf('<td data-index="%d"></td>', $i+2);
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

</script>