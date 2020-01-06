<?php
include_once(dirname(dirname(__DIR__)).'/connect.php');

function isValidToken() {
    global $db;

    $query = $db->prepare('SELECT * FROM customer WHERE api_token = ? AND api_token_expiry > NOW() AND status = 1 LIMIT 1');
    $query->execute([$_POST['token']]);
    return ($query->rowCount() > 0);
}

function loginCustomer() {
    global $db;

    $query = $db->prepare('SELECT * FROM customer WHERE email = ? AND password = ? AND status = 1 LIMIT 1');
    $query->execute([
        $_POST['email'], sha1($_POST['pass'])
    ]);

    if ($query->rowCount() > 0) {

        $row = $query->fetch();

        $query = $db->prepare('UPDATE customer SET api_token = ?, api_token_expiry = DATE(NOW() + INTERVAL 3 MONTH) WHERE customer_id = ?');
        $token = createRandomPassword('api-', 50);
        $query->execute([$token, $row['customer_id']]);

        echo json_encode(array(
            'success' => 1,
            'msg' => '',
            'data' => [
                'customer_id' => $row['customer_id'],
                'first_name' => $row['first_name'] ? $row['first_name'] : '',
                'last_name' => $row['last_name'] ? $row['last_name'] : '',
                'token' => $token
            ]
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

function getPackagesAndOffers() {
    global $db;

    $arr = [];

    $result = $db->prepare("SELECT * FROM flight_packages WHERE id IN (1,3)");
    $result->execute();
    for ($i = 0; $row = $result->fetch(PDO::FETCH_ASSOC); $i++) {

        $package = [
            'package_id' => $row['id'],
            'package_name' => $row['package_name']
        ];

        $offers = [];
        $result2 = $db->prepare("SELECT * FROM flight_offers WHERE package_id = :package_id AND status = 1 
                            AND offer_name NOT LIKE '%Upsale%'");
        $result2->execute(array('package_id' => $row['id']));
        while($row2 = $result2->fetch(PDO::FETCH_ASSOC)) {
            $package['offers'][] = $row2;
        }

        $arr[] = $package;
    }

    echo json_encode(array(
        'success' => 1,
        'msg' => '',
        'data' => $arr
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

function getTimeslotsForFlightDate() {

    global $db;

    $duration_required = (int)$_POST['duration'];
    $show_slots_with_minutes_only = $_POST['show_slots_with_minutes_only'];
    $office_time_slots = $_POST['office_time_slots'];

    $start = "00:00"/*date('i')>=30 ? date('H:30') : date('H:00')*/;
    $end = "23:30";

    if ($office_time_slots == 1) {
        $start = "10:00";
        $end = "18:30";

    } else if(strpos($_SERVER["HTTP_REFERER"], BASE_URL) !== false) {
        $start = "11:00";
        $end = "18:30";
    }

    $tStart = strtotime($start);
    $tEnd = strtotime($end);
    $tNow = $tStart;

    $slot_increment = 30;
    $str = '';
    $previous_loop_duration = 0;

    $current_timestamp = strtotime(date("Y-m-d H:i:s"));

    $slots = [];

    while ($tNow <= $tEnd) {

        $slot_time = $_POST['flight_date'] . ' ' . date("H:i:00", $tNow);

        if(strpos(BASE_URL, $_SERVER['HTTP_HOST']) !== false) {
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
        if($tNow <= strtotime("09:30") || $tNow >= strtotime("19:00") ) {
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

        $slots[] = date("H:i", $tNow);

        $tNow = strtotime("+{$slot_increment} minutes", $tNow);
    }


    echo json_encode(array('success' => 1, 'msg' => '', 'data' => $slots));
}

function addToCart() {
    global $db;

    $invoice            = $_POST['invoice'];
    if(!strlen($invoice)) {
        $invoice = 'RS-'.createRandomPassword();
    }
    $flight_offer_id    = $_POST['flightOffer'];
    $flight_time        = $_POST['flightDate'] . " " . $_POST['flightTime'] . ":00";
    $offer_duration     = $_POST['offerDuration'];
    $flight_duration    = $_POST['flightDuration'];
    $flight_purchase_id = $_POST['flightPurchaseId'];
    $customer_id        = $_POST['customerId'];
    $class_people            = isset($_POST['txtClassPeople']) ? $_POST['txtClassPeople'] : 0;

    if ($flight_purchase_id > 0) {
        insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);

    } else {
        $flight_purchase_id = insertFlightPurchase($invoice, $flight_offer_id, $customer_id, 0, 0, $class_people);
        insertFlightBooking($flight_purchase_id, $flight_time, $flight_duration);
    }

    $query = $db->prepare("SELECT SUM(duration) AS booked_duration FROM flight_bookings WHERE flight_purchase_id=:flightPurchaseId");
    $query->execute(array(
        ':flightPurchaseId' => $flight_purchase_id
    ));
    $row             = $query->fetch();
    $booked_duration = $row['booked_duration'];

    $minutes = $offer_duration - $booked_duration;

    if($minutes == 0) {
        updateCustomerFlightBalance($customer_id, $flight_purchase_id, $minutes, false, true);
    } else {
        updateCustomerFlightBalance($customer_id, $flight_purchase_id, $minutes);
    }

    echo json_encode([
        'success' => 1,
        'msg' => 'Booking added',
        'data' => [
            'invoice' => $invoice
        ]
    ]);
}

function getBookingsForInvoice() {
    global $db;
    $result = $db->prepare("SELECT fp.id AS flight_purchase_id, fp.deduct_from_balance, fp.class_people, fp.discount, fp.discount_id, vc.percent,
                      fo.code, fpkg.package_name, fpkg.id AS package_id, fo.offer_name, fp.price, fo.duration, fp.flight_offer_id,
                       fb.flight_time
                      FROM flight_purchases fp
                      INNER JOIN flight_bookings fb ON fp.id = fb.flight_purchase_id
                      LEFT JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                      LEFT JOIN flight_packages fpkg ON fo.package_id = fpkg.id
                      LEFT JOIN vat_codes vc ON fp.vat_code_id = vc.id
                      LEFT JOIN discounts d ON fp.discount_id = d.id
                      WHERE fp.invoice_id= :invoiceId");
    $result->bindParam(':invoiceId', $_POST['invoice']);
    $result->execute();

    $cart_items = [];
    $total = 0;
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $row['vat_amount'] = round($row['percent'] * $row['price'] / 105, 1);
        $cart_items[] = $row;

        $row['price'] = (int) $row['price'];
        $total += $row['price'];
    }

    echo json_encode([
        'success' => 1,
        'msg' => '',
        'data' => [
            'items' => $cart_items,
            'total' => $total
        ]
    ]);
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
      activate_token,
      note)
      VALUES
      (:customer_name, :address, :gender, :phone, :email, :password, :nationality, :resident_of, :dob, 
      :image,
      :activate_token,
      :note)";

    $query = $db->prepare($sql);

    $link_token = sha1(uniqid('t-'));
    $link = sprintf('<a href="%smain/activate.php?lt=%s&invoice=%s&p=%s">Activate</a>', BASE_URL, $link_token, $post['invoice'], $post['p']);

    $query->execute(array(
        ':customer_name' => ($post['pos']==1) ? $post['customer_name'] : $post['first_name'].' '.$post['last_name'],
        ':address' => ($post['pos']==1) ? $post['email'] : ($post['address'] ? $post['address'] : ''),
        ':gender' => $post['gender'],
        ':phone' => $post['phone'],
        ':email' => $post['email'],
        ':password' => sha1($post['password']),
        ':nationality' => ($post['nationality'] ? $post['nationality'] : ''),
        ':resident_of' => ($post['resident_of'] ? $post['resident_of'] : ''),
        ':dob' => $post['dob-year'].'-'.$post['dob-month'].'-'.$post['dob-day'],
        ':image' => ($new_image ? $new_image : ''),
        ':activate_token' => $link_token,
        ':note' => $post['note'] ? $post['note'] : ''
    ));

    $customer_id = $db->lastInsertId();

    if($post['pos'] != 1) {
        $body = '<div>
            <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
            <p>Click on the following link to activate your account: </p>
            <p>' . $link . '</p>
        </div>';
        $response = sendEmail($post['email'], 'InflightDubai Account Activation', $body);
    }

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

        if($_POST['bookingCustomerId'] > 0) {
            $sql .= sprintf(' AND fp.customer_id = %d', $_POST['bookingCustomerId']);
        }

        $arr_params = array(
            ':flightDate' => $post['date']
        );

        if (isset($_SESSION['CUSTOMER_ID'])) {
            $sql .= " AND fp.customer_id = :customer_id";
            $arr_params[':customer_id'] = $_SESSION['CUSTOMER_ID'];
        }

        $sql .= " ORDER BY fb.flight_time ASC";

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
                            AND (fc.minutes > 0 OR fb.flight_time >= NOW())
                            AND fc.expired_on IS NULL");

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

                $cancel_html = '<a href="javascript:;" onclick="reschedule(' . $row['flight_booking_id'] . ')" class="btn btn-small btn-reschedule">Reschedule</a>
                            <a href="javascript:;" onclick="cancelFlight(\'' . $row['flight_booking_id'] . '\', this)" class="btn btn-small btn-cancel">Cancel</a>';

                if(!is_null($row['flight_time']) && strtotime($row['flight_time']) < time()) {
                    $cancel_html = '';
                }

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
                            '.$cancel_html.'
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

function deleteFromCart() {
    if ($_POST['flightPurchaseId'] > 0) {
        deleteFlightPurchase($_POST['flightPurchaseId']);
    }

    getBookingsForInvoice();
}

if(!isset($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

$public_methods = ['loginCustomer', 'saveCustomer', 'sendPassReset'];

if(!in_array($_POST['call'], $public_methods)) {
    if (isValidToken()) {
        call_user_func($_POST['call']);
    } else {
        echo json_encode(['success' => 0, 'msg' => 'Invalid token. Please login again.']);
    }
} else {
    call_user_func($_POST['call']);
}