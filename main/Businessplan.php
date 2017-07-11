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
                $month_index = (int)$_GET['monthIndex'];
                $months      = array(
                    array('Jan', 'Feb', 'Mar'),
                    array('Apr', 'May', 'Jun'),
                    array('Jul', 'Aug', 'Sep'),
                    array('Oct', 'Nov', 'Dec'),
                );
                ?>

                <thead id="tblHead">
                <tr>
                    <th>
                        <button class="btn btn-small btn-primary btnPrevMonths"> <</button>
                        <button class="btn btn-small btn-primary btnNextMonths"> ></button>
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
                            <button class="btn btn-small btn-secondary btnParentRow"> +</button>
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
                    $sql     = "SELECT bpe.id, bpe.name, bpy.month, bpy.value
                      FROM business_plan_entities bpe
                      LEFT JOIN business_plan_yearly bpy ON bpy.business_plan_entity_id = bpe.id AND bpy.year = :years
                      WHERE bpe.parent_id = :parentId";
                    $result2 = $db->prepare($sql);
                    $arr     = array(
                        ':parentId' => $row['id'],
                        ':years'    => $_GET['year']
                    );
                    $result2->execute($arr);
                    while ($row2 = $result2->fetch()) {
                        ?>
                        <tr class="row_<?= $row['name'] ?>">
                            <td><?= $row2['name'] ?></td>
                            <td><input type="text" class="input-small" data-entity-id="<?= $row2['id'] ?>"
                                       data-index="1"
                                       value="<?= $row2['month'] == $months[$month_index][0] ? $row2['value'] : '' ?>"/>
                            </td>
                            <td></td>
                            <td><input type="text" class="input-small" data-entity-id="<?= $row2['id'] ?>"
                                       data-index="3"
                                       value="<?= $row2['month'] == $months[$month_index][1] ? $row2['value'] : '' ?>"/>
                            </td>
                            <td></td>
                            <td><input type="text" class="input-small" data-entity-id="<?= $row2['id'] ?>"
                                       data-index="5"
                                       value="<?= $row2['month'] == $months[$month_index][2] ? $row2['value'] : '' ?>"/>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td><b>Total</b></td>
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
</script>


<script type="text/javascript">
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
        var parentName = $(this).parent().find('span').text();
        $('.row_' + parentName).toggleClass('hidden');
    });

    $('input[type="text"]').off('blur').on('blur', function (e) {
        var tdIndex   = $(e.target).data('index');
        var monthYear = $('#tblHead th:eq(' + tdIndex + ')').html();
        monthYear     = monthYear.split("<br>");
        var month     = monthYear[1];
        var year      = monthYear[0];
        var entityId  = $(e.target).data('entity-id');
        var value     = $(e.target).val();

        if (value > 0) {
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

                    }
                }
            });
        }
    });
</script>