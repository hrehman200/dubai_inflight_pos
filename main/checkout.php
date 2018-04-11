<html>
<head>
    <title>Checkout</title>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.js"></script>
    <script>
        function suggest(inputString) {
            if (inputString.length == 0) {
                $('#suggestions').fadeOut();
            } else {
                $('#country').addClass('load');
                $.post("autosuggestname.php", {queryString: "" + inputString + ""}, function (data) {
                    if (data.length > 0) {
                        $('#suggestions').fadeIn();
                        $('#suggestionsList').html(data);
                        $('#country').removeClass('load');
                    }
                });
            }
        }

        function fill(thisValue) {
            $('#country').val(thisValue);
            setTimeout("$('#suggestions').fadeOut();", 600);
        }

    </script>

    <style>
        #result {
            height: 20px;
            font-size: 16px;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            padding: 5px;
            margin-bottom: 10px;
            background-color: #FFFF99;
        }

        #country {
            border: 1px solid #999;
            background: #EEEEEE;
            padding: 5px 10px;
            box-shadow: 0 1px 2px #ddd;
            -moz-box-shadow: 0 1px 2px #ddd;
            -webkit-box-shadow: 0 1px 2px #ddd;
        }

        .suggestionsBox {
            position: absolute;
            left: 10px;
            margin: 0;
            width: 268px;
            top: 40px;
            padding: 0px;
            background-color: #000;
            color: #fff;
        }

        .suggestionList {
            margin: 0px;
            padding: 0px;
        }

        .suggestionList ul li {
            list-style: none;
            margin: 0px;
            padding: 6px;
            border-bottom: 1px dotted #666;
            cursor: pointer;
        }

        .suggestionList ul li:hover {
            background-color: #FC3;
            color: #000;
        }

        ul {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #FFF;
            padding: 0;
            margin: 0;
        }

        .load {
            background-image: url(loader.gif);
            background-position: right;
            background-repeat: no-repeat;
        }

        #suggest {
            position: relative;
        }

        .combopopup {
            padding: 3px;
            width: 268px;
            border: 1px #CCC solid;
        }

    </style>
</head>
<body onLoad="document.getElementById('country').focus();">
<form action="savesales.php" method="post">
    <div id="ac">
        <center><h4><i class="icon icon-money icon-large"></i> Collection</h4></center>
        <hr>
        <input type="hidden" name="date" value="<?php echo date("Y-m-d"); ?>"/>
        <input type="hidden" name="invoice" value="<?php echo $_GET['invoice']; ?>"/>
        <input type="hidden" name="amount" value="<?php echo $_GET['total']; ?>"/>
        <input type="hidden" name="ptype" value="<?php echo $_GET['pt']; ?>"/>
        <input type="hidden" name="cashier" value="<?php echo $_GET['cashier']; ?>"/>
        <input type="hidden" name="profit" value="<?php echo $_GET['totalprof']; ?>"/>
        <input type="hidden" name="savingflight" value="<?php echo @$_GET['savingflight']; ?>"/>
        <input type="hidden" name="customerId" value="<?php echo @$_GET['customerId']; ?>"/>
        <input type="hidden" name="giveaway_token" value="<?php echo @$_GET['giveaway_token']; ?>"/>

        <center>

            <?php
            include_once('../connect.php');

            $result = $db->prepare("SELECT * FROM customer WHERE customer_id = :customer_id");
            $result->execute(array('customer_id' => $_GET['customerId']));
            $row = $result->fetch();
            $customer_name = $row['customer_name'];

            echo '<input type="hidden" name="credit_time" id="credit_time" value="' . $row['credit_time'] . '" />';
            echo '<input type="hidden" name="credit_cash" id="credit_cash" value="' . $row['credit_cash'] . '" />';
            ?>
            <?php
            $asas = $_GET['pt'];
            if ($asas == 'credit') {
                ?>Due Date: <input type="date" name="due" placeholder="Due Date"
                                   style="width: 268px; height:30px; margin-bottom: 15px;"/><br>
                <?php
            }
            if ($asas == 'cash') {
            ?>

            <span style="vertical-align: middle; width: auto; padding-right:0">AED</span>
        <input type="number" name="total_cash" id="total_cash" placeholder="Cash"
               style="width: 225px; height:30px;  margin-bottom: 15px;" value="<?= $_GET['total'] ?>" readonly/>

        <br>

        <!--<input type="number" name="discount" placeholder="Discount %" step="any"
            style="width: 268px; height:30px;  margin-bottom: 15px;" value="" onkeyup="myDiscountedInputFunction(this);"
            required/>-->
              <?php
            }
            ?>

            <br/>

            <!--<span style="width: auto; padding-right:5px;">VAT</span>
            <label id="lblVat"
                   style="display:inline-block; width: 225px; height:30px;  margin-bottom: 15px; text-align: left;">-</label>
            <br/>

            <span style="vertical-align: middle; width: auto; padding-right:0">AED</span>
            <input type="text" name="discountedValue" placeholder="Discounted Value" id="discountedValue"
                   style="width: 225px; height:30px;  margin-bottom: 15px;" step="any" readonly/>

            <br>-->

            <!-- for discounted Value End -->

            <select name="mode_of_payment" id="mode_of_payment"
                    style="width: 268px; height:30px;  margin-bottom: 15px;" onchange="callJavaScriptOnFirstMenu(this);"
                    required>
                <option value="-1">-- Mode of Payment--</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Account">Account</option>
                <option value="Online">Online</option>
                <option value="credit_cash">Credit Cash</option>
                <option value="credit_time">Pre-Opening</option>

            </select>

            <br/>
            <span style="vertical-align: middle; width: auto; padding-right:0">AED</span>
            <input type="number" name="cash" placeholder="Cash" id="cash"
                   style="width: 225px; height:30px;  margin-bottom: 15px;" value=""
                   onkeyup="myFirstInputFunction(this);" step="any" required/>

            <br>

            <select name="mode_of_payment_1" id="mode_of_payment_1"
                    style="width: 268px; height:30px;  margin-bottom: 15px;"
                    onchange="callJavaScriptOnSecondMenu(this);" step="any" required>
                <option value="-1">-- Mode of Payment--</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Account">Account</option>
                <option value="Online">Online</option>
            </select>

            <br>

            <span style="vertical-align: middle; width: auto; padding-right:0">AED</span>
            <input type="number" name="remaining_cash" id="remaining_cash" placeholder="Cash"
                   style="width: 225px; height:30px;  margin-bottom: 15px;" value=""
                   onkeydown="mySecondInputFunction(this);" step="any" readonly/>


            <?php

            ?>
            <button class="btn btn-success btn-block btn-large" style="width:267px;" id="saveButton" disabled><i
                    class="icon icon-save icon-large"></i> Save
            </button>
        </center>
    </div>
</form>

<script type="text/javascript">

    function callJavaScriptOnFirstMenu(selectedValue) {
        var credit_time = document.getElementById('credit_time').value;
        var credit_cash = document.getElementById('credit_cash').value;
        var inputValue  = $(selectedValue).val();

        console.log(credit_cash);

        if (inputValue == 'Cash') {

        }
        else if (inputValue == 'Card') {

        }

        else if (inputValue == 'Account') {

        }

        else if (inputValue == 'Online') {

        }

        else if (inputValue == 'credit_cash') {
            document.getElementById("cash").value = credit_cash;
            myFirstInputFunction(document.getElementById("cash"));
        }

        else if (inputValue == 'credit_time') {

        }

        else {

        }

    }

    function callJavaScriptOnSecondMenu(selectedValue) {
        var credit_time = document.getElementById('credit_time').value;
        var credit_cash = document.getElementById('credit_cash').value;
        var inputValue  = $(selectedValue).val();

        var remainingValue = document.getElementById("remaining_cash").value;

        if (inputValue == 'Cash' || inputValue == 'Card'
            || inputValue == 'Account' || inputValue == 'Online'
            || inputValue == 'credit_cash' || inputValue == 'credit_time') {

            if (remainingValue > 0) {
                var btn      = document.getElementById("saveButton");
                btn.disabled = false;
            }
        }
        else {
            if (remainingValue > 0) {
                var btn      = document.getElementById("saveButton");
                btn.disabled = true;
            }
        }

    }

    function myFirstInputFunction(value) {
        console.log(value.value);

        var paidValue                                   = value.value;
        var total_value                             = document.getElementById("total_cash").value;
        var remainingValue                              = total_value - paidValue;
        document.getElementById("remaining_cash").value = remainingValue;

        if (remainingValue == 0) {
            var btn      = document.getElementById("saveButton");
            btn.disabled = false;
        }
        else {
            var btn      = document.getElementById("saveButton");
            btn.disabled = true;
        }
    }

    function mySecondInputFunction(value) {
        console.log(value.value);
        // remaining_cash
    }

    function myDiscountedInputFunction(selectedValue) {
        var inputValue      = $(selectedValue).val();
        var totalCash       = document.getElementById("total_cash").value;
        var percentageValue = totalCash * (inputValue / 100);
        var afterPerValue   = totalCash - percentageValue;

        $.ajax({
            url: 'api.php',
            type: 'POST',
            data: {
                'call': 'getVatForDiscountedAmountAndInvoice',
                'discounted_amount': afterPerValue,
                'invoice': '<?=$_GET['invoice']?>',
                'saving_flight': '<?=@$_GET['savingflight']?>'
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == 1) {
                    $('#lblVat').html(response.data[0] + " (" + response.data[1] + "%)");
                    $('#discountedValue').val((afterPerValue + response.data[0]).toFixed(2));
                }
            }
        });
    }

    <?php
    $purchased_package = getPurchaseType($_GET['invoice']);
    if($customer_name == 'FDR' || $purchased_package['type'] == FLIGHT_PACKAGE_TYPE_INTERNAL) {
        if(in_array($purchased_package['package_name'], ['giveaways', 'marketing'])) {
        ?>
            $('#mode_of_payment').append('<option selected><?=$purchased_package['package_name']?></option>');
        <?php
        }

        ?>
        $('#cash').val(0);
        $('#saveButton').prop('disabled', false).click();
    <?php
    }
    ?>

</script>

<!-- End here -->

</body>
</html>