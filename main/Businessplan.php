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
<?php
function createRandomPassword() {
    $chars = "003232303232023232023456789";
    srand((double)microtime() * 1000000);
    $i    = 0;
    $pass = '';
    while ($i <= 7) {

        $num = rand() % 33;

        $tmp = substr($chars, $num, 1);

        $pass = $pass . $tmp;

        $i++;

    }

    return $pass;
}

$finalcode = 'RS-' . createRandomPassword();
?>


<script language="javascript" type="text/javascript">
    /* Visit http://www.yaldex.com/ for full source code
     and get more free JavaScript, CSS and DHTML scripts! */
    var timerID = null;
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
            </form>

            <table class="table table-striped table-bordered">

                <?php
                $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                $month_index = (int)$_GET['monthIndex'];
                $months      = array(
                    array('Jan', 'Feb', 'Mar'),
                    array('Apr', 'May', 'Jun'),
                    array('Jul', 'Aug', 'Sep'),
                    array('Oct', 'Nov', 'Dec'),
                );
                $all_months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

                // merchandise sale
                $result = $db->prepare("SELECT SUM(amount) AS amount FROM sales WHERE sale_type = 'Merchandise'
                  AND month = :month AND year = :year");
                $result->execute(array(
                    ':month' => $months[$month_index][0],
                    ':year'  => $year
                ));
                $row = $result->fetch();
                $merchandise_1 = $row['amount'];

                $result = $db->prepare("SELECT SUM(amount) AS amount FROM sales WHERE sale_type = 'Merchandise'
                  AND month = :month AND year = :year");
                $result->execute(array(
                    ':month' => $months[$month_index][1],
                    ':year'  => $year
                ));
                $row = $result->fetch();
                $merchandise_2 = $row['amount'];

                $result = $db->prepare("SELECT SUM(amount) AS amount FROM sales WHERE sale_type = 'Merchandise'
                  AND month = :month AND year = :year");
                $result->execute(array(
                    ':month' => $months[$month_index][2],
                    ':year'  => $year
                ));
                $row = $result->fetch();
                $merchandise_3 = $row['amount'];

                $placeholders = rtrim(str_repeat('?, ', count($all_months)), ', ') ;
                $stmt = $db->prepare("SELECT SUM(amount) AS amount FROM sales WHERE sale_type = 'Merchandise'
                  AND month IN ($placeholders) AND year = ?");
                $stmt->execute(array_merge($all_months, array($year)));
                $row = $stmt->fetch();
                $total_merchandise = $row['amount'];

                $stmt = $db->prepare("SELECT SUM(value) AS value FROM business_plan_yearly bpy
                  INNER JOIN business_plan_entities bpe ON bpy.business_plan_entity_id = bpe.id
                  WHERE bpe.name = 'Merchandise'
                  AND bpy.month IN ($placeholders) AND year = ?");
                $stmt->execute(array_merge($all_months, array($year)));
                $row = $stmt->fetch();
                $fy_estimated_merchandise = $row['value'];

                // --------------- FTF ----------------
                $query = "SELECT SUM(s.amount) AS amount FROM sales s
                    INNER JOIN flight_purchases fp ON s.invoice_number = fp.invoice_id
                    INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                    WHERE fo.offer_name LIKE '%FTF%'
                    AND s.month = :month
                    AND s.year = :year";

                $stmt = $db->prepare($query);
                $stmt->execute(array(
                    ':month' => $months[$month_index][0],
                    ':year'  => $year
                ));
                $row = $stmt->fetch();
                $ftf_1 = $row['amount'];

                $stmt = $db->prepare($query);
                $stmt->execute(array(
                    ':month' => $months[$month_index][1],
                    ':year'  => $year
                ));
                $row = $stmt->fetch();
                $ftf_2 = $row['amount'];

                $stmt = $db->prepare($query);
                $stmt->execute(array(
                    ':month' => $months[$month_index][2],
                    ':year'  => $year
                ));
                $row = $stmt->fetch();
                $ftf_3 = $row['amount'];

                $stmt = $db->prepare("SELECT SUM(s.amount) AS amount FROM sales s
                    INNER JOIN flight_purchases fp ON s.invoice_number = fp.invoice_id
                    INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                    WHERE fo.offer_name LIKE '%FTF%'
                    AND s.month IN ($placeholders)
                    AND s.year = ?");
                $stmt->execute(array_merge($all_months, array($year)));
                $row = $stmt->fetch();
                $total_ftf = $row['amount'];

                $stmt = $db->prepare("SELECT SUM(value) AS value FROM business_plan_yearly bpy
                  INNER JOIN business_plan_entities bpe ON bpy.business_plan_entity_id = bpe.id
                  WHERE bpe.name LIKE '%FTF%'
                  AND bpy.month IN ($placeholders) AND year = ?");
                $stmt->execute(array_merge($all_months, array($year)));
                $row = $stmt->fetch();
                $fy_estimated_ftf = $row['value'];
                ?>

                <thead id="tblHead">
                <tr>
                    <th>
                        <button class="btn btn-small btn-primary btnPrevMonths"> < </button>
                        <button class="btn btn-small btn-primary btnNextMonths"> > </button>
                    </th>
                    <th><?= $_GET['year'] ?><br/><?= $months[$month_index][0] ?></th>
                    <th><?= $_GET['year'] ?><br/><?= $months[$month_index][0] ?></th>
                    <th><?= $_GET['year'] ?><br/><?= $months[$month_index][1] ?></th>
                    <th><?= $_GET['year'] ?><br/><?= $months[$month_index][1] ?></th>
                    <th><?= $_GET['year'] ?><br/><?= $months[$month_index][2] ?></th>
                    <th><?= $_GET['year'] ?><br/><?= $months[$month_index][2] ?></th>
                    <th><?= $_GET['year'] ?><br/>FY Total</th>
                    <th><?= $_GET['year'] ?><br/>FY Total</th>
                    <th><?= $_GET['year'] ?><br/>Deviation</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $result = $db->prepare("SELECT * FROM business_plan_entities WHERE parent_id = 0");
                $result->execute();
                while ($row = $result->fetch()) {
                    ?>
                    <tr>
                        <td>
                            <button class="btn btn-small btn-secondary btnParentRow" data-parent-id="<?=$row['id']?>" > +</button>
                            <b><span><?= $row['name'] ?></span></b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
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
                            'month'=>$row2['month'],
                            'value'=>$row2['value'],
                            'id'=>$row2['id'],
                            'parent_id'=>$row2['parent_id']
                        );
                    }

                    function getMonthValue($needle, $arr) {
                        $index = array_search($needle, array_map(function ($v) {
                            return $v['month'];
                        }, $arr));

                        return $index !== false ? $arr[$index]['value'] : 0;
                    }

                    foreach ($arr_to_display as $entity_name=>$arr_monthwise_data) {
                        ?>
                        <tr class="row_<?=$arr_monthwise_data[0]['parent_id']?>">
                            <td><?= $entity_name ?></td>
                            <td><input type="text" class="input-small" data-entity-id="<?= $arr_monthwise_data[0]['id'] ?>"
                                       data-index="1"
                                       value="<?= getMonthValue($months[$month_index][0], $arr_monthwise_data) ?>"/>
                            </td>
                            <td>
                                <?php
                                switch($entity_name) {
                                    case 'Merchandise':
                                        echo $merchandise_1;
                                        break;
                                    case 'FTF':
                                        echo $ftf_1;
                                        break;
                                }
                                ?>
                            </td>
                            <td><input type="text" class="input-small" data-entity-id="<?= $arr_monthwise_data[0]['id'] ?>"
                                       data-index="3"
                                       value="<?= getMonthValue($months[$month_index][1], $arr_monthwise_data) ?>"/>
                            </td>
                            <td>
                                <?php
                                switch($entity_name) {
                                    case 'Merchandise':
                                        echo $merchandise_2;
                                        break;
                                    case 'FTF':
                                        echo $ftf_2;
                                        break;
                                }
                                ?>
                            </td>
                            <td><input type="text" class="input-small" data-entity-id="<?= $arr_monthwise_data[0]['id'] ?>"
                                       data-index="5"
                                       value="<?= getMonthValue($months[$month_index][2], $arr_monthwise_data) ?>"/>
                            </td>
                            <td>
                                <?php
                                switch($entity_name) {
                                    case 'Merchandise':
                                        echo (int)$merchandise_3;
                                        break;
                                    case 'FTF':
                                        echo $ftf_3;
                                        break;
                                }
                                ?>
                            </td>
                            <td class="fyEstimted">
                                <?php
                                switch($entity_name) {
                                    case 'Merchandise':
                                        echo $fy_estimated_merchandise;
                                        break;
                                    case 'FTF':
                                        echo $fy_estimated_ftf;
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                switch($entity_name) {
                                    case 'Merchandise':
                                        echo $total_merchandise;
                                        break;
                                    case 'FTF':
                                        echo $total_ftf;
                                        break;
                                }
                                ?>
                            </td>
                            <td class="derivation">
                                <?php
                                switch($entity_name) {
                                    case 'Merchandise':
                                        echo $fy_estimated_merchandise - $total_merchandise;
                                        break;
                                    case 'FTF':
                                        echo $fy_estimated_ftf - $total_ftf;
                                        break;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr class="rowTotal" data-parent-id="<?=$row['id']?>">
                        <td><b>Total</b></td>
                        <td data-index="1"></td>
                        <td data-index="2"></td>
                        <td data-index="3"></td>
                        <td data-index="4"></td>
                        <td data-index="5"></td>
                        <td data-index="6"></td>
                        <td data-index="7"></td>
                        <td data-index="8"></td>
                        <td data-index="9"></td>
                    </tr>
                    <tr>
                        <td colspan="10">&nbsp;</td>
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

    $('#year').on('change', function (e) {
        $(e.target).parent().submit();
    });

    $('.btnPrevMonths').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var monthIndex = $('#monthIndex').val();
        if (monthIndex > 0) {
            monthIndex--;
            $('#monthIndex').val(monthIndex);
            $('#bpForm').submit();
        }
    });

    $('.btnNextMonths').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var monthIndex = $('#monthIndex').val();
        if (monthIndex < 3) {
            monthIndex++;
            $('#monthIndex').val(monthIndex);
            $('#bpForm').submit();
        }
    });

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

    var _recalculate = function() {
        $('.rowTotal td').each(function(index, obj) {
            if(index != 0) {
                var rowTotal = $(this).parent();
                var parentId = rowTotal.data('parent-id');
                var prevRows = rowTotal.prevAll('.row_' + parentId);
                var sum      = 0;
                $(prevRows).find('td:eq(' + index + ')').each(function (index2, obj2) {
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
                $(obj).html(sum);
            }
        });
    };

    _recalculate();
</script>