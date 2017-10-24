<!DOCTYPE html>
<html>
<head>
    <!-- js -->
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
    <script src="js/jquery-1.12.4.min.js" type="text/javascript"></script>

    <script src="js/image-picker.min.js" type="text/javascript"></script>
    <link href="js/image-picker.css" media="screen" rel="stylesheet" type="text/css"/>

    <script src="src/facebox.js" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('a[rel*=facebox]').facebox({
                loadingImage: 'src/loading.gif',
                closeImage: 'src/closelabel.png'
            })
        })
    </script>
    <title>
        POS
    </title>
    <?php
    include_once('../connect.php');
    ?>

    <link href="vendors/uniform.default.css" rel="stylesheet" media="screen">
    <link href="css/bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- combosearch box-->
    <script src="vendors/bootstrap.js"></script>


    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }

        .sidebar-nav {
            padding: 9px 0;
        }

        .custom-img {
            max-width: 200px;
            height: auto;
        }

        .custom-img p {
            text-align: center;
            margin-top: 5px;
        }

        .radio-inline {
            display: inline;
            margin-left: 10px;
        }

        .radio-inline input {
            margin-right: 5px;
            vertical-align: top;
        }

        .row .span2 {
            padding-left: 40px;
        }

        .selectedProduct {
            max-width: 400px;
            height: auto;
        }
    </style>
    <!--sa poip up-->

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
    </SCRIPT>

</head>
<?php
$finalcode = 'RS-' . createRandomPassword();
?>
<body>
<?php include('navfixed.php'); ?>
<?php
$position = $_SESSION['SESS_LAST_NAME'];
if ($position == 'cashier') {

    echo '<a href="sales.php?id=cash&invoice=' . $finalcode . '">Cash</a>';
    ?>

    <a href="../index.php">Logout</a>
    <?php
}
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
                                <font color="white">Time: <br></font>&nbsp;<input style="width:150px;" type="text"
                                                                                  class="trans" name="face" value=""
                                                                                  disabled>
                            </form>
                        </div>
                    </li>

                </ul>
            </div><!--/.well -->
        </div><!--/span-->
        <div class="span10">
            <div class="contentheader">
                <i class="icon-money"></i>
                <?php echo $_SESSION['SESS_LAST_NAME'] == 'cashier' ? 'Merchandise' : 'Sales'; ?>
            </div>
            <ul class="breadcrumb">
                <a href="index.php">
                    <li>Dashboard</li>
                </a> /
                <li class="active">
                    <?php echo $_SESSION['SESS_LAST_NAME'] == 'cashier' ? 'Merchandise' : 'Sales'; ?>
                </li>
            </ul>
            <div style="margin-top: -19px; margin-bottom: 21px;">
                <a href="index.php">
                    <button class="btn btn-default btn-large" style="float: none;"><i
                            class="icon icon-circle-arrow-left icon-large"></i> Back
                    </button>
                </a>
            </div>

            <form action="incoming.php" method="post">

                <input type="hidden" name="pt" value="<?php echo $_GET['id']; ?>"/>
                <input type="hidden" name="invoice" value="<?php echo $_GET['invoice']; ?>"/>
                <input type="hidden" id="product" name="product"/>

                Category : <select class="category">
                    <?php
                    $result = $db->prepare("SELECT * FROM product_categories WHERE parent_id IS NULL OR parent_id = 0");
                    $result->execute();
                    while ($row = $result->fetch()) {
                        echo sprintf('<option value=""></option><option value="%d" data-img-src="img/%s" data-img-class="custom-img">%s</option>', $row['id'], $row['image'], $row['category_name']);
                    }
                    ?>
                </select>

                <div style="display: none;">
                    Sub-category : <select class="subcategory">
                    </select></div>

                <div class="row" style="display: none;">
                    <div class="span2">Product :</div>
                    <div class="span4">
                        <select class="product"></select>
                    </div>
                </div>

                <div class="row" style="display: none;">
                    <div class="span2">Gender :</div>
                    <div class="span4 gender" style="display: inline;">
                    </div>
                </div>

                <div class="row" style="display: none;">
                    <div class="span2">Size :</div>
                    <div class="span4 size" style="display: inline;">
                    </div>
                </div>

                <div class="row" style="display: none;">
                    <div class="span2">Color :</div>
                    <div class="span4 color" style="display: inline;">
                    </div>
                </div>

                <div class="row quantity" style="display: none;">
                    <div class="span2">Quantity:</div>
                    <div class="span4">
                        <input type="number" name="qty" value="1" min="1" placeholder="Qty" autocomplete="off"
                               style="width: 68px; height:30px; padding-top:6px; padding-bottom: 4px; margin-right: 4px; font-size:15px;"
                        / required>
                        <input type="hidden" name="discount" value="" autocomplete="off"
                               style="width: 68px; height:30px; padding-top:6px; padding-bottom: 4px; margin-right: 4px; font-size:15px;"/>
                        <input type="hidden" name="date" value="<?php echo date("m/d/y"); ?>"/>
                        <Button type="submit" class="btn btn-info btnAdd"
                                style="width: 123px; height:35px; margin-top:-5px;" disabled>
                            <i class="icon-plus-sign icon-large"></i> Add
                        </button>
                    </div>
                    <div class="span6">
                        <img class="selectedProduct" src="" style="width: 100%; height: auto;"/>
                    </div>
                </div>
            </form>
            <table class="table table-bordered" id="resultTable" data-responsive="table">
                <thead>
                <tr>
                    <th> Product Code</th>
                    <th> Item</th>
                    <th> Category / Description</th>
                    <th> Price</th>
                    <th> Qty</th>
                    <th> Amount</th>
                    <th> Profit</th>
                    <th> Action</th>
                </tr>
                </thead>
                <tbody>

                <?php
                $id     = $_GET['invoice'];
                $result = $db->prepare("SELECT * FROM sales_order WHERE invoice= :userid");
                $result->bindParam(':userid', $id);
                $result->execute();
                for ($i = 1; $row = $result->fetch(); $i++) {
                    ?>
                    <tr class="record">
                        <td hidden><?php echo $row['product']; ?></td>
                        <td><?php echo $row['product_code']; ?></td>
                        <td><?php $salesType = $row['gen_name'];
                            echo $row['gen_name']; ?></td>
                        <td><?php $productName = $row['name'];
                            echo $row['name']; ?></td>

                        <td>
                            <?php
                            $ppp = $row['price'];
                            echo formatMoney($ppp, true);
                            ?>
                        </td>
                        <td><?php echo $row['qty']; ?></td>
                        <td>
                            <?php
                            $dfdf = $row['amount'];
                            echo formatMoney($dfdf, true);
                            ?>
                        </td>
                        <td>
                            <?php
                            $profit = $row['profit'];
                            echo formatMoney($profit, true);
                            ?>
                        </td>
                        <td width="90"><a
                                href="delete.php?id=<?php echo $row['transaction_id']; ?>&invoice=<?php echo $_GET['invoice']; ?>&dle=<?php echo $_GET['id']; ?>&qty=<?php echo $row['qty']; ?>&code=<?php echo $row['product']; ?>">
                                <button class="btn btn-mini btn-warning"><i class="icon icon-remove"></i> Cancel
                                </button>
                            </a></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <td> Total Amount:</td>
                    <td> Total Profit:</td>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="5"><strong style="font-size: 12px; color: #222222;">Total:</strong></th>
                    <td colspan="1"><strong style="font-size: 12px; color: #222222;">
                            <?php
                            function formatMoney($number, $fractional = false) {
                                if ($fractional) {
                                    $number = sprintf('%.2f', $number);
                                }
                                while (true) {
                                    $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
                                    if ($replaced != $number) {
                                        $number = $replaced;
                                    } else {
                                        break;
                                    }
                                }

                                return $number;
                            }

                            $sdsd     = $_GET['invoice'];
                            $resultas = $db->prepare("SELECT sum(amount) FROM sales_order WHERE invoice= :a");
                            $resultas->bindParam(':a', $sdsd);
                            $resultas->execute();
                            for ($i = 0; $rowas = $resultas->fetch(); $i++) {
                                $fgfg = $rowas['sum(amount)'];
                                echo formatMoney($fgfg, true);
                            }
                            ?>
                        </strong></td>
                    <td colspan="1"><strong style="font-size: 12px; color: #222222;">
                            <?php
                            $resulta = $db->prepare("SELECT sum(profit) FROM sales_order WHERE invoice= :b");
                            $resulta->bindParam(':b', $sdsd);
                            $resulta->execute();
                            for ($i = 0; $qwe = $resulta->fetch(); $i++) {
                                $asd = $qwe['sum(profit)'];
                                echo formatMoney($asd, true);
                            }
                            ?>

                    </td>
                    <th></th>
                </tr>

                </tbody>
            </table>
            <br>
            <a rel="facebox"
               href="checkout.php?pt=<?php echo $_GET['id'] ?>&
               invoice=<?php echo $_GET['invoice'] ?>&
               total=<?php echo $fgfg ?>&
               totalprof=<?php echo $asd ?>&
               salesType=<?php echo $salesType ?>&
               productName=<?php echo $productName ?>&
               cashier=<?php echo $_SESSION['SESS_FIRST_NAME'] ?>">
                <button class="btn btn-success btn-large btn-block"><i class="icon icon-save icon-large"></i> SAVE
                </button>
            </a>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
</body>
<?php include('footer.php'); ?>
</html>

<script type="text/javascript">
    $(function () {
        $('.category').imagepicker({
            show_label: true,
            changed: function (select, newValues, oldValues, event) {
                getSubCategories(newValues[0]);
            }
        });

        function getSubCategories(parentId) {

            $('.product').parents('.row:eq(0)').hide().nextAll('.row').hide();
            $('.selectedProduct').hide();

            if(parentId > 0) {
                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    data: {
                        'call': 'getProductSubcategories',
                        'parentId': parentId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success == 1) {

                            if ($('.subcategory').data('picker') != undefined) {
                                $('.subcategory').data('picker').destroy();
                            }
                            $('.subcategory').find('option').remove();
                            for (var i in response.data) {
                                var item = response.data[i];
                                $('.subcategory').append('<option value=""></option><option value="' + item.id + '" data-img-src="img/' + item.image + '"  data-img-class="custom-img">' + item.category_name + '</option>')
                            }
                            $('.subcategory').imagepicker({
                                show_label: true,
                                changed: function (select, newValues, oldValues, event) {
                                    getProducts(newValues[0]);
                                }
                            }).parent('div').show();
                        }
                    }
                });
            } else {
                $('.subcategory').parents('div:eq(0)').hide().nextAll('.row').hide();
                $('.selectedProduct').hide();
            }
        }

        var arrProductAttr = [];

        function getProducts(subcategoryId) {
            if(subcategoryId > 0) {
                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    data: {
                        'call': 'getProducts',
                        'categoryId': subcategoryId
                    },
                    dataType: 'json',
                    success: function (response) {
                        $('.product').parents('.row:eq(0)').show();
                        if (response.success == 1) {
                            $('.product').find('option').remove()
                            for (var i in response.data) {
                                arrProductAttr = response.data[i];
                                $('.product').append('<option>' + arrProductAttr['name'] + '</option>')
                            }

                            $('.product').off().on('change', function (e) {
                                getGenders();
                            }).trigger('change').parents('.row').show();
                        }
                    }
                });
            } else {
                $('.product').parents('.row:eq(0)').hide().nextAll('.row').hide();
                $('.selectedProduct').hide();
            }
        }

        function getGenders() {
            if($('.product').val() != '') {
                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    data: {
                        'call': 'getGenders',
                        'commonName': $('.product').val(),
                    },
                    dataType: 'json',
                    success: function (response) {
                        $('.gender').parents('.row:eq(0)').show();
                        $('.gender').html('');
                        for (var i in response.data) {
                            $('.gender').append('<label class="radio-inline"><input type="radio" data-product-id="' + response.data[i]['product_id'] + '" name="radio1" value="' + response.data[i]['gender'] + '">' + response.data[i]['gender'] + '</label>')
                        }
                        $('.gender').off().on('change', function (e) {
                            getSizes();
                        }).trigger('change').parents('.row').show();

                        if($('.gender input:eq(0)').val() == 'NA') {
                           $('.gender input').click();
                        }
                    }
                });
            } else {
                $('.gender').parents('.row:eq(0)').hide().nextAll('.row').hide();
                $('.selectedProduct').hide();
            }
        }

        function getSizes() {
            if($('.product').val() != '' && $('.gender input:checked').val() != '') {
                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    data: {
                        'call': 'getSizes',
                        'commonName': $('.product').val(),
                        'gender': $('.gender input:checked').val()
                    },
                    dataType: 'json',
                    success: function (response) {
                        $('.size').parents('.row:eq(0)').show();
                        $('.size').html('');
                        for (var i in response.data) {
                            $('.size').append('<label class="radio-inline"><input type="radio" data-product-id="' + response.data[i]['product_id'] + '" name="radio2" value="' + response.data[i]['size'] + '">' + response.data[i]['size'] + '</label>')
                        }
                        $('.size').off().on('change', function (e) {
                            getColors();
                        }).trigger('change').parents('.row').show();

                        if($('.size input:eq(0)').val() == 'NA') {
                            $('.size input').click();
                        }
                    }
                });
            } else {
                $('.size').parents('.row:eq(0)').hide().nextAll('.row').hide();
                $('.selectedProduct').hide();
            }
        }

        function getColors() {
            if($('.product').val() != '' && $('.gender input:checked').val() != '' && $('.size input:checked').val() != '') {
                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    data: {
                        'call': 'getColors',
                        'commonName': $('.product').val(),
                        'gender': $('.gender input:checked').val(),
                        'size': $('.size input:checked').val()
                    },
                    dataType: 'json',
                    success: function (response) {
                        $('.color').parents('.row:eq(0)').show();
                        $('.color').html('');
                        for (var i in response.data) {
                            $('.color').append('<label class="radio-inline"><input type="radio" data-product-id="' + response.data[i]['product_id'] + '"  data-product-img="' + response.data[i]['image'] + '" name="radio3" value="' + response.data[i]['Attribute'] + '">' + response.data[i]['Attribute'] + '</label>')
                        }
                        $('.color input').off().on('change', function (e) {
                            if ($(this).is(':checked')) {
                                $('#product').val($(this).data('product-id'));
                                $('.btnAdd').prop('disabled', false);
                                $('.quantity').show();
                                $('.selectedProduct').show().attr('src', 'img/'+$(this).data('product-img'));
                            }
                        }).trigger('change').parents('.row').show();

                        if($('.color input:eq(0)').val() == 'NA') {
                            $('.color input').click();
                        }
                    }
                });
            } else {
                $('.color').parents('.row:eq(0)').hide().nextAll('.row').hide();
                $('.selectedProduct').hide();
            }
        }
    })
</script>

