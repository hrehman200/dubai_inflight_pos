<?php

require_once(dirname(__DIR__) . '/connect.php');

//session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$position = $_SESSION['SESS_LAST_NAME'];
if ($position == 'cashier'  || $position == 'Operator' || $_SESSION[SESS_MOCK_ROLE] == ROLE_CASHIER) {
	$finalcode = 'RS-' . createRandomPassword();
	echo '
   <li class="' . ($current_page == 'index.php' ? 'active' : '') . '"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li>
			<li class="' . ($current_page == 'sales.php' ? 'active' : '') . '"><a href="sales.php?id=cash&invoice=' . $finalcode . '"><i class="icon-shopping-cart icon-2x"></i> Merchandise</a>  </li>
			<li class="' . ($current_page == 'salesreport.php' ? 'active' : '') . '"><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a></li>
			<li class="' . (in_array($current_page, array('flight_packages.php', 'flight_picker.php')) ? 'active' : '') . '"><a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a></li>
		    <li class="' . ($current_page == 'customer.php' ? 'active' : '') . '"><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a></li>
';
	//<li class="'.($current_page=='products.php'?'active':'').'"><a href="products.php"><i class="icon-list-alt icon-2x"></i> Products</a></li>
	//	<li class="'.($current_page=='customer.php'?'active':'').'"><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a></li>
	//	<li class="'.($current_page=='partners.php'?'active':'').'"><a href="partners.php"><i class="icon-group icon-2x"></i> Partners</a></li>

} else if ($position == 'admin') {
	$finalcode = 'RS-' . createRandomPassword();
	echo '
   <li class="' . ($current_page == 'index.php' ? 'active' : '') . '"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li>
			
<li class="' . ($current_page == 'collect_meraas.php' ? 'active' : '') . '"><a href="collect_meraas.php"><i class="icon-bar-chart icon-2x"></i> End of day Report </a>  </li>
			<li class="' . ($current_page == 'revenue_liability_customer.php' ? 'active' : '') . '"><a href="revenue_liability_customer.php?d1=0&d2=0"><i class="icon-money icon-2x"></i> Customer Liability</a></li>
			<li class="' . ($current_page == 'collection_other.php' ? 'active' : '') . '"><a href="collection_other.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Collection Report</a></li>
			<li class="' . ($current_page == 'revenue_liability_acc.php' ? 'active' : '') . '"><a href="revenue_liability_acc.php?d1=0&d2=0"><i class="icon-money icon-2x"></i> Revenue & Liability Acc.</a></li>
			<li class="' . ($current_page == 'discounts.php' ? 'active' : '') . '"><a href="discounts.php"><i class="icon-arrow-down icon-2x"></i> Discounts</a></li>
			<li class="' . ($current_page == 'packages.php' ? 'active' : '') . '"><a href="packages.php"><i class="icon-list icon-2x"></i> Flight Packages</a></li>';
	//<li class="'.($current_page=='collection_other.php'?'active':'').'"><a href="collection_other.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Master Report</a></li>
	//<li class="'.($current_page=='sales.php'?'active':'').'"><a href="sales.php?id=cash&invoice=' . $finalcode . '"><i class="icon-shopping-cart icon-2x"></i> Sales</a>  </li>
	//<li class="'.($current_page=='products.php'?'active':'').'"><a href="products.php"><i class="icon-list-alt icon-2x"></i> Products</a></li>
	//<li class="'.($current_page=='customer.php'?'active':'').'"><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a></li>
	//<li class="'.($current_page=='supplier.php'?'active':'').'"><a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a></li>
	//<li class="'.($current_page=='partners.php'?'active':'').'"><a href="partners.php"><i class="icon-group icon-2x"></i> Partners</a></li>
	//<li class="'.($current_page=='Businessplan.php'?'active':'').'"><a href="Businessplan.php"><i class="icon-group icon-2x"></i> Business Plan</a></li>
	//<li class="'.($current_page=='accounts.php'?'active':'').'"><a href="accounts.php"><i class="icon-group icon-2x"></i> Accounts</a></li>
	//<li class="'.($current_page=='supplier.php'?'active':'').'"><a href="supplier.php"><i class="icon-group icon-2x"></i> Operators</a></li>
	//<li class="'.($current_page=='salesreport.php'?'active':'').'"><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a></li>
	//<li class="'.(in_array($current_page, array('flight_packages.php','flight_picker.php'))?'active':'').'"><a href="flight_packages.php"><i class="icon-bar-chart icon-2x"></i> Booking Calander</a></li>
	//<li class="'.($current_page=='salesreport.php'?'active':'').'"><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a></li>
} else if ($position == 'account' || $_SESSION[SESS_MOCK_ROLE] == ROLE_ACCOUNT) {
	echo '<li class="' . ($current_page == 'index.php' ? 'active' : '') . '"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li>
				<li class="' . ($current_page == 'collect_meraas.php' ? 'active' : '') . '"><a href="collect_meraas.php"><i class="icon-bar-chart icon-2x"></i> End of Day </a>  </li>
				<li class="' . ($current_page == 'collection_other.php' ? 'active' : '') . '"><a href="collection_other.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Collection Report</a></li>
				<li class="' . ($current_page == 'revenue_liability_acc.php' ? 'active' : '') . '"><a href="revenue_liability_acc.php?d1=0&d2=0"><i class="icon-money icon-2x"></i> Revenue & Liability Acc.</a></li>
				<li class="' . ($current_page == 'revenue_liability_customer.php' ? 'active' : '') . '"><a href="revenue_liability_customer.php?d1=0&d2=0"><i class="icon-money icon-2x"></i> Customer Liability</a></li>
				<li class="' . ($current_page == 'customer.php' ? 'active' : '') . '"><a href="customer.php"><i class="icon-group icon-2x"></i> Customers</a></li>
				<li class="' . ($current_page == 'Partners.php' ? 'active' : '') . '"><a href="partners.php"><i class="icon-group icon-2x"></i> Partners</a></li> 
				<li class="' . ($current_page == 'fixer_rnl.php' ? 'active' : '') . '"><a href="fixer_rnl.php"><i class="icon-cog icon-2x"></i> Fix RnL</a></li> 
				';
	//<li class="'.($current_page=='supplier.php'?'active':'').'"><a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a></li>
	//<li class="'.($current_page=='partners.php'?'active':'').'"><a href="partners.php"><i class="icon-group icon-2x"></i> Partners</a></li>
	//<li class="'.($current_page=='purchaseslist.php'?'active':'').'"><a href="purchaseslist.php"><i class="icon-inbox icon-2x"></i> Purchases</a></li>
	//li class="'.($current_page=='accountreceivables.php'?'active':'').'"><a href="accountreceivables.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Accounts Receivable Report</a>    </li>
	//<li class="'.($current_page=='salesreport.php'?'active':'').'"><a href="salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i> Sales Report</a></li>
	//<li class="'.($current_page=='select_customer.php'?'active':'').'"><a rel="facebox" href="select_customer.php"><i class="icon-user icon-2x"></i> Customer Ledger</a></li>
	//
} else if ($position == ROLE_PROCUREMENT || $_SESSION[SESS_MOCK_ROLE] == ROLE_PROCUREMENT) {
	echo '<li class="' . ($current_page == 'index.php' ? 'active' : '') . '"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li>
				<li class="' . ($current_page == 'purchaseslist.php' ? 'active' : '') . '"><a href="purchaseslist.php"><i class="icon-bar-chart icon-2x"></i> Procurement</a></li>
				<li class="' . ($current_page == 'supplier.php' ? 'active' : '') . '"><a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a></li>
				<li class="' . ($current_page == 'products.php' ? 'active' : '') . '"><a href="products.php"><i class="icon-table icon-2x"></i> Inventory</a></li>';
} else if ($position == 'Management') {
	echo '
   <li class="' . ($current_page == 'index.php' ? 'active' : '') . '"><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard </a></li>
			<li class="' . ($current_page == 'Businessplan.php' ? 'active' : '') . '"><a href="Businessplan.php"><i class="icon-list icon-2x"></i> Business Plan</a></li>
			<li class="' . ($current_page == 'revenue_liability.php' ? 'active' : '') . '"><a href="revenue_liability.php?d1=0&d2=0"><i class="icon-money icon-2x"></i> Revenue & Liability</a></li>
			<li class="' . ($current_page == 'supplier.php' ? 'active' : '') . '"><a href="supplier.php"><i class="icon-group icon-2x"></i> Suppliers</a></li>
			<li class="' . ($current_page == 'partners.php' ? 'active' : '') . '"><a href="partners.php"><i class="icon-group icon-2x"></i> Partners</a></li>';
}

if ($position == ROLE_MANAGEMENT) {
?>
	<hr />
	<li class=""><a href="mock_role.php?r=<?= ROLE_CASHIER ?>"><i class="icon-user icon-2x"></i> Reception</a></li>
	<li class=""><a href="mock_role.php?r=<?= ROLE_ACCOUNT ?>"><i class="icon-user icon-2x"></i> Managers</a></li>
	<li class=""><a href="mock_role.php?r=<?= ROLE_PROCUREMENT ?>"><i class="icon-user icon-2x"></i> Support Dept.</a></li>
	<li class=""><a href="mock_role.php?r=<?= ROLE_MANAGEMENT ?>"><i class="icon-user icon-2x"></i> Admin</a></li>
<?php
}

?>