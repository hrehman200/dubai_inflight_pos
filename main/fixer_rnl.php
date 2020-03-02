<?php
include('header.php');

if (isset($_POST['month']) && $_SESSION['counter'] < 6) {

    // generate for 5 days in one go
    $_SESSION['counter'] += 1;
    if ($_SESSION['counter'] == 1) {
        $start = '01';
    } else {
        $start =  (($_SESSION['counter'] - 1) * 5) + 1;
        $start = str_pad($start, 2, '0', STR_PAD_LEFT);
    }

    $start_date = new DateTime(sprintf('%d-%s-%s', $_POST['year'], $_POST['month'], $start));
    $end = $_SESSION['counter'] * 5;
    $end = str_pad($end, 2, '0', STR_PAD_LEFT);

    $end_date = new DateTime(sprintf('%d-%s-%s', $_POST['year'], $_POST['month'], $end));
    if ($_SESSION['counter'] == 6) {
        $end_date->modify('last day of this month');
        unset($_SESSION['counter']);
    }

    $query = $db->query(sprintf('DELETE FROM rnl_cache WHERE date >= "%s" AND date <= "%s"', $start_date->format('Y-m-d'), $end_date->format('Y-m-d')));
    $query->execute();

    $ftf_discounts = getDiscountsOf();
    $military_discounts = getDiscountsOfParent('Military');

    while ($start_date <= $end_date) {

        // we will be saving each row for each day
        $start_end = $start_date->format('Y-m-d');

        $arr_revenue = [];

        $arr_ftf = getFTFRevenue($start_end, $start_end, false, false);

        foreach ($arr_ftf as $item) {
            if (in_array($item['package_name'], $ftf_discounts)) {
                $discount_name = $item['package_name'];
                saveRnLRow($start_end, $discount_name, 'FTF', $item['paid'], $item['total_minutes'], $item['minutes_used'], $item['aed_value'], $item['avg_per_min']);
                foreach ($item[$discount_name] as $discount_item) {
                    saveRnLRow($start_end, $discount_item['package_name'], $discount_name, $discount_item['paid'], $discount_item['total_minutes'], $discount_item['minutes_used'], $discount_item['aed_value'], $discount_item['avg_per_min']);
                }
            } else {
                saveRnLRow($start_end, $item['package_name'], null, $item['paid'], $item['total_minutes'], $item['minutes_used'], $item['aed_value'], $item['avg_per_min']);
            }
        }

        /** UP-Sale */
        $arr = getDataAndAggregate('UP-Sale', $start_end, $start_end);
        saveRnLRow($start_end, 'UP-Sale', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

        /** RF */
        $arr = getDataAndAggregate('RF - Repeat Flights', $start_end, $start_end);
        saveRnLRow($start_end, 'RF - Repeat Flights', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

        /** SKYDIVERS */
        $arr = getDataAndAggregate('Skydivers', $start_end, $start_end);
        saveRnLRow($start_end, 'Skydivers', null, $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);

        foreach ($military_discounts as $military_discount) {

            $military_pkg = '';
            switch ($military_discount) {

                case 'Military':
                    $military_pkg = 'Military'; // in db
                    $military_discount = 'Military Individuals'; // how its shown in RnL
                    break;

                case 'Navy Seal':
                case 'Navy Seal Zero %':
                    $military_pkg = $military_discount = 'Navy Seal';
                    break;

                case 'Sky god%':
                    $military_pkg = 'Sky god%';
                    $military_discount = 'US Navy';
                    break;

                default:
                    $military_pkg = $military_discount;
            }

            $arr = getDataAndAggregate($military_pkg, $start_end, $start_end);
            saveRnLRow($start_end, $military_discount, 'Military', $arr[0]['paid'], $arr[0]['total_minutes'], $arr[0]['minutes_used'], $arr[0]['aed_value'], $arr[0]['avg_per_min']);
        }

        /** HELMET RENT */
        $arr = getMerchandiseRevenue('Helmet Rent', $start_end, $start_end);
        saveRnLRow($start_end, 'Helmet Rent', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

        /** VIDEO */
        $arr = getMerchandiseRevenue('Video', $start_end, $start_end);
        saveRnLRow($start_end, 'Videos/Photos', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

        /** MERCHANDISE */
        $arr = getMerchandiseRevenue(TYPE_MERCHANDISE, $start_end, $start_end);
        saveRnLRow($start_end, TYPE_MERCHANDISE, 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

        /** OTHER e.g. Facility Rental, Sandstorm Registration Fee  */
        $arr = getOtherRevenue('Other', $start_end, $start_end);
        saveRnLRow($start_end, 'Other', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

        /** Class session */
        $arr = getClassSessionRevenue('Class Session', $start_end, $start_end);
        saveRnLRow($start_end, 'Class Session', 'Retail Revenue', $arr[0]['paid'], 0, 0, $arr[0]['aed_value'], 0);

        $start_date->modify('+1 day');
    }
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

                </ul>
            </div>
            <!--/.well -->
        </div>
        <!--/span-->
        <div class="span10">
            <h3>Fix RnL for Month</h3>
            <form method="post" id="formRnLCache" action="fixer_rnl.php">
                <select name="month" id="month">
                    <option val="01">Jan</option>
                    <option val="02">Feb</option>
                    <option val="03">Mar</option>
                    <option val="04">Apr</option>
                    <option val="05">May</option>
                    <option val="06">Jun</option>
                    <option val="07">Jul</option>
                    <option val="08">Aug</option>
                    <option val="09">Sep</option>
                    <option val="10">Oct</option>
                    <option val="11">Nov</option>
                    <option val="12">Dec</option>
                </select>
                <select name="year" id="year">
                    <option>2020</option>
                    <option>2021</option>
                    <option>2022</option>
                    <option>2023</option>
                </select>
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        </div>
    </div>
    </body>

    <?php include('footer.php'); ?>

    </html>

    <script type="text/javascript">
        <?php if ($_SESSION['counter'] >= 1 && $_SESSION['counter'] < 6) { ?>
            $('#month').val('<?= $_POST['month'] ?>');
            $('#year').val('<?= $_POST['year'] ?>');
            $('#formRnLCache').submit();
        <?php } ?>
    </script>