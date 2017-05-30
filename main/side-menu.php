         
<?php

//session_start();

$position=$_SESSION['SESS_LAST_NAME'];
if($position=='cashier') {
echo '
            <li class="active"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li> 
			<li><a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i> Merchandise</a>  </li>
			<li><a href="products.php"><i class="icon-list-alt icon-2x"></i> Products</a>                                     </li>
			<li><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>                                    </li>
			<li><a href="supplier.php"><i class="icon-group icon-2x"></i> Partners</a>                                    </li>
			<li><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a>                </li>
			<li><a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a>                </li>
			';

}
else if($position=='admin') {
	echo '
            <li class="active"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li> 
			<li><a href="sales.php?id=cash&invoice='.$finalcode.'"><i class="icon-shopping-cart icon-2x"></i> Sales</a>  </li>             
			<li><a href="products.php"><i class="icon-list-alt icon-2x"></i> Products</a>                                     </li>
			<li><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>                                    </li>
			<li><a href="supplier.php"><i class="icon-group icon-2x"></i> Partners</a>                                    </li>
			<li><a href="Businessplan.php"><i class="icon-group icon-2x"></i> Business Plan</a>                                    </li>
			<li><a href="accounts.php"><i class="icon-group icon-2x"></i> Accounts</a>                                    </li>
			<li><a href="supplier.php"><i class="icon-group icon-2x"></i> Operators</a>                                    </li>
			<li><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a>                </li>
			<li><a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a>                </li>
			';}

else if ($position=='account') {
	 echo '<li class="active"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li> 
				<li><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a>							</li>
				<li><a href="collection.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Collection Report</a>                     </li>
				<li class="active"><a href="accountreceivables.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Accounts Receivable Report</a>    </li>
				<li><a rel="facebox" href="select_customer.php"><i class="icon-user icon-2x"></i> Customer Ledger</a>                   </li>
				<li><a href="products.php"><i class="icon-table icon-2x"></i> Products</a>                                              </li>
				<li><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>                                             </li>
				<li><a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a>                                             </li>
				<li><a href="purchaseslist.php"><i class="icon-inbox icon-2x"></i> Purchases</a></li> ';

}
else if ($position=='Procurement') {
	 echo '<li class="active"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li> 
				<li><a href="purchaseslist.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Procurement</a></li>
				<li><a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a></li>';
				
}
else if ($position=='Management') {
	 echo '
            <li class="active"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li> 
			<li><a href="Businessplan.php"><i class="icon-group icon-2x"></i> Business Plan</a>                                    </li>    
			<li><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a>                                    </li>
			<li><a href="supplier.php"><i class="icon-group icon-2x"></i> Partners</a>                                    </li>
			<li><a href="bookingcalander.php"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a></li>';
				

}
?>

          
