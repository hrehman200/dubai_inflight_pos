<?php
require_once '../connect.php';
$salary = getRowById('employee_salaries', $_GET['id']);
?>
<form id="salary-form" action="api.php" method="post" role="form" enctype="multipart/form-data">
    <input type="hidden" name="call" value="saveEmployeeSalary"/>
    <input type="hidden" name="id" value="<?= $_GET['id'] ?>"/>
    <input type="hidden" name="employee_id" value="<?= $_GET['employee_id'] ?>"/>

    <div class="form-group">
        <input type="text" name="current_salary" id="current_salary" class="form-control"
               placeholder="Salary" value="<?= isset($salary) ? $salary['current_salary'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="effect_date" id="effect_date" class="form-control"
               placeholder="Effect date" value="<?= isset($salary) ? $salary['effect_date'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="house_allowance" id="house_allowance" class="form-control salary"
               placeholder="House allowance" value="<?= isset($salary) ? $salary['house_allowance'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="medical" id="medical" class="form-control salary" placeholder="Medical"
               value="<?= isset($salary) ? $salary['medical'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="mobile" id="mobile" class="form-control salary" placeholder="Mobile"
               value="<?= isset($salary) ? $salary['mobile'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="ticket" id="ticket" class="form-control salary" placeholder="Ticket"
               value="<?= isset($salary) ? $salary['ticket'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="other" id="other" class="form-control salary"
                  placeholder="Other" value="<?= isset($salary) ? $salary['other'] : '' ?>" />
    </div>

    <div class="form-group">
        <input type="text" name="bonus" id="bonus" class="form-control salary" placeholder="Bonus"
               value="<?= isset($salary) ? $salary['bonus'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="staff_uniform" id="staff_uniform" class="form-control salary" placeholder="Staff uniform"
               value="<?= isset($salary) ? $salary['staff_uniform'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="monthly_total" id="monthly_total" class="form-control" placeholder="Monthly total"
               value="<?= isset($salary) ? $salary['monthly_total'] : '' ?>">
    </div>

    <div class="form-group">
        <input type="text" name="yearly_total" id="yearly_total" class="form-control" placeholder="Yearly total"
               value="<?= isset($salary) ? $salary['yearly_total'] : '' ?>">
    </div

</form>

<style>
    .datepicker {
        z-index: 1151 !important;
    }

    input[type="text"] {
        height: auto;
    }
</style>

<script type="text/javascript">
    $('#effect_date').datepicker({
        format: 'yyyy-mm-dd'
    }).on('changeDate', function (ev) {
        $('.datepicker').hide();
    });

    $('.salary').on('keyup', function(e) {
        console.log(e);
        var monthlySum = 0;
        $('.salary').each(function(i, el) {
            monthlySum += Number($(el).val());
        });

        $('#monthly_total').val(monthlySum);
        $('#yearly_total').val(monthlySum * 12);
    });
</script>