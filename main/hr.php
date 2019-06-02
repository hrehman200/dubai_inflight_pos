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
                <i class="icon-group"></i> Employees
            </div>
            <ul class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                /
                <li class="active">Employees</li>
            </ul>

            <div style="margin-top: -19px; margin-bottom: 21px;" class="btns">

                <a href="index.php" class="btn btn-default btn-large" style="float: none;">
                    <i class="icon icon-circle-arrow-left icon-large"></i> Back
                </a>
                <!--<button style="float:right; margin-right: 5px;" class="btn btn-success btn-large" onclick="window.print()">
                    Print
                </button>
                <button style="float:right; margin-right:5px;" class="btn btn-warning btn-large" onclick="convertToCSV()" id="exportCSV"/>
                Export
                </button>-->
                <br><br>
            </div>

            <button id="add" data-href="employee-edit.php" class="btn btn-primary pull-right">Add Employee</button>
            <table id="tblEmployees" class="table table-striped">
                <thead>
                    <th>Name</th>
                    <th>Employee Number</th>
                    <th>Department</th>
                    <th>Date of joining</th>
                    <th>Salary and Wages</th>
                    <th>Assets in Hand</th>
                    <th>Employee status</th>
                    <th>Visa Expiry date</th>
                    <th>Family Member</th>
                    <th>Emergency Contact number</th>
                    <th>Bank</th>
                    <th>Account Number</th>
                    <th>Action</th>
                </thead>

                <tbody>
                <?php
                $query = $db->query('SELECT * FROM employees ORDER BY name ASC');
                $query->execute();
                while($row = $query->fetch()) {
                    ?>
                    <tr>
                        <td><?=$row['name']?></td>
                        <td><?=$row['employee_no']?></td>
                        <td><?=$row['department']?></td>
                        <td><?=$row['date_of_joining']?></td>
                        <td><button class="btnSalary" data-href="employee-salary-list.php?employee_id=<?=$row['id']?>">Edit Salary</button></td>
                        <td><?=$row['assets_in_hand']?></td>
                        <td><?=$row['date_of_resignation'] == '0000-00-00' ? 'Active' : 'Resigned'?></td>
                        <td><?=$row['visa_expiry_date']?></td>
                        <td><?=$row['family_member']?></td>
                        <td><?=$row['emergency_contact_no']?></td>
                        <td><?=$row['bank_account_name']?></td>
                        <td><?=$row['bank_account_no']?></td>
                        <td>
                            <button id="edit" data-href="employee-edit.php?id=<?=$row['id']?>"><i class="icon-edit"></i></button>
                            <button id="delete" data-id="<?=$row['id']?>"><i class="icon-remove"></i></button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>

            </table>

        </div>
    </div>
</div>

<div id="employee-modal" class="modal fade" style="width: 350px; left:58%;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Employee</h4>
            </div>
            <div class="modal-body">
                <p>Loading...</p>
            </div>
            <div class="modal-footer">
                <div class="msg alert alert-danger hidden"></div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btnSaveEmployee" class="btn btn-primary" data-loading-text="<i>Saving...</i>">Save</button>
            </div>
        </div>
    </div>
</div>

<div id="employee-salary-modal" class="modal fade" style="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add/Edit Salary</h4>
            </div>
            <div class="modal-body">
                <p>Loading...</p>
            </div>
            <div class="modal-footer">
                <div class="msg alert alert-danger hidden"></div>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="btnSaveSalary" class="btn btn-primary" data-loading-text="<i>Saving...</i>">Save</button>
            </div>
        </div>
    </div>
</div>

</body>


<script type="text/javascript">

    $(document).ready(function() {
        $('#add').on('click', function(e){
            e.preventDefault();
            $('#employee-modal').modal('show').find('.modal-body').load($(this).data('href'));
        });

        $('#edit').on('click', function(e){
            e.preventDefault();
            $('#employee-modal').modal('show').find('.modal-body').load($(this).data('href'));
        });

        $('#delete').on('click', function(e){
            e.preventDefault();
            $(e.target).button('loading');
            $.ajax({
                url:'api.php',
                method: 'POST',
                data: {
                    id: $(this).attr('data-id'),
                    call: 'deleteEmployee'
                },
                dataType: 'json',
                success: function(response) {
                    $(e.target).button('reset');
                    if(response.success == 1) {
                        window.location.href = window.location.href;
                    } else {
                        $('#employee-modal .msg').removeClass('hidden').html(response.msg);
                    }
                }
            });
        });

        $('#btnSaveEmployee').on('click', function(e) {
            $(e.target).button('loading');
            $.ajax({
                url:'api.php',
                method: 'POST',
                data: $('#employee-form').serialize(),
                dataType: 'json',
                success: function(response) {
                    $(e.target).button('reset');
                    if(response.success == 1) {
                        window.location.href = window.location.href;
                    } else {
                        $('#employee-modal .msg').removeClass('hidden').html(response.msg);
                    }
                }
            });
        });

        $('.btnSalary').on('click', function(e){
            e.preventDefault();
            $('#employee-salary-modal').modal('show').find('.modal-body').load($(this).data('href'));
        });

        $('#employee-salary-modal').on('click', '#btnAddSalary', function(e) {
            e.preventDefault();
            $('#employee-salary-modal').find('.modal-body').load($(this).data('href'));
        });

        $('#employee-salary-modal').on('click', '.editSalary', function(e) {
            e.preventDefault();
            $('#employee-salary-modal').find('.modal-body').load($(this).data('href'));
        });

        $('#employee-salary-modal').on('click', '.deleteSalary', function(e) {
            e.preventDefault();
            $(e.target).button('loading');
            $.ajax({
                url:'api.php',
                method: 'POST',
                data: {
                    id: $(this).attr('data-id'),
                    call: 'deleteEmployeeSalary'
                },
                dataType: 'json',
                success: function(response) {
                    $(e.target).button('reset');
                    if(response.success == 1) {
                        window.location.href = window.location.href;
                    } else {
                        $('#employee-salary-modal .msg').removeClass('hidden').html(response.msg);
                    }
                }
            });
        });

        $('#btnSaveSalary').on('click', function(e) {
            $(e.target).button('loading');
            $.ajax({
                url:'api.php',
                method: 'POST',
                data: $('#salary-form').serialize(),
                dataType: 'json',
                success: function(response) {
                    $(e.target).button('reset');
                    if(response.success == 1) {
                        window.location.href = window.location.href;
                    } else {
                        $('#employee-salary-modal .msg').removeClass('hidden').html(response.msg);
                    }
                }
            });
        });
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