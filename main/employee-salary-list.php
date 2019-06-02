<?php
require_once '../connect.php';
?>
<button id="btnAddSalary" class="btn btn-primary" data-href="employee-salary-edit.php?employee_id=<?=$_GET['employee_id']?>">Add Salary</button>

<table class="table table-striped">
    <thead>
    <th>Salary</th>
    <th>Effect Date</th>
    <th>Monthly Total</th>
    <th>Action</th>
    </thead>
    <tbody>
    <?php
    $salary_salaries = getRowsWhere('employee_salaries', 'employee_id', $_GET['employee_id']);
    if (count($salary_salaries) > 0) {
        foreach ($salary_salaries as $s) {
            ?>
            <tr>
                <td><?= $s['current_salary'] ?></td>
                <td><?= $s['effect_date'] ?></td>
                <td><?= $s['monthly_total'] ?></td>
                <td>
                    <button class="editSalary" data-href="employee-salary-edit.php?employee_id=<?=$_GET['employee_id'].'&id='.$s['id']?>"><i class="icon-edit"></i></button>
                    <button class="deleteSalary" data-id="<?=$s['id']?>"><i class="icon-remove"></i></button>
                </td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="4" align="center">No salaries found.</td></tr>';
    }
    ?>
    </tbody>
</table>