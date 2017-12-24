
<div id="mainmain">
<?php

//session_start();

$position=$_SESSION['SESS_LAST_NAME'];
if($position=='cashier' || $position == 'Operator') {
    echo '
            <a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i><br> Merchandise</a>
			<a href="products.php"><i class="icon-list-alt icon-2x"></i><br>Products</a>
			<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Partners</a>
			<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Sales Report</a>
			<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>';

}
else if($position=='admin') {
    echo '
            <!--<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>-->
			<a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i> <br/> Sales</a>  </li>
			<a href="products.php"><i class="icon-list-alt icon-2x"></i> <br/> Products</a>
			<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Partners</a>
			<a href="Businessplan.php"><i class="icon-group icon-2x"></i> <br/> Business Plan</a>
			<a href="accounts.php"><i class="icon-group icon-2x"></i> <br/> Accounts</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Operators</a>
			<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Sales Report</a>
			<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>
			<!--<a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> <br/> Booking Calander</a>-->
			';}

else if ($position=='account') {
    echo '<!--<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>-->
				<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Sales Report</a>
				<a href="collection_other.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Collection Report</a>     
				<a rel="facebox" href="select_customer.php"><i class="icon-user icon-2x"></i> <br/> Customer Ledger</a> 
				<a href="accountreceivables.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Accounts Receivable Report</a></li> 
				<a href="products.php"><i class="icon-table icon-2x"></i> <br/> Products</a>   </li>      
				<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>        
				<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Suppliers</a>        
				<a href="purchaseslist.php"><i class="icon-inbox icon-2x"></i> <br/> Purchases</a>
				<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a> ';

}
else if ($position=='Procurement') {
    echo '<!--<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>-->
				<a href="purchaseslist.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Procurement</a>
				<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Suppliers</a>';

}
else if ($position=='Management') {
    echo '
            <!--<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>-->
			<a href="Businessplan.php"><i class="icon-group icon-2x"></i> <br/> Business Plan</a>
			<!--<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>-->
			<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Partners</a>
			<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>
			<!--<a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> <br/> Booking Calander</a>-->';


}
?>
    <div class="clearfix"></div>
</div>

