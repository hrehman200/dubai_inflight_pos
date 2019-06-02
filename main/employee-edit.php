<?php
require_once '../connect.php';
?>
<form id="employee-form" action="api.php" method="post" role="form" enctype="multipart/form-data">
    <input type="hidden" name="call" value="saveEmployee" />
    <input type="hidden" name="id" value="<?=$_GET['id']?>" />

    <?php
    $employee = getRowById('employees', $_GET['id']);
    ?>

    <div class="form-group">
        <input type="text" name="employee_no" id="employee_no" class="form-control"
               placeholder="Employee No." value="<?=isset($employee) ? $employee['employee_no'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="name" id="name" class="form-control"
               placeholder="Name" value="<?=isset($employee) ? $employee['name'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="<?=isset($employee) ? $employee['email'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="designation" id="designation" class="form-control" placeholder="Designation" value="<?=isset($employee) ? $employee['designation'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="department" id="department" class="form-control" placeholder="Department" value="<?=isset($employee) ? $employee['department'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="manager_name" id="manager_name" class="form-control" placeholder="Manager Name" value="<?=isset($employee) ? $employee['manager_name'] : ''?>">
    </div>

    <div class="form-group">
        <textarea name="assets_in_hand" id="assets_in_hand" class="form-control" placeholder="Assets in hand" ><?=isset($employee) ? $employee['assets_in_hand'] : ''?></textarea>
    </div>

    <div class="form-group">
        <input type="text" name="date_of_joining" id="date_of_joining" class="form-control" placeholder="Date of Joining" value="<?=isset($employee) ? $employee['date_of_joining'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="visa_expiry_date" id="visa_expiry_date" class="form-control" placeholder="Visa Expiry Date" value="<?=isset($employee) ? $employee['visa_expiry_date'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="family_member" id="family_member" class="form-control" placeholder="Family Member" value="<?=isset($employee) ? $employee['family_member'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="emergency_contact_no" id="emergency_contact_no" class="form-control" placeholder="Emergency Contact No" value="<?=isset($employee) ? $employee['emergency_contact_no'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" placeholder="Bank" value="<?=isset($employee) ? $employee['bank_account_name'] : ''?>">
    </div>

    <div class="form-group">
        <input type="text" name="bank_account_no" id="bank_account_no" class="form-control" placeholder="Account No" value="<?=isset($employee) ? $employee['bank_account_no'] : ''?>">
    </div>

    <hr/>

    <?php if($_GET['id'] > 0) { ?>

        <b>Final Settlement</b><br><br>

        <div class="form-group">
            <input type="text" name="date_of_resignation" id="date_of_resignation" class="form-control" placeholder="Date of Resignation" value="<?=isset($employee) ? $employee['date_of_resignation'] : ''?>">
        </div>

        <div class="form-group">
            <input type="text" name="notice_period" id="notice_period" class="form-control" placeholder="Notice Period" value="<?=isset($employee) ? $employee['notice_period'] : ''?>">
        </div>

        <div class="form-group">
            <textarea name="assets_return" id="assets_return" class="form-control" placeholder="Assets Return"><?=isset($employee) ? $employee['assets_return'] : ''?></textarea>
        </div>

        <div class="form-group">
            <input type="text" name="annual_leaves_eligible" id="annual_leaves_eligible" class="form-control" placeholder="Annual leaves eligible" value="<?=isset($employee) ? $employee['annual_leaves_eligible'] : ''?>">
        </div>

        <div class="form-group">
            <input type="text" name="annual_leaves_balance_aed" id="annual_leaves_balance_aed" class="form-control" placeholder="Annual leaves balance" value="<?=isset($employee) ? $employee['annual_leaves_balance_aed'] : ''?>">
        </div>

        <div class="form-group">
            <input type="text" name="deduction_aed" id="deduction_aed" class="form-control" placeholder="Deduction" value="<?=isset($employee) ? $employee['deduction_aed'] : ''?>">
        </div>

        <div class="form-group">
            <input type="text" name="gratuity" id="gratuity" class="form-control" placeholder="Gratuity" value="<?=isset($employee) ? $employee['gratuity'] : ''?>">
        </div>

        <div class="form-group">
            <input type="text" name="total" id="total" class="form-control" placeholder="Total" value="<?=isset($employee) ? $employee['total'] : ''?>">
        </div>

    <?php } ?>

</form>

<style>
    .datepicker{z-index:1151 !important;}

    input[type="file"] {
        border:none;
        background: none;
        padding:0;
        line-height: 20px;
        box-shadow: none;
    }

    input[type="text"] {
        height: auto;
    }
</style>

<script type="text/javascript">
    $('#date_of_joining').datepicker({
        format: 'yyyy-mm-dd'
    }).on('changeDate', function(ev) {
        $('.datepicker').hide();
    });
</script>