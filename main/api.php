<?php
include_once('../connect.php');
include_once('include/security.php');
include_once('include/Mailin.php');

function getTimeslotsForFlightDate() {

    global $db;

    $duration_required = (int)$_POST['duration'];
    $show_slots_with_minutes_only = $_POST['show_slots_with_minutes_only'];
    $office_time_slots = $_POST['office_time_slots'];

    $start = "00:00"/*date('i')>=30 ? date('H:30') : date('H:00')*/;
    $end = "23:30";

    if ($office_time_slots == 1) {
        $start = "09:30";
        $end = "19:00";
    }

    $tStart = strtotime($start);
    $tEnd = strtotime($end);
    $tNow = $tStart;

    $slot_increment = 30;
    $counter = 0;
    $str = '';
    $previous_loop_duration = 0;

    $current_timestamp = strtotime(date("Y-m-d H:i:s"));

    while ($tNow <= $tEnd) {

        $slot_time = $_POST['flight_date'] . ' ' . date("H:i:00", $tNow);

        if(isset($_SESSION['CUSTOMER_ID'])) {
            $slot_timestamp = strtotime($slot_time);
            if ($current_timestamp > $slot_timestamp) {
                $tNow = strtotime("+{$slot_increment} minutes", $tNow);
                continue;
            }
        }

        $query = $db->prepare("SELECT SUM(duration) AS bookedDuration FROM flight_bookings
              WHERE flight_time = :flight_time");
        $query->execute([
            'flight_time' => $slot_time,
        ]);

        $row = $query->fetch();

        // if someone wants to book 40 minutes, then select 30 minutes in this block and 10 minutes in next block
        if ($row['bookedDuration'] > 30) {
            $previous_loop_duration = $row['bookedDuration'] - 30;
            $row['bookedDuration'] = 30;

            // TODO: check here if next 10 minutes are available

        } else if ($previous_loop_duration > 0) {
            $row['bookedDuration'] = $previous_loop_duration;
            $previous_loop_duration = 0;
        }

        $unbooked_duration = 30 - $row['bookedDuration'];
        $percent_booked = (int)floor($row['bookedDuration'] / 30 * 100);
        $percent_unbooked = 100 - $percent_booked;


        /*if ($percent_unbooked >= 100) {
            $background = "#51a351";
        } else if ($percent_booked >= 100) {
            $background = "#ee5f5b";
        } else {
            $background = "linear-gradient(to left, #51a351 {$percent_unbooked}%, #ee5f5b {$percent_booked}%)";
        }*/

        // nobody books 60 min slot and also we have only 30 min slots
        if ($duration_required > 30) {
            $duration_required = 30;
        }

        if ($tNow <= strtotime("09:30") || $tNow >= strtotime("19:00")) {
            //$background = "linear-gradient(to left, #bfbfbf {$percent_unbooked}%, #ee5f5b {$percent_booked}%)";

            $query = $db->prepare('SELECT * FROM flight_slots WHERE slot_time = :slotTime AND unlocked = 1');
            $query->execute(array(
                ':slotTime' => $slot_time
            ));
            if ($query->rowCount() > 0) {
                $unlocked = 1;
                if ($duration_required <= $unbooked_duration) {
                    $background = "#51a351";
                } else {
                    $background = "#ee5f5b";
                }
            } else {
                $unlocked = 0;
                $background = "#bfbfbf";
            }
        } else {
            // only show 1 color, no gradient
            if ($duration_required <= $unbooked_duration) {
                $background = "#51a351";
            } else {
                $background = "#ee5f5b";
            }
        }

        $tooltip_title = sprintf('Booked Time: %d <br> Time Remaining: %d', $row['bookedDuration'], 30 - $row['bookedDuration']);

        if ($show_slots_with_minutes_only == 1 && $duration_required > $unbooked_duration) {
            $tNow = strtotime("+{$slot_increment} minutes", $tNow);
            continue;
        }

        // don't show online customers beyond office timings
        if(isset($_SESSION['CUSTOMER_ID']) && ($tNow <= strtotime("09:30") || $tNow >= strtotime("19:00")) ) {
            $tNow = strtotime("+{$slot_increment} minutes", $tNow);
            continue;
        }

        $temp_str = sprintf('<span class="label lb-lg"
            data-remaining-minutes="%d"
            data-unlocked="%d"
            data-toggle="tooltip"
            title="%s"
            style="
            background: %s;
            margin:5px;
            padding:10px;
            color:white;">%s</span>', 30 - $row['bookedDuration'], $unlocked, $tooltip_title, $background, date("H:i", $tNow));

        $str .= $temp_str;

        $tNow = strtotime("+{$slot_increment} minutes", $tNow);

        $counter++;

        if ($counter > 0 && $counter % 6 == 0) {
            $str .= '<br/><br/>';
        }
    }


    echo json_encode(array('success' => 1, 'msg' => '', 'data' => $str));
}

function sendEmail($email, $subject, $body) {

    $mailin = new Mailin('https://api.sendinblue.com/v2.0', MAILIN_API_KEY);

    $data = array(
        "to" => array($email => "to whom!"),
        "bcc" => array("hrehman200@gmail.com" => "bcc whom!"),
        "from" => array("info@inflightdubai.com"),
        "subject" => $subject,
        "html" => $body,
        "headers" => array("Content-Type" => "text/html; charset=iso-8859-1")
    );

    $response = $mailin->send_email($data);
    return $response;
}

function validPhone($phone_no) {

    $isPhoneNum = false;

    //eliminate every char except 0-9
    $justNums = preg_replace("/[^0-9]/", '', $phone_no);

    //eliminate leading 0 if its there
    if (strlen($justNums) == 11) {
        $justNums = preg_replace("/^0/", '',$justNums);
    }

    if (strlen($justNums) >= 6 && strlen($justNums) <= 13) {
        $isPhoneNum = true;
    }

    return $isPhoneNum;
}

function saveCustomer() {

    global $db;

    $post = $_POST;

    foreach ($post as $key => $value) {
        if (empty($post[$key])) {
            echo json_encode(array('success' => 0, 'msg' => 'Please fill all fields'));
            return;
        }
    }

    if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('success' => 0, 'msg' => 'Please enter valid email'));
        return;
    }

    if (!validPhone($post['phone'])) {
        echo json_encode(array('success' => 0, 'msg' => 'Please enter valid phone number of 6 to 13 digits'));
        return;
    }

    $query = $db->prepare('SELECT customer_id FROM customer WHERE email = ?');
    $query->execute(array($post['email']));
    if (count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) {
        echo json_encode(array('success' => 0, 'msg' => 'The given email already exists in the system. Please login with that email'));
        return;
    }

    if(strlen($_FILES['customer_img']['name']) > 0) {
        $current_image = $_FILES['customer_img']['name'];
        $extension = substr(strrchr($current_image, '.'), 1);
        if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "gif") && ($extension != "png") && ($extension != "bmp")) {
            echo json_encode(array('success' => 0, 'msg' => 'Please upload image file'));
            return;
        }

        if (round(($_FILES["customer_img"]["size"]/1024)/1024, 0) > 2) {
            echo json_encode(array('success' => 0, 'msg' => "Image file size should not be greater than 2mb"));
            return;
        }

        $time = date("YmdHis-") . rand(1, 100);
        $new_image = $time . "." . $extension;
        $destination = "uploads/" . $new_image;
        $action = move_uploaded_file($_FILES['customer_img']['tmp_name'], $destination);
        if (!$action) {
            echo json_encode(array('success' => 0, 'msg' => 'Failed in uploading image'));
            return;
        }
    }

    $sql = "INSERT INTO customer
      (customer_name, address, gender, phone, email, password, nationality, resident_of, dob, 
      image, 
      activate_token)
      VALUES
      (:customer_name, :address, :gender, :phone, :email, :password, :nationality, :resident_of, :dob, 
      :image,
      :activate_token)";

    $query = $db->prepare($sql);

    $link_token = sha1(uniqid('t-'));
    $link = sprintf('<a href="%smain/activate.php?lt=%s">Activate</a>', BASE_URL, $link_token);

    $query->execute(array(
        ':customer_name' => $post['first_name'].' '.$post['last_name'],
        ':address' => $post['address'],
        ':gender' => $post['gender'],
        ':phone' => $post['phone'],
        ':email' => $post['email'],
        ':password' => sha1($post['password']),
        ':nationality' => $post['nationality'],
        ':resident_of' => $post['resident_of'],
        ':dob' => $post['dob-year'].'-'.$post['dob-month'].'-'.$post['dob-day'],
        ':image' => $new_image,
        ':activate_token' => $link_token
    ));

    $customer_id = $db->lastInsertId();

    $body = '<div>
        <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
        <p>Click on the following link to activate your account: </p>
        <p>' . $link . '</p>
    </div>';
    $response = sendEmail($post['email'], 'InflightDubai Account Activation', $body);

    echo json_encode(array(
        'success' => 1,
        'msg' => 'Thank you for registration, please check your email to activate your account.',
        'data' => array('customer_id' => $customer_id, 'customer_name' => $post['customer_name'], 'mail' => $response)
    ));
}

function saveProfile() {

    global $db;

    $post = $_POST;

    foreach ($post as $key => $value) {
        if(strpos($key, 'password') !== false) {
            continue;
        }

        if (empty($post[$key])) {
            echo json_encode(array('success' => 0, 'msg' => 'Please fill all fields'));
            return;
        }
    }

    if (!validPhone($post['phone'])) {
        echo json_encode(array('success' => 0, 'msg' => 'Please enter valid phone number of 6 to 13 digits'));
        return;
    }

    if(strlen($_FILES['customer_img']['name']) > 0) {
        $current_image = $_FILES['customer_img']['name'];
        $extension = substr(strrchr($current_image, '.'), 1);
        if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "gif") && ($extension != "png") && ($extension != "bmp")) {
            echo json_encode(array('success' => 0, 'msg' => 'Please upload image file'));
            return;
        }

        if (round(($_FILES["customer_img"]["size"]/1024)/1024, 0) > 2) {
            echo json_encode(array('success' => 0, 'msg' => "Image file size should not be greater than 2mb"));
            return;
        }


        $time = date("YmdHis-") . rand(1, 100);
        $new_image = $time . "." . $extension;
        $destination = "uploads/" . $new_image;
        $action = move_uploaded_file($_FILES['customer_img']['tmp_name'], $destination);
        if (!$action) {
            echo json_encode(array('success' => 0, 'msg' => 'Failed in uploading image'));
            return;
        }
    }

    if($post['new_password'] != '') {

        $query = $db->prepare('SELECT * FROM customer WHERE email = ? AND password = ? AND status = 1 LIMIT 1');
        $query->execute([
            $post['email'], sha1($post['password'])
        ]);

        if ($query->rowCount() > 0) {
            if (strlen($post['new_password']) >= 6 && $post['new_password'] == $post['confirm_password']) {
                $new_password = sha1($post['new_password']);
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Make sure new password contains atleast 6 characters && it matches confirm password'));
                return;
            }
        } else {
            echo json_encode(array('success' => 0, 'msg' => 'Invalid old password'));
            return;
        }
    }

    $sql = "UPDATE customer 
        SET
        customer_name=?, address=?, gender=?, phone=?, email=?, nationality=?, resident_of=?, dob=?";

    $arr_params = array(
        $post['first_name'].' '.$post['last_name'],
        $post['address'],
        $post['gender'],
        $post['phone'],
        $post['email'],
        $post['nationality'],
        $post['resident_of'],
        $post['dob-year'].'-'.$post['dob-month'].'-'.$post['dob-day']
    );

    if($new_image) {
        $sql .= ', image=?';
        $arr_params[] = $new_image;
    }

    if($new_password) {
        $sql .= ', password=?';
        $arr_params[] = $new_password;
    }

    $sql .= ' WHERE customer_id = ? LIMIT 1';
    $arr_params[] = $_SESSION['CUSTOMER_ID'];

    $query = $db->prepare($sql);
    $query->execute($arr_params);

    echo json_encode(array(
        'success' => 1,
        'msg' => 'Customer profile updated successfully.',
        'data' => array('')
    ));
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
        'msg' => '',
        'data' => $result
    ));
}

function getCustomerOptions() {
    global $db;

    $query = $db->prepare('SELECT customer_id, customer_name FROM customer ORDER BY customer_name ASC');
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    $str = '';
    foreach ($result as $row) {
        if ($row['customer_id'] == $_POST['customerId']) {
            continue;
        }
        $str .= sprintf('<option value="%s">%s</option>', $row['customer_id'], $row['customer_name']);
    }

    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $str
    ));
}


function getDetailsForNewBookingModal() {
    global $db;
    $post = $_POST;
    $unbooked_duration = 0;

    // if making another booking from same purchase
    if ($post['flightPurchaseId'] > 0) {
        $query = $db->prepare("SELECT * FROM flight_purchases fp
                    INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                    WHERE fp.id = :flightPurchaseId");
        $query->execute(array(
            ':flightPurchaseId' => $post['flightPurchaseId']
        ));
        $row = $query->fetch();
        $total_duration = $row['duration'];

        $query = $db->prepare("SELECT SUM(duration) AS booked_duration FROM flight_bookings WHERE flight_purchase_id=:flightPurchaseId");
        $query->execute(array(
            ':flightPurchaseId' => $post['flightPurchaseId']
        ));
        $row = $query->fetch();
        $booked_duration = $row['booked_duration'];
        $unbooked_duration = $total_duration - $booked_duration;
    }

    // get balance only from paid invoices
    $query = $db->prepare("SELECT SUM(minutes) AS balance FROM flight_credits fc
                           INNER JOIN flight_purchases fp ON fc.flight_purchase_id = fp.id
                           WHERE fc.customer_id = :customerId
                           AND fp.status = 1
                           AND fp.flight_offer_id = :flightOfferId ");
    $query->execute(array(
        ':customerId' => $post['customerId'],
        ':flightOfferId' => $post['flightOfferId']
    ));

    $row = $query->fetch();


    // get balance only from paid invoices
    $result = $db->prepare("SELECT * FROM customer WHERE customer_id = :customer_id");
    $result->execute(array('customer_id' => $post['customerId']));
    $row12 = $result->fetch();

    $data = array(
        'unbooked_duration' => (int)$unbooked_duration,
        'balance' => (int)$row['balance'],
        'credit_time' => (int)$row12['credit_time'],
    );

    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $data
    ));
}

function getCustomerBookings() {
    global $db;

    $post = $_POST;

    // new query
    if ($post['date'] != '') {

        $sql = "SELECT fo.offer_name, customer.customer_name, fb.flight_time, fb.duration AS booking_duration
                           FROM flight_purchases fp
                           INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                           INNER JOIN customer ON customer.customer_id = fp.customer_id
                           INNER JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
                           WHERE fp.status = 1 AND date(fb.flight_time) = :flightDate";

        $arr_params = array(
            ':flightDate' => $post['date']
        );

        if (isset($_SESSION['CUSTOMER_ID'])) {
            $sql .= " AND fp.customer_id = :customer_id";
            $arr_params[':customer_id'] = $_SESSION['CUSTOMER_ID'];
        }

        $query = $db->prepare($sql);
        $query->execute($arr_params);

        $table = '<table class="table table-striped table-bordered">
        <tr>
            <th>Customer</th>
            <th>Offer</th>
            <th>Flight Time</th>
            <th>Minutes</th>
        </tr>';

        if ($query->rowCount() > 0) {
            while ($row = $query->fetch()) {

                $table .= sprintf('
                    <tr>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                    </tr>', $row['customer_name'], $row['offer_name'], $row['flight_time'], $row['booking_duration']);
            }

        } else {
            $table .= '<tr><td colspan="4">No bookings found</td></tr>';
        }
        $table .= '</table>';
    }

    if ($post['customerId'] != '') {

        $query2 = $db->prepare(" SELECT fo.offer_name, 
                           DATE_FORMAT(fp.created, '%D %M %Y') AS created, 
                           fo.duration, 
                           fp.id AS flight_purchase_id, 
                           fc.minutes,
                           customer.customer_id, customer.customer_name, customer.credit_time, 
                           fo.id,
                           fb.id AS flight_booking_id, fb.flight_time, fb.duration AS booking_duration, fp.deduct_from_balance
                           FROM flight_purchases fp
                           LEFT JOIN flight_credits fc ON fc.flight_purchase_id = fp.id
                           INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                           INNER JOIN customer ON customer.customer_id = fp.customer_id
                           LEFT JOIN flight_bookings fb ON fb.flight_purchase_id = fp.id
                           WHERE fp.customer_id =:customerId 
                            AND fp.status = 1 
                            AND (fb.id IS NOT NULL OR fc.minutes > 0)
                            AND (fc.minutes > 0 OR fb.flight_time >= NOW())");

        $query2->execute(array(
            ':customerId' => $post['customerId']
        ));

        $table2 = '<table class="table table-striped table-bordered">
        <tr>
            <th>Customer</th>
            <th>Offer</th>
            <th>Purchaed  Date</th>
            <th>Flight Time</th>
            <th>Minutes</th>
            <th>Remaining</th>
            <th>Pre-Opening</th>
            <th>Action</th>
        </tr>';
    }

    if ($post['customerId'] != '') {

        if ($query2->rowCount() > 0) {
            while ($row = $query2->fetch()) {

                $table2 .= sprintf('
                    <tr>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%d</td>
                        <td>%d <br/>' . ($row['minutes'] > 0 ? '<a href="javascript:;" onclick="deductFromBalance(' . $row['duration'] . ',' . $row['minutes'] . ',' . $row['id'] . ',' . $row['flight_purchase_id'] . ');" class="btn btn-small">Deduct</a>' : '') .
                    ($row['minutes'] > 0 ? '<a href="javascript:;" onclick="showBalanceTransferDialog(' . $row['customer_id'] . ',' . $row['id'] . ',' . $row['minutes'] . ',' . $row['flight_purchase_id'] . ');" class="btn btn-small btn-transfer">Transfer</a>' : '')
                    . '</td>
                        <td>%d <br/>' . ($row['credit_time'] > 0 ? '<a href="javascript:;" onclick="deductFromCreditTime(' . $row['customer_id'] . ',' . $row['credit_time'] . ',' . $row['id'] . ',' . $row['duration'] . ');" class="btn btn-small">Deduct</a>' : '') .
                    ($row['credit_time'] > 0 ? '<a href="javascript:;" class="btn btn-small btnTransferCredit">Transfer</a>' : '')
                    . '</td>
                        <td>
                            <a href="javascript:;" onclick="reschedule(' . $row['flight_booking_id'] . ')" class="btn btn-small btn-reschedule">Reschedule</a>
                            <a href="javascript:;" onclick="cancelFlight(\'' . $row['flight_booking_id'] . '\', this)" class="btn btn-small btn-cancel">Cancel</a>
                        </td>
                    </tr>', $row['customer_name'], $row['offer_name'], $row['created'], $row['flight_time'],
                    ($row['deduct_from_balance'] > 0) ? $row['booking_duration'] : $row['duration'],
                    $row['minutes'], $row['credit_time']);
            }
        }
    } else {
        $table2 .= '<tr><td colspan="8">No previous bookings found</td></tr>';
    }
    $table2 .= '</table>';

    $data = array(
        'success' => 1,
        'msg' => '',
        'data' => array('table' => $table, 'table2' => $table2)
    );

    if ($post['date'] != '') {
        $data['bookings'] = $query->rowCount();
    }

    if ($post['customerId'] > 0) {
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
    global $db;

    if (sha1($_POST['password']) == '17874598808386e981a2bc4723c9bd38c5de4982') {

        $sql = "INSERT INTO flight_slots VALUES (:slotTime, 1)";
        $query = $db->prepare($sql);
        $query->execute(array(
            'slotTime' => $_POST['slotTime']
        ));
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
        'msg' => '',
        'data' => $result
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
        ':month' => $post['month'],
        ':year' => $post['year']
    );
    $query->execute($form_data);
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    $form_data[':value'] = $post['value'];

    if (count($result) > 0) {
        $sql = "UPDATE business_plan_yearly SET value = :value
          WHERE business_plan_entity_id = :entityId
            AND month = :month
            AND year = :year";
        $query = $db->prepare($sql);
        $query->execute($form_data);
    } else {
        $sql = "INSERT INTO business_plan_yearly VALUES (NULL, :entityId, :month, :year, :value)";
        $query = $db->prepare($sql);
        $query->execute($form_data);
    }

    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $result
    ));
}

function rescheduleFlightTime() {
    global $db;
    $post = $_POST;

    $sql = "UPDATE flight_bookings SET flight_time = :flightTime
          WHERE id = :flightBookingId";
    $query = $db->prepare($sql);
    $query->execute(array(
        'flightTime' => $post['flight_time'],
        'flightBookingId' => $post['flight_booking_id']
    ));

    echo json_encode(array(
        'success' => 1,
        'msg' => ''
    ));
}

function cancelFlight() {
    global $db;

    deleteFlightBooking($_POST['flight_booking_id']);

    echo json_encode(array(
        'success' => 1,
        'msg' => ''
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
    if ($row['credit_time'] < $post['credit_to_transfer']) {
        echo json_encode(array(
            'success' => 0,
            'msg' => 'Selected customer does not have mentioned credit'
        ));
        return;
    }

    $sql = "UPDATE customer SET credit_time = credit_time + :creditToTransfer
          WHERE customer_id = :toCustomerId";
    $query = $db->prepare($sql);
    $query->execute(array(
        'creditToTransfer' => $post['credit_to_transfer'],
        'toCustomerId' => $post['to_customer_id']
    ));

    $sql = "UPDATE customer SET credit_time = credit_time - :creditToTransfer
          WHERE customer_id = :customerId";
    $query = $db->prepare($sql);
    $query->execute(array(
        'creditToTransfer' => $post['credit_to_transfer'],
        'customerId' => $post['customer_id']
    ));

    echo json_encode(array(
        'success' => 1,
        'msg' => ''
    ));
}

function transferBalance() {
    global $db;
    $post = $_POST;

    transferBalanceFromCustomerAtoB($post['from_customer_id'], $post['to_customer_id'], $post['balance_to_transfer'], $post['flightOfferId'], $post['fromFlightPurchaseId']);

    echo json_encode(array(
        'success' => 1,
        'msg' => ''
    ));
}

function getProductSubCategories() {
    global $db;

    $post = $_POST;

    $query = $db->prepare('SELECT * FROM product_categories WHERE parent_id = :parentId');
    $query->execute(array(
        ':parentId' => $post['parentId']
    ));

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $result
    ));
}

function getProducts() {
    global $db;
    $result = $db->prepare("SELECT common_name FROM products WHERE category_id = :categoryId GROUP BY common_name");
    $result->bindParam(':categoryId', $_POST['categoryId']);
    $result->execute();

    $arr = array();
    while ($row = $result->fetch()) {
        $arr[] = array(
            'name' => $row['common_name']
        );
    }

    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $arr
    ));
}

function getGenders() {
    global $db;
    $result2 = $db->prepare("SELECT gender, product_id
          FROM products
          WHERE common_name = :commonName
          GROUP BY gender");
    $result2->bindParam(':commonName', $_POST['commonName']);
    $result2->execute();
    $genders = $result2->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $genders
    ));
}

function getSizes() {
    global $db;
    $result2 = $db->prepare("SELECT size, product_id
          FROM products
          WHERE common_name = :commonName AND gender = :gender
          GROUP BY size
          ORDER BY size ASC");
    $result2->execute(array(
        ':commonName' => $_POST['commonName'],
        ':gender' => $_POST['gender']
    ));
    $sizes = $result2->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $sizes
    ));
}

function getColors() {
    global $db;
    $result2 = $db->prepare("SELECT Attribute, product_id, image, qty
          FROM products
          WHERE common_name = :commonName AND gender = :gender AND size = :size
          GROUP BY Attribute");
    $result2->execute(array(
        ':commonName' => $_POST['commonName'],
        ':gender' => $_POST['gender'],
        ':size' => $_POST['size']
    ));
    $colors = $result2->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $colors
    ));
}

function getProductId() {
    global $db;
    $result = $db->prepare("SELECT product_id FROM products WHERE common_name = :commonName
      AND size = :size
      AND Attribute = :color
      AND gender = :gender");
    $result->execute(array(
        ':commonName' => $_POST['commonName'],
        ':size' => $_POST['size'],
        ':color' => $_POST['color'],
        ':gender' => $_POST['gender'],
    ));

    if ($result->rowCount() > 0) {
        $row = $result->fetch();
        echo json_encode(array(
            'success' => 1,
            'msg' => '',
            'data' => $row['product_id']
        ));
    } else {
        echo json_encode(array(
            'success' => 0,
            'msg' => '',
            'data' => null
        ));
    }
}

function getVatForDiscountedAmountAndInvoice() {
    $arr_vat = getVatDetailsForDiscountedAmountAndInvoice($_POST['discounted_amount'], $_POST['invoice'], $_POST['saving_flight']);
    echo json_encode(array(
        'success' => 1,
        'data' => $arr_vat
    ));
}

function saveDiscount() {

    global $db;

    if (@$_POST['saving_flight'] == 1) {
        $query = $db->prepare("UPDATE flight_purchases SET discount = ?, discount_id = ?
          WHERE id = ?");

    } else {
        $query = $db->prepare("UPDATE sales_order SET discount = ?, discount_id = ?
          WHERE transaction_id = ?");
    }
    $query->execute([$_POST['discount'], $_POST['discount_id'], $_POST['transaction_id']]);

    echo json_encode(array(
        'success' => 1,
        'msg' => ''
    ));
}

function loginCustomer() {
    global $db;

    $query = $db->prepare('SELECT * FROM customer WHERE email = ? AND password = ? AND status = 1 LIMIT 1');
    $query->execute([
        $_POST['email'], sha1($_POST['pass'])
    ]);

    if ($query->rowCount() > 0) {

        $row = $query->fetch();

        session_destroy();
        session_start();
        $_SESSION['CUSTOMER_FIRST_NAME'] = $row['customer_name'];
        $_SESSION['CUSTOMER_ID'] = $row['customer_id'];

        echo json_encode(array(
            'success' => 1,
            'msg' => ''
        ));

    } else {
        echo json_encode(array(
            'success' => 0,
            'msg' => 'Invalid credentials. Please try again.'
        ));
    }
}

function logoutCustomer() {
    global $db;

    session_destroy();
    unset($_GET);

    echo json_encode(array(
        'success' => 1
    ));
}

function sendPassReset() {
    global $db;

    $query = $db->prepare('SELECT customer_id FROM customer WHERE email = ? LIMIT 1');
    $query->execute([
        $_POST['email']
    ]);

    if ($query->rowCount() > 0) {
        $row = $query->fetch();

        $token = uniqid('fpt-');
        $hashed_token = sha1($token);

        $query = $db->prepare('UPDATE customer SET forgot_pass_token = ? WHERE customer_id = ?');
        $query->execute(array($hashed_token, $row['customer_id']));

        $link = sprintf('<a href="%smain/forgotpass.php?fpt=%s">Reset Password</a>', BASE_URL, $token);
        $body = '<div>
            <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
            <p>Click on the following link to reset your password: </p>
            <p>' . $link . '</p>
        </div>';
        $response = sendEmail($_POST['email'], 'Password Reset Instructions', $body);

        echo json_encode(array(
            'success' => 1,
            'msg' => 'Password reset instruction sent to your email.',
            'data' => $response
        ));

    } else {
        echo json_encode(array(
            'success' => 0,
            'msg' => 'No record found of given email. Please register first.'
        ));
    }
}

function getSignature() {

    $params = [];
    foreach ($_REQUEST['data'] as $value) {
        $params[$value['name']] = $value['value'];
    }

    $signature = sign($params);
    echo json_encode(array('success' => 1, 'data' => $signature));
}

function getFlightOffers() {
    global $db;

    if ($_POST['packageId'] > 0) {
        $result = $db->prepare("SELECT * FROM flight_offers WHERE package_id = :package_id AND status = 1
        AND offer_name NOT LIKE '%Upsale%'");
        $result->execute(array('package_id' => $_POST['packageId']));

        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $rows = [];
    }

    echo json_encode(array(
        'success' => 1,
        'data' => $rows
    ));
}

call_user_func($_POST['call']);
