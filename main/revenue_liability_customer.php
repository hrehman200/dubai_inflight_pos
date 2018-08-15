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
                <i class="icon-bar-chart"></i> Revenue & Liability per Customer
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Revenue & Liability per Customer</li>
            </ul>

            <div>
                <div style="margin-top: -19px; margin-bottom: 21px;">
                    <a href="index.php">
                        <button class="btn btn-default btn-large" style="float: none;"><i
                                class="icon icon-circle-arrow-left icon-large"></i> Back
                        </button>
                    </a>
                </div>
                <form action="<?=$_SERVER['PHP_SELF']?>" method="get">

                    Liability till:
                    <select id='year' name="year" class="span2">
                        <option>2017</option>
                        <option>2018</option>
                    </select>

                    <select id='month' name="month" class="span2">
                        <option selected>Jan</option>
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

                    <br/>
                    <input type="hidden" name="customerId" id="customerId" value="<?=$_GET['customerId']?>" />
                    <input type="text" class="form-contorl span6" placeholder="Customer Name" id="customer" name="customer" autocomplete="off" />

                    <button class="btn btn-info" style="width: 123px; height:35px; margin-top:-8px;" type="submit">
                        <i class="icon icon-search icon-large"></i> Search
                    </button>

                    <span id="spPre2018">
                        <input type="checkbox" id="chkPre2018" class="form-control" />
                        <label for="chkPre2018" style="display: inline;">Pre 2018</label>
                    </span>

                    <button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-info btn-large" onclick="convertToCSV()"><i
                                class="icon icon-columns icon-large"></i> Export</button>
                    <button style="width: 123px; height:35px; margin-top:-2px; float:right;"
                            class="btn btn-success btn-large"><a href="javascript:Clickheretoprint()"><i
                                class="icon icon-print icon-large"></i> Print</a></button>
                </form>

                <div class="content" id="content">

                    <?php
                    //if($_GET['customerId'] > 0) {
                        ?>
                        <table class="table table-striped" style="background-color: white;" id="tblCustomerLiability">
                            <?php

                            if(!isset($_GET['month'])) {
                                $_GET['month'] = date('M');
                                $_GET['year'] = date('Y');
                            }

                            $sql = "
                                SELECT * FROM customer_monthly_liability cml
                                INNER JOIN customer c ON cml.customer_id = c.customer_id
                                WHERE cml.year = :year AND cml.month = :month";

                            if($_GET['customerId'] > 0) {
                                $sql .= sprintf(" AND c.customer_id = %d", $_GET['customerId']);
                            }

                            $sql .= " ORDER BY c.customer_name";

                            $result = $db->prepare($sql);
                            $result->execute(array(
                                ':year' => $_GET['year'],
                                ':month' => $_GET['month']
                            ));

                            ?>
                            <tr>
                                <th>Customer Name</th>
                                <th>Customer ID</th>
                                <th>Total Mins Liability</th>
                                <th>AED Liability</th>
                                <th>Mins. Liability Pre 2018</th>
                                <th>AED Value Pre 2018</th>
                                <th>VAT on Pre 2018</th>
                            </tr>
                            <?php
                            $total_minutes = 0;
                            $total_price = 0;
                            if($result->rowCount() > 0) {
                                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

                                    $units_remaining = $row['liability_minutes'];
                                    $credit_minutes_liability = $row['liability_amount'];
                                    $total_minutes += $units_remaining;
                                    $total_price += $credit_minutes_liability;

                                    if($units_remaining <= 0 && $credit_minutes_liability <= 0 && $_GET['customerId'] <= 0) {
                                        continue;
                                    }

                                    if(strcasecmp($row['customer_name'], 'inflight staff flying') == 0 ||
                                        strcasecmp($row['customer_name'], 'fdr') == 0 ||
                                        strcasecmp($row['customer_name'], 'maintenance') == 0) {
                                        continue;
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $row['customer_name'] ?></td>
                                        <td><?= $row['customer_id'] ?></td>
                                        <td><?= number_format($units_remaining) ?></td>
                                        <td><?= number_format(round($credit_minutes_liability)) ?></td>
                                        <td><?php
                                            echo $row['pre_2018_minutes'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo round($row['pre_2018_amount']);
                                            ?>
                                        </td>
                                        <td><?php
                                            echo round($row['pre_2018_amount'] * 5 / 105, 2);
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align:right; padding-right: 50px;"><b>Total:</b></td>
                                    <td><b><?=number_format($total_minutes)?></b></td>
                                    <td><b><?=number_format($total_price)?></b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" align="center">No record found</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                        <?php
                    //}
                    ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $('#month').val('<?=$_GET['month']?>');
    $('#year').val('<?=$_GET['year']?>');

    $('#chkPre2018').on('change', function (e) {
        if($(this).is(':checked')) {
            $('#tblCustomerLiability tr th:nth-last-child(-n+2), #tblCustomerLiability tr td:nth-last-child(-n+2)').show();
        } else {
            $('#tblCustomerLiability tr th:nth-last-child(-n+2), #tblCustomerLiability tr td:nth-last-child(-n+2)').hide();
        }
    }).change();

    $('#year').on('change', function (e) {
        if($(this).val() < 2018) {
            $('#spPre2018')
                .find('#chkPre2018').prop('checked', false).change().end()
                .hide();
        } else {
            $('#spPre2018').show();
        }
    }).change();

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
        exportTableToCSV($('#tblCustomerLiability'), 'rnl.csv');
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

<?php include('footer.php'); ?>
</html>

