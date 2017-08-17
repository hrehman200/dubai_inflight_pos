<?php
include('../connect.php');

call_user_func($_POST['call']);

function getTimeslotsForFlightDate() {

    global $db;

    $duration_required            = $_POST['duration'];
    $show_slots_with_minutes_only = $_POST['show_slots_with_minutes_only'];
    $office_time_slots            = $_POST['office_time_slots'];

    $start = "00:00"/*date('i')>=30 ? date('H:30') : date('H:00')*/
    ;
    $end   = "23:30";

    if ($office_time_slots == 1) {
        $start = "09:30";
        $end   = "19:00";
    }

    $tStart = strtotime($start);
    $tEnd   = strtotime($end);
    $tNow   = $tStart;

    $slot_increment         = 30;
    $counter                = 0;
    $str                    = '';
    $previous_loop_duration = 0;

    while ($tNow <= $tEnd) {

        $query = $db->prepare("SELECT SUM(duration) AS bookedDuration FROM flight_bookings
              WHERE flight_time = :flight_time");
        $query->execute([
            'flight_time' => $_POST['flight_date'] . ' ' . date("H:i:00", $tNow),
        ]);

        $row = $query->fetch();

        // if someone wants to book 40 minutes, then select 30 minutes in this block and 10 minutes in next block
        if ($row['bookedDuration'] > 30) {
            $previous_loop_duration = $row['bookedDuration'] - 30;
            $row['bookedDuration']  = 30;

            // TODO: check here if next 10 minutes are available

        } else if ($previous_loop_duration > 0) {
            $row['bookedDuration']  = $previous_loop_duration;
            $previous_loop_duration = 0;
        }

        $unbooked_duration = 30 - $row['bookedDuration'];
        $percent_booked    = (int)floor($row['bookedDuration'] / 30 * 100);
        $percent_unbooked  = 100 - $percent_booked;

        if ($counter > 0 && $counter % 6 == 0) {
            $str .= '<br/><br/>';
        }

        if ($percent_unbooked >= 100) {
            $background = "#51a351";
        } else if ($percent_booked >= 100) {
            $background = "#ee5f5b";
        } else {
            $background = "linear-gradient(to left, #51a351 {$percent_unbooked}%, #ee5f5b {$percent_booked}%)";
        }

        if ($tNow <= strtotime("09:30") || $tNow >= strtotime("19:00")) {
            # code...
            $background = "linear-gradient(to left, #bfbfbf {$percent_unbooked}%, #ee5f5b {$percent_booked}%)";
        } 

        $tooltip_title = sprintf('Booked Time: %d <br> Time Remaining: %d', $row['bookedDuration'], 30 - $row['bookedDuration']);

        // nobody books 60 min slot and also we have only 30 min slots
        if ($duration_required > 30) {
            $duration_required = 30;
        }
        if ($show_slots_with_minutes_only == 1 && $unbooked_duration < $duration_required) {
            $tNow = strtotime("+{$slot_increment} minutes", $tNow);
            continue;
        }

        $str .= sprintf('<span class="label lb-lg" data-toggle="tooltip" title="%s" style="
            background: %s;
            margin:5px;
            padding:10px;
            color:white;">%s</span>', $tooltip_title, $background, date("H:i", $tNow));

        $tNow = strtotime("+{$slot_increment} minutes", $tNow);

        $counter++;
    }


    echo json_encode(array('success' => 1, 'msg' => '', 'data' => $str));
}

function saveCustomer() {
    global $db;

    $post = $_POST;

    $sql = "INSERT INTO customer
      (customer_name, address, gender, phone, email, password, nationality, resident_of, dob)
      VALUES
      (:customer_name, :address, :gender, :phone, :email, :password, :nationality, :resident_of, :dob)";

    $query = $db->prepare($sql);

    $query->execute(array(
        ':customer_name' => $post['customer_name'],
        ':address'       => $post['address'],
        ':gender'        => $post['gender'],
        ':phone'         => $post['phone'],
        ':email'         => $post['email'],
        ':password'      => $post['password'],
        ':nationality'   => $post['nationality'],
        ':resident_of'   => $post['resident_of'],
        ':dob'           => $post['dob']
    ));

    $customer_id = $db->lastInsertId();

    echo json_encode(array('success' => 1, 'msg' => '', 'data' => array('customer_id' => $customer_id, 'customer_name' => $post['customer_name'])));
}

function searchCustomers() {
    global $db;

    $post = $_POST;

    $query = $db->prepare('SELECT * FROM customer WHERE customer_name LIKE :search');
    $query->execute(array(
        ':search' => '%' . $post['search'] . '%'
    ));

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array(
        'success' => 1,
        'msg'     => '',
        'data'    => $result
    ));
}

function getCustomerOptions() {
    global $db;

    $query = $db->prepare('SELECT customer_id, customer_name FROM customer ORDER BY customer_name ASC');
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    $str = '';
    foreach($result as $row) {
        $str .= sprintf('<option value="%s">%s</option>', $row['customer_id'], $row['customer_name']);
    }

    echo json_encode(array(
        'success' => 1,
        'msg'     => '',
        'data'    => $str
    ));
}


function getDetailsForNewBookingModal() {
    global $db;
    $post              = $_POST;
    $unbooked_duration = 0;

    // if making another booking from same purchase
    if ($post['flightPurchaseId'] > 0) {
        $query = $db->prepare("SELECT * FROM flight_purchases fp
                    INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                    WHERE fp.id = :flightPurchaseId");
        $query->execute(array(
            ':flightPurchaseId' => $post['flightPurchaseId']
        ));
        $row            = $query->fetch();
        $total_duration = $row['duration'];

        $query = $db->prepare("SELECT SUM(duration) AS booked_duration FROM flight_bookings WHERE flight_purchase_id=:flightPurchaseId");
        $query->execute(array(
            ':flightPurchaseId' => $post['flightPurchaseId']
        ));
        $row               = $query->fetch();
        $booked_duration   = $row['booked_duration'];
        $unbooked_duration = $total_duration - $booked_duration;
    }

    // get balance only from paid invoices
    $query = $db->prepare("SELECT SUM(minutes) AS balance FROM flight_credits fc
                           INNER JOIN flight_purchases fp ON fc.flight_purchase_id = fp.id
                           WHERE fc.customer_id = :customerId
                           AND fp.status = 1
                           AND fp.flight_offer_id = :flightOfferId ");
    $query->execute(array(
        ':customerId'    => $post['customerId'],
        ':flightOfferId' => $post['flightOfferId']
    ));

    $row = $query->fetch();


    // get balance only from paid invoices
    $result = $db->prepare("SELECT * FROM customer WHERE customer_id = :customer_id");
    $result->execute(array('customer_id'=>$post['customerId']));
    $row12 = $result->fetch();

    $data = array(
        'unbooked_duration' => (int)$unbooked_duration,
        'balance'           => (int)$row['balance'],
        'credit_time'       => (int)$row12['credit_time'],
    );

    echo json_encode(array(
        'success' => 1,
        'msg'     => '',
        'data'    => $data
    ));
}

function getCustomerBookings() {
    global $db;

    $post = $_POST;

    // original Query

    // $query = $db->prepare("SELECT fo.offer_name, DATE_FORMAT(fp.created, '%D %M %Y') AS created, fo.duration, fc.flight_purchase_id, fc.minutes
    //     FROM flight_credits fc
    //     INNER JOIN flight_purchases fp ON fc.flight_purchase_id = fp.id
    //     INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
    //     WHERE fp.customer_id = :customerId AND fc.minutes > 0 AND fp.status = 1");

    // new query
    if($post['date'] != '') {
        $query = $db->prepare(" SELECT fo.offer_name, DATE_FORMAT(fp.created, '%D %M %Y')
                           AS created, fo.duration, fp.id AS flight_purchase_id, fc.minutes,
                           customer.customer_id, customer.customer_name, customer.credit_time, fo.id,
                           fb.id AS flight_booking_id, fb.flight_time
                           FROM flight_purchases fp
                           LEFT JOIN flight_credits fc ON fc.flight_purchase_id = fp.id
                           INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                           INNER JOIN customer ON customer.customer_id = fp.customer_id
                           INNER JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
                           WHERE fp.status = 1 AND date(fb.flight_time) = :flightDate");

        $query->execute(array(
            ':flightDate' => $post['date']
        ));

    } else {
        $query = $db->prepare(" SELECT fo.offer_name, DATE_FORMAT(fp.created, '%D %M %Y')
                           AS created, fo.duration, fp.id AS flight_purchase_id, fc.minutes,
                           customer.customer_id, customer.customer_name, customer.credit_time, fo.id,
                           fb.id AS flight_booking_id, fb.flight_time
                           FROM flight_purchases fp
                           LEFT JOIN flight_credits fc ON fc.flight_purchase_id = fp.id
                           INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id 
                           INNER JOIN customer ON customer.customer_id = fp.customer_id
                           LEFT JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
                           WHERE fp.customer_id =:customerId AND fp.status = 1");

        $query->execute(array(
            ':customerId' => $post['customerId']
        ));
    }

   // $query = $db->prepare("SELECT fp.id AS flight_purchase_id, fp.deduct_from_balance, fo.code, fpkg.package_name, fo.offer_name, fo.price, fo.duration, c.customer_name, DATE_FORMAT(fp.created,'%b %d, %Y') AS created
   //                            FROM flight_purchases fp
   //                            LEFT JOIN flight_offers fo ON fp.flight_offer_id = fo.id
   //                            LEFT JOIN flight_packages fpkg ON fo.package_id = fpkg.id
   //                            LEFT JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
   //                            INNER JOIN customer c ON fp.customer_id = c.customer_id
   //                            INNER JOIN flight_credits ON flight_credits.flight_purchase_id = fb.flight_purchase_id
   //                            WHERE fp.customer_id =:customerId AND flight_credits.minutes > 0 AND fp.status = 1");



    $table = '<table class="table table-striped table-bordered">
        <tr>
            <td>Customer</td>
            <td>Offer</td>
            <td>Purchaed  Date</td>
            <td>Flight Time</td>
            <td>Minutes</td>
            <td>Remaining</td>
            <td>Pre-Opening</td>
            <td>Action</td>
        </tr>';

    if ($query->rowCount() > 0) {
        while ($row = $query->fetch()) {
            $table .= sprintf('
            <tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%d</td>
                <td>%d '.($row['minutes']>0?'<a href="#" onclick="deductFromBalance(\''.$row['duration'].'\', \''.$row['minutes'].'\', \''.$row['id'].'\');" class="btn">Deduct from balance</a>':'').
                       ($row['minutes']>0?'<a href="#" onclick="showBalanceTransferDialog('.$row['customer_id'].','.$row['id'].','.$row['minutes'].','.$row['flight_purchase_id'].');" class="btn">Transfer Balance</a>':'')
                .'</td>
                <td>%d '.($row['credit_time']>0?'<a href="#" onclick="deductFromCreditTime('.$row['customer_id'].','.$row['credit_time'].','.$row['id'].','.$row['duration'].');" class="btn">Deduct from credit</a>':'').'</td>
                <td>
                    <a href="#" onclick="reschedule('.$row['flight_booking_id'].')" class="btn">Reschedule</a>
                </td>
            </tr>', $row['customer_name'], $row['offer_name'], $row['created'], $row['flight_time'], $row['duration'], $row['minutes'], $row['credit_time']);
        }
    } else {
        $table .= '<tr><td colspan="8">No previous bookings found</td></tr>';
    }
    $table .= '</table>';

    $data = array(
        'success' => 1,
        'msg'     => '',
        'data'    => $table
    );

    if($post['customerId'] > 0) {
        $query = $db->prepare(" SELECT credit_time FROM customer WHERE customer_id =:customerId");
        $query->execute(array(
            ':customerId' => $post['customerId']
        ));
        $row = $query->fetch();
        $data['credit_time'] = $row['credit_time'];
    }

    echo json_encode($data);
}

function verifyPassword() {
    if (sha1($_POST['password']) == '17874598808386e981a2bc4723c9bd38c5de4982') {
        $_SESSION['beyond_office_allowed'] = 1;
        echo json_encode(array('success' => 1));
    } else {
        echo json_encode(array('success' => 0));
    }
}

function getPONo() {
    global $db;

    $query = $db->prepare('SELECT DISTINCT po_no, po_amount FROM purchases WHERE po_no LIKE :search ORDER BY transaction_id DESC');
    $query->execute(array(
        ':search' => '%' . $_POST['search'] . '%'
    ));

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array(
        'success' => 1,
        'msg'     => '',
        'data'    => $result
    ));
}

function saveBusinessPlanRow() {
    global $db;

    $post = $_POST;

    $query = $db->prepare('SELECT * FROM business_plan_yearly
      WHERE business_plan_entity_id = :entityId
        AND month = :month
        AND year = :year');

    $form_data = array(
        ':entityId' => $post['entityId'],
        ':month'    => $post['month'],
        ':year'     => $post['year']
    );
    $query->execute($form_data);
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    $form_data[':value'] = $post['value'];

    if (count($result) > 0) {
        $sql   = "UPDATE business_plan_yearly SET value = :value
          WHERE business_plan_entity_id = :entityId
            AND month = :month
            AND year = :year";
        $query = $db->prepare($sql);
        $query->execute($form_data);
    } else {
        $sql   = "INSERT INTO business_plan_yearly VALUES (NULL, :entityId, :month, :year, :value)";
        $query = $db->prepare($sql);
        $query->execute($form_data);
    }

    echo json_encode(array(
        'success' => 1,
        'msg'     => '',
        'data'    => $result
    ));
}

function rescheduleFlightTime() {
    global $db;
    $post = $_POST;

    $sql   = "UPDATE flight_bookings SET flight_time = :flightTime
          WHERE id = :flightBookingId";
    $query = $db->prepare($sql);
    $query->execute(array(
        'flightTime' => $post['flight_time'],
        'flightBookingId' => $post['flight_booking_id']
    ));

    echo json_encode(array(
        'success' => 1,
        'msg'     => ''
    ));
}

function transferCredit() {
    global $db;
    $post = $_POST;

    $query = $db->prepare('SELECT credit_time FROM customer WHERE customer_id = :customerId');
    $query->execute(array(
       'customerId' => $post['customer_id']
    ));
    $row = $query->fetch();
    if($row['credit_time'] < $post['credit_to_transfer']) {
        echo json_encode(array(
           'success' => 0,
           'msg' => 'Selected customer does not have mentioned credit'
        ));
        return;
    }

    $sql   = "UPDATE customer SET credit_time = credit_time + :creditToTransfer
          WHERE customer_id = :toCustomerId";
    $query = $db->prepare($sql);
    $query->execute(array(
        'creditToTransfer' => $post['credit_to_transfer'],
        'toCustomerId' => $post['to_customer_id']
    ));

    $sql   = "UPDATE customer SET credit_time = credit_time - :creditToTransfer
          WHERE customer_id = :customerId";
    $query = $db->prepare($sql);
    $query->execute(array(
        'creditToTransfer' => $post['credit_to_transfer'],
        'customerId' => $post['customer_id']
    ));

    echo json_encode(array(
        'success' => 1,
        'msg'     => ''
    ));
}

function transferBalance() {
    global $db;
    $post = $_POST;

    transferBalanceFromCustomerAtoB($post['from_customer_id'], $post['to_customer_id'], $post['balance_to_transfer'], $post['flightOfferId'], $post['fromFlightPurchaseId']);

    echo json_encode(array(
        'success' => 1,
        'msg'     => ''
    ));
}