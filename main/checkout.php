<?php
include '../connect.php';
?>
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
        <center><h4><i class="icon icon-money icon-large"></i> Cash</h4></center>
        <hr>
        <input type="hidden" name="date" value="<?php echo date("m/d/y"); ?>"/>
        <input type="hidden" name="invoice" value="<?php echo $_GET['invoice']; ?>"/>
        <input type="hidden" name="amount" value="<?php echo $_GET['total']; ?>"/>
        <input type="hidden" name="cashier" value="<?php echo $_GET['cashier']; ?>"/>
        <input type="hidden" name="profit" value="<?php echo $_GET['totalprof']; ?>"/>
        <input type="hidden" name="savingflight" value="<?php echo @$_GET['savingflight']; ?>"/>
        <input type="hidden" name="customerId" value="<?php echo @$_GET['customerId']; ?>"/>
        <input type="hidden" name="partnerId" value="<?php echo @$_GET['partnerId']; ?>"/>

        <div style="text-align: center;">

            <?php
            if($_GET['partnerId'] > 0) {
                $query = $db->prepare('SELECT * FROM partners WHERE partner_id=?');
                $query->execute(array($_GET['partnerId']));
                $row = $query->fetch();
                $discount = (int)$row['discount'];
            }
            ?>

            <select name="mode_of_payment" id="mode_of_payment" <?=isset($discount)?'disabled':''?>
                    style="width: 268px; height:30px;  margin-bottom: 15px;" required>
                <option value="-1">-- Mode of Payment--</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Account" <?=isset($discount)?'selected':''?> >Account</option>
            </select>

            <br/>
            <span style="vertical-align: middle; width: auto; padding-right:0">AED</span>
            <input type="number" name="cash" placeholder="Cash"
                   style="width: 225px; height:30px;  margin-bottom: 15px;" value="<?=$_GET['total']?>" required/>

            <?php
            if(isset($_GET['savingflight']) && $_GET['savingflight'] == 1) {
                if($_GET['partnerId'] > 0) {
                    $query = $db->prepare('SELECT * FROM partners WHERE partner_id=?');
                    $query->execute(array($_GET['partnerId']));
                    $row = $query->fetch();
                    $discount = (int)$row['discount'];

                    $days     = (int)$row['payment_term'];
                    $due_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +30 day'));
                }
                ?>
                <input type="number" name="discount" placeholder="Discount %" style="width: 268px; height:30px;  margin-bottom: 15px;" required value="<?=$discount?>"  />
                <?php

                if($due_date != '') {
                    echo sprintf('<span style="width:auto;">Due Date: %s</span>
                        <input type="hidden" name="due_date" value="%s" />
                        <input type="hidden" name="ptype" value="account" /> ', $due_date, $due_date);
                }
            } else {
                echo sprintf('<input type="hidden" name="ptype" value="%s" /> ', $_GET['pt']);
            }
            ?>

            <br>

            <button class="btn btn-success btn-block btn-large"><i
                    class="icon icon-save icon-large"></i> Save
            </button>
        </div>
    </div>
</form>
</body>
</html>