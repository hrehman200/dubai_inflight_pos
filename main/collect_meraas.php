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
                include_once 'partials/collect_meraas.php';
                ?>

            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

</body>
<script type="text/javascript">

    $(function() {

        <?php
        if(isset($_GET['verified']) || $argv[1] == 'verified') {
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