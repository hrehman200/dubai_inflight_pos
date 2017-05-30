
<div id="mainmain">
<?php

//session_start();

$position=$_SESSION['SESS_LAST_NAME'];
if($position=='cashier') {
    echo '
            <a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i><br> Merchandise</a>
			<a href="products.php"><i class="icon-list-alt icon-2x"></i><br>Products</a>
			<a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> Partners</a>
			<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a>
			<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> Book Flight</a>';

}
else if($position=='admin') {
    echo '
            <a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a>
			<a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i> Sales</a>  </li>
			<a href="products.php"><i class="icon-list-alt icon-2x"></i> Products</a>
			<a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> Partners</a>
			<a href="Businessplan.php"><i class="icon-group icon-2x"></i> Business Plan</a>
			<a href="accounts.php"><i class="icon-group icon-2x"></i> Accounts</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> Operators</a>
			<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a>
			<a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a>
			';}

else if ($position=='account') {
    echo '<a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a>
				<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a>							</li>
				<a href="collection.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Collection Report</a>     
				<a href="accountreceivables.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Accounts Receivable Report</a>    </li>
				<a rel="facebox" href="select_customer.php"><i class="icon-user icon-2x"></i> Customer Ledger</a>   
				<a href="products.php"><i class="icon-table icon-2x"></i> Products</a>         
				<a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>        
				<a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a>        
				<a href="purchaseslist.php"><i class="icon-inbox icon-2x"></i> Purchases</a> ';

}
else if ($position=='Procurement') {
    echo '<a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a>
				<a href="purchaseslist.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Procurement</a>
				<a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a>';

}
else if ($position=='Management') {
    echo '
            <a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a>
			<a href="Businessplan.php"><i class="icon-group icon-2x"></i> Business Plan</a>
			<a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> Partners</a>
			<a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a>';


}
?>
    <div class="clearfix"></div>
</div>

