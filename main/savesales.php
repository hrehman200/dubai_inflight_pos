<?php
include_once('../connect.php');
$invoice_id               = $_POST['invoice'];
if($invoice_id == '') {
    echo '<h3>No invoice number (RS-.....) set. Go back to select flight package selection screen.</h3>';
    exit();
}

$b               = $_POST['cashier'];
$c               = $_POST['date'];
$d               = $_POST['ptype'];
$e               = $_POST['amount'];
$z               = $_POST['profit'];
$cname           = $_POST['cname'];
$customer_id     = $_POST['customerId'];
$salesType   = $_POST['salesType'];
$productName = $_POST['productName'];

$mode_of_payment = $_POST['mode_of_payment'];
$mode_of_payment_1 = $_POST['mode_of_payment_1'];
$total_cash = $_POST['total_cash'];

$cash = $_POST['cash'];
$remaining_cash = $_POST['remaining_cash'];

if (@$_POST['savingflight'] == 1) {
    $query = $db->prepare('SELECT SUM(discount) AS total_discount FROM flight_purchases WHERE invoice_id=?');
    $query->execute([$invoice_id]);
    $row      = $query->fetch();
    $discount = $row['total_discount'];

} else {
    $query = $db->prepare('SELECT SUM(discount) AS total_discount FROM sales_order WHERE invoice=?');
    $query->execute([$invoice_id]);
    $row      = $query->fetch();
    $discount = $row['total_discount'];
}

if ($salesType == '' || empty($salesType )) {
    # code...
    $salesType ='Service';
}

$monthNumber = date_parse_from_format("Y-m-d", $c);
$monthNum    = $monthNumber["month"];

$dateObj   = DateTime::createFromFormat('!m', $monthNum);
$monthName = $dateObj->format('M');

$specificyear = date_parse_from_format("Y-m-d", $c);
$salesyear    = $specificyear["year"];

if ($d == 'cash') {
    $f = $_POST['cash'] + $remaining_cash;
} else if ($d == 'credit') {
    $f = $_POST['due'];
}

if (@$_POST['savingflight'] == 1) {

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment, discount, customer_id, sale_type, mode_of_payment_1, mop_amount, mop1_amount, after_dis,
          expiry)
        VALUES 
        (:a,:b,:c,:d,:monh,:year,:e,:z,:due_date, :mode_of_payment, :discount, :customerId, :Service, :mode_of_payment_1, :mop_amount, :mop1_amount, :discountedValue,
          DATE(NOW() + INTERVAL 1 YEAR))";
    $q   = $db->prepare($sql);
    $q->execute(array(':a' => $invoice_id, ':b' => $b, ':c' => $c, ':d' => $d, ':monh' => $monthName, ':year' => $salesyear, ':e' => $e, ':z' => $z, ':due_date' => $f, ':mode_of_payment' => $mode_of_payment, ':discount' => $discount, ':customerId' => $customer_id, ':Service' => $salesType, ':mode_of_payment_1' => $mode_of_payment_1
        , ':mop_amount' => $cash, ':mop1_amount' => $remaining_cash, ':discountedValue' => $total_cash));

    // expire the token
    if(strlen($_POST['giveaway_token']) > 0) {

        $query = $db->prepare('SELECT id FROM approval_requests WHERE token = ?');
        $query->execute([$_POST['giveaway_token']]);
        $approval_request = $query->fetch();

        $query = $db->prepare('UPDATE approval_requests SET status = ? WHERE token = ?');
        $query->execute([GIVEAWAY_APPROVAL_USED, $_POST['giveaway_token']]);

        $query = $db->prepare('UPDATE sales SET approval_request_id = ? WHERE invoice_number = ? LIMIT 1');
        $query->execute([$approval_request['id'], $invoice_id]);
    }

    $query = $db->prepare("UPDATE flight_purchases SET status = 1 WHERE invoice_id = :invoiceId");
    $query->execute(array(
        ':invoiceId' => $invoice_id
    ));

    adjustBalanceForDeletedFlightBookings($invoice_id);

    $query = $db->prepare('SELECT * FROM customer WHERE customer_id = ?');
    $query->execute([$customer_id]);
    $customer = $query->fetch(PDO::FETCH_ASSOC);

    ob_start();
    include './partials/flight_preview.php';
    $receipt = ob_get_clean();

    $body = '<div>
            <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
            <p>Dear <b>'.$customer['customer_name'].'</b>,</p>
            <p>Greetings from Inflight Dubai</p>
            <p>Thank you very much for your transaction with Inflight Dubai. For your reference the receipt of your transaction <b>'.$invoice_id.'</b> is listed below:</p>
            <p>'.$receipt.'</p>
            <p>Inflight Dubai is one of the largest wind tunnel, where you can have indoor free fall experience, flying fun and indoor adventure thrill. Experience the adventure with your family and friends. For more details please visit www.inflightdubai.com. Also for any kind of assistance send email to info@inflightdubai.com</p>
            <p><b>Inflight Terms:</b></p>
                <ul>
                    <li>All purchases are non-refundable and not transferable.</li>
                    <li>All purchases are valid to fly within <b>1 year</b> from the date of purchase.</li>
                    <li>Inflight terms & conditions are applicable for each transaction.</li>
                </ul>
            </p>
            <br><br>
            <p>Regards, <br> Operation - Inflight Dubai</p>
        </div>';
    $email = (filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) ? $customer['email'] : $customer['address'];
    $response = sendEmail($email, 'Thanks for Flying with InflightDubai', $body, true);

    header("location: flight_preview.php?invoice=$invoice_id&payfirst=$cash&paysecond=$remaining_cash");

} else {

    $query = $db->prepare('SELECT DISTINCT(gen_name) FROM sales_order WHERE invoice = ?');
    $query->execute([$invoice_id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);
    $salesType = $row['gen_name'];

    $sql = "INSERT INTO sales (invoice_number,cashier,date,type,month,year,amount,profit,due_date, mode_of_payment, discount, customer_id, sale_type, mode_of_payment_1, mop_amount, mop1_amount, after_dis)
        VALUES (:a,:b,:c,:d,:monh,:year,:e,:z,:due_date, :mode_of_payment, :discount, :customerId, :Service, :mode_of_payment_1, :mop_amount, :mop1_amount, :discountedValue)";
    $q   = $db->prepare($sql);
    $q->execute(array(':a' => $invoice_id, ':b' => $b, ':c' => $c, ':d' => $d, ':monh' => $monthName, ':year' => $salesyear, ':e' => $e, ':z' => $z, ':due_date' => $f, ':mode_of_payment' => $mode_of_payment, ':discount' => $discount, ':customerId' => $customer_id, ':Service' => $salesType, ':mode_of_payment_1' => $mode_of_payment_1
        , ':mop_amount' => $cash, ':mop1_amount' => $remaining_cash, ':discountedValue' => $total_cash));



    header("location: preview.php?invoice=$invoice_id&payfirst=$cash&paysecond=$remaining_cash&sale_type=&d1=&d2=");
}


?>
