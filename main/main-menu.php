
<div id="mainmain">
<?php

//session_start();

$position=$_SESSION['SESS_LAST_NAME'];
if($position=='cashier' || $position == 'Operator' || $_SESSION[SESS_MOCK_ROLE] == ROLE_CASHIER) {
    echo '
            <a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i><br> Merchandise</a>
			<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> End of Shift</a>
			<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>
			<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>';
			//<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Partners</a>
			//<a href="products.php"><i class="icon-list-alt icon-2x"></i><br>Products</a>
			//<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>

}
else if($position=='admin') {
    echo '
            
			<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Sales Report</a>
			<a href="collect_meraas.php"><i class="icon-bar-chart icon-2x"></i> <br/> End of Day Report</a>  </li>
			<a href="collection_other.php"><i class="icon-table icon-2x"></i> <br/> Master Report</a>
			<a href="revenue_liability_customer.php?d1=0&d2=0"><i class="icon-money icon-2x"></i><br/> Customer Liability</a>
			<a href="collection_other.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Collection Report</a>
            <a href="revenue_liability_acc.php?d1=0&d2=0"><i class="icon-money icon-2x"></i><br/> Revenue & Liability Acc</a>';}

			//<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>
			//<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Partners</a>
			//<a href="Businessplan.php"><i class="icon-group icon-2x"></i> <br/> Business Plan</a>
			//<a href="accounts.php"><i class="icon-group icon-2x"></i> <br/> Accounts</a>
			//<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Operators</a>
			//<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Sales Report</a>
			//<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>
			//<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>
			//<!--<a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> <br/> Booking Calander</a>-->
			

else if ($position=='account' || $_SESSION[SESS_MOCK_ROLE] == ROLE_ACCOUNT) {
    echo '<!--<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>-->
				
				<a href="collect_meraas.php"><i class="icon-bar-chart icon-2x"></i> <br/> End of Day Report</a>  </li>
				<a href="collection_other.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Collection Report</a>
				<a href="revenue_liability_acc.php?d1=0&d2=0"><i class="icon-money icon-2x"></i><br/> Revenue & Liability Acc</a>
				<a href="products.php"><i class="icon-table icon-2x"></i> <br/> Inventory</a> </li>      
				<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>
				<a href="Partners.php"><i class="icon-group icon-2x"></i> <br/> Partners</a>
				<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>
';        
				//<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Suppliers</a>        
				//<a href="purchaseslist.php"><i class="icon-inbox icon-2x"></i> <br/> Purchases</a>
				//<a href="select_customer.php"><i class="icon-user icon-2x"></i> <br/> Customer Ledger</a> 
				//<a href="accountreceivables.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Accounts Receivable Report</a></li>
				//<a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i><br/> Merchandise </a>
				//<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>
				//<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Sales Report</a>
				//<a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Sales Report</a>

}
else if ($position=='Procurement') {
    echo '<!--<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>-->
				<a href="purchaseslist.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> <br/> Procurement</a>
				<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Suppliers</a>
				<a href="products.php"><i class="icon-table icon-2x"></i> <br/> Inventory</a> </li>   
';

}
else if ($position=='Management') {
    echo '
            <!--<a href="index.php"><i class="icon-dashboard icon-2x"></i> <br/> Dashboard </a>-->
			<a href="Businessplan.php"><i class="icon-group icon-2x"></i> <br/> Business Plan</a>
			<!--<a href="customer.php"><i class="icon-group icon-2x"></i> <br/> Customers</a>-->
			<a href="revenue_liability.php?d1=0&d2=0"><i class="icon-money icon-2x"></i><br/> Revenue & Liability</a>
			<a href="supplier.php"><i class="icon-group icon-2x"></i> <br/> Suppliers</a>';

			//<a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> <br/> Book Flight</a>
			//<!--<a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> <br/> Booking Calander</a>-->';


}
?>
    <div class="clearfix"></div>
</div>

