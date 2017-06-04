<form id="register-form" action="http://phpoll.com/register/process" method="post" role="form">
    <input type="hidden" name="call" value="saveCustomer" />
    <div class="form-group">
        <input type="text" name="customer_name" id="customer_name" class="form-control"
               placeholder="Customer Name" value="">
    </div>
    <div class="form-group">
        <input type="text" name="phone" id="phone" class="form-control" placeholder="Phone No" value="">
    </div>
    <div class="form-group">
        <input type="text" name="address" id="address" class="form-control" placeholder="Address" value="">
    </div>
    <div class="form-group">
        <input type="text" name="resident_of" id="resident_of" class="form-control"
               placeholder="Country of Resident" value="">
    </div>
    <div class="form-group">
        <input type="text" name="nationality" id="nationality" class="form-control"
               placeholder="Nationality" value="">
    </div>
    <div class="form-group">
        <input type="text" name="dob" id="dob" class="form-control" placeholder="Date of Birth" value="">
    </div>
    <div class="form-group">
        <select class="form-control" name="gender" id="gender">
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select>
    </div>
    <div class="form-group">
        <input type="email" name="email" id="email" class="form-control" placeholder="Email Address"
               value="">
    </div>
    <div class="form-group">
        <input type="password" name="password" id="password" class="form-control" placeholder="Password">
    </div>
</form>

<style>
    .datepicker{z-index:1151 !important;}
</style>