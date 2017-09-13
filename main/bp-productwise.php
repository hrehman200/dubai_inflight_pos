

<!DOCTYPE html>
<html >
<?php
  require_once('auth.php');

  include('../connect.php');

  $month = $_GET['month'];

  $year = $_GET['year'];


?>

<head>

  <meta charset="UTF-8">
  <link rel="shortcut icon" type="image/x-icon" href="https://production-assets.codepen.io/assets/favicon/favicon-8ea04875e70c4b0bb41da869e81236e54394d63638a1ef12fa558a4a835f1164.ico" />
  <link rel="mask-icon" type="" href="https://production-assets.codepen.io/assets/favicon/logo-pin-f2d2b6d2c61838f7e76325261b7195c27224080bc099486ddd6dccb469b8e8e6.svg" color="#111" />


      <style>
      /*
    Side Navigation Menu V2, RWD
    ===================
    License:
    http://goo.gl/EaUPrt
    ===================
    Author: @PableraShow

 */

@charset "UTF-8";
@import url(http://fonts.googleapis.com/css?family=Open+Sans:300,400,700);

body {
  font-family: 'Open Sans', sans-serif;
  font-weight: 300;
  line-height: 1.42em;
  color:#A7A1AE;
  background-color:#1F2739;
}

h1 {
  font-size:3em;
  font-weight: 300;
  line-height:1em;
  text-align: center;
  color: #4DC3FA;
}

h2 {
  font-size:1em;
  font-weight: 300;
  text-align: center;
  display: block;
  line-height:1em;
  padding-bottom: 2em;
  color: #FB667A;
}

h2 a {
  font-weight: 700;
  text-transform: uppercase;
  color: #FB667A;
  text-decoration: none;
}

.blue { color: #185875; }
.yellow { color: #FFF842; }

.container th h1 {
      font-weight: bold;
      font-size: 1em;
  text-align: left;
  color: #185875;
}

.container td {
      font-weight: normal;
      font-size: 1em;
  -webkit-box-shadow: 0 2px 2px -2px #0E1119;
       -moz-box-shadow: 0 2px 2px -2px #0E1119;
            box-shadow: 0 2px 2px -2px #0E1119;
}

.container {
      text-align: left;
      overflow: hidden;
      width: 80%;
      margin: 0 auto;
  display: table;
  padding: 0 0 8em 0;
}

.container td, .container th {
      padding-bottom: 2%;
      padding-top: 2%;
  padding-left:2%;
}

/* Background-color of the odd rows */
.container tr:nth-child(odd) {
      background-color: #323C50;
}

/* Background-color of the even rows */
.container tr:nth-child(even) {
      background-color: #2C3446;
}

.container th {
      background-color: #1F2739;
}

.container td:first-child { color: #FB667A; }

.container tr:hover {
   background-color: #464A52;
-webkit-box-shadow: 0 6px 6px -6px #0E1119;
       -moz-box-shadow: 0 6px 6px -6px #0E1119;
            box-shadow: 0 6px 6px -6px #0E1119;
}

.container td:hover {
  background-color: #FFF842;
  color: #403E10;
  font-weight: bold;

  box-shadow: #7F7C21 -1px 1px, #7F7C21 -2px 2px, #7F7C21 -3px 3px, #7F7C21 -4px 4px, #7F7C21 -5px 5px, #7F7C21 -6px 6px;
  transform: translate3d(6px, -6px, 0);

  transition-delay: 0s;
      transition-duration: 0.4s;
      transition-property: all;
  transition-timing-function: line;
}

@media (max-width: 800px) {
.container td:nth-child(4),
.container th:nth-child(4) { display: none; }
}
    </style>

  <script>
  window.console = window.console || function(t) {};
</script>



  <script>
  if (document.location.search.match(/type=embed/gi)) {
    window.parent.postMessage("resize", "*");
  }
</script>

</head>

<body translate="no" >

<h1><span class="blue">&lt;</span>Summary<span class="blue">&gt;</span> <span class="yellow">High Level</span></h1>
<h2>Business Intellegence <a rel="nofollow" rel="noreferrer"href="http://inflightdubai..com" target="_blank">Inflight Dubai</a></h2>
<h3 style="text-align:center;"><?php echo $month.", ".$year?></h3>
<?php
$resultSales = $db->prepare("SELECT * FROM forecast_table WHERE month=:month AND year=:year");
$resultSales->execute(array(':month' => $month, ':year' => $year));

$row_forecast = $resultSales->fetch();

$table_rows = '';
foreach($row_forecast as $col=>$value) {
    if(!is_numeric($col) && !in_array($col, array('id', 'month', 'year')) ) {

        $actualSales = $db->prepare("SELECT SUM(s.amount) AS amount FROM sales s
                INNER JOIN sales_order so
                ON s.invoice_number = so.invoice
                WHERE s.month=:month AND s.year=:year
                AND so.name =:type");

        $actualSales->execute(array(
            ':month' => $month,
            ':year' => $year,
            ':type' => $col
        ));

        $row_sales = $actualSales->fetch();
        $actual_sale = $row_sales['amount'];
        $forecast_sale = str_replace(",", "", $value);
        $percentage = ($forecast_sale>0) ? ($actual_sale/ $forecast_sale * 100) : 0;

        $table_rows .= sprintf('<tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s%%</td>
                <td>%s</td>
            </tr>', $col, $forecast_sale, $actual_sale, $percentage, ($forecast_sale-$actual_sale));
    }
}
?>

<table class="container">
    <thead>
        <tr>
            <th><h1>Business Accounts</h1></th>
            <th><h1>Forecast</h1></th>
            <th><h1>Actual</h1></th>
            <th><h1>%_age</h1></th>
            <th><h1>Difference</h1></th>
        </tr>
    </thead>
    <tbody>
        <?php echo $table_rows; ?>
    </tbody>
</table>






</body>
</html>
 