<?php
include('../connect.php');

call_user_func($_POST['call']);

function getTimeslotsForFlightDate() {

    global $db;

    $duration_required = $_POST['duration'];

    $start = "00:00"/*date('i')>=30 ? date('H:30') : date('H:00')*/;
    $end   = "23:30";

    $tStart = strtotime($start);
    $tEnd   = strtotime($end);
    $tNow   = $tStart;

    $slot_increment = 30;
    $counter = 0;
    $str = '';
    $previous_loop_duration = 0;

    while ($tNow <= $tEnd) {

        $query = $db->prepare("SELECT SUM(duration) AS bookedDuration FROM flight_bookings
              WHERE flight_time = :flight_time");
        $query->execute([
            'flight_time' => $_POST['flight_date'].' '.date("H:i:00", $tNow),
        ]);

        $row = $query->fetch();

        // if someone wants to book 40 minutes, then select 30 minutes in this block and 10 minutes in next block
        if($row['bookedDuration'] > 30) {
            $previous_loop_duration = $row['bookedDuration'] - 30;
            $row['bookedDuration'] = 30;

            // TODO: check here if next 10 minutes are available

        } else if($previous_loop_duration > 0){
            $row['bookedDuration'] = $previous_loop_duration;
            $previous_loop_duration = 0;
        }

        $percent_booked = (int)floor($row['bookedDuration'] / 30 * 100);
        $percent_unbooked = 100 - $percent_booked;

        if ($counter % 6 == 0) {
            $str .= '<br/><br/>';
        }

        if($percent_unbooked >= 100) {
            $background = "#51a351";
        } else if($percent_booked >= 100) {
            $background = "#ee5f5b";
        }else {
            $background = "linear-gradient(to left, #51a351 {$percent_unbooked}%, #ee5f5b {$percent_booked}%)";
        }

        $tooltip_title = sprintf('Booked Time: %d <br> Time Remaining: %d', $row['bookedDuration'], 30 - $row['bookedDuration']);

        $str .= sprintf('<span class="label lb-lg" data-toggle="tooltip" title="%s" style="
            background: %s;
            margin:5px;
            padding:10px;
            color:white;">%s</span>', $tooltip_title, $background, date("H:i", $tNow));

        $tNow = strtotime("+{$slot_increment} minutes", $tNow);

        $counter++;
    }


    echo json_encode(array('success'=>1, 'msg'=>'', 'data'=>$str));
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
        ':customer_name'=>$post['customer_name'],
        ':address'=>$post['address'],
        ':gender'=>$post['gender'],
        ':phone'=>$post['phone'],
        ':email'=>$post['email'],
        ':password'=>$post['password'],
        ':nationality'=>$post['nationality'],
        ':resident_of'=>$post['resident_of'],
        ':dob'=>$post['dob']
    ));

    $customer_id = $db->lastInsertId();

    echo json_encode(array('success'=>1, 'msg'=>'', 'data'=>array('customer_id'=>$customer_id, 'customer_name'=>$post['customer_name'])));
}

function searchCustomers() {
    global $db;

    $post = $_POST;

    $query = $db->prepare('SELECT * FROM customer WHERE customer_name LIKE :search');
    $query->execute(array(
        ':search' => '%'.$post['search'].'%'
    ));

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array(
        'success'=>1,
        'msg'=>'',
        'data'=>$result
    ));
}

function getDetailsForNewBookingModal() {
    global $db;
    $post = $_POST;

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
    $booked_duration  = $row['booked_duration'];
    $unbooked_duration = $total_duration - $booked_duration;

    // get balance only from paid invoices
    $query = $db->prepare("SELECT SUM(minutes) AS balance FROM flight_credits fc
                           INNER JOIN flight_purchases fp ON fc.flight_purchase_id = fp.id
                           WHERE fc.customer_id=:customerId AND fp.status = 1");
    $query->execute(array(
        ':customerId' => $post['customerId']
    ));
    $row = $query->fetch();

    $data = array(
        'unbooked_duration' => (int)$unbooked_duration,
        'balance' => (int)$row['balance']
    );

    echo json_encode(array(
        'success'=>1,
        'msg'=>'',
        'data'=>$data
    ));
}

function getCustomerBookings() {
    global $db;

    $post = $_POST;

    $query = $db->prepare("SELECT fp.id AS flight_purchase_id, fo.code, fo.offer_name, fo.price, fo.duration FROM flight_purchases fp
                  INNER JOIN flight_offers fo ON fp.flight_offer_id = fo.id
                  WHERE fp.customer_id= :customerId AND fp.status = 1");
    $query->execute(array(
        ':customerId' => '%'.$post['customerId'].'%'
    ));

    $tbody = '';
    while($row = $query->fetch()) {

        $query2 = $db->prepare('SELECT * FROM flight_bookings WHERE flight_purchase_id = :flight_purchase_id');
        $query2->bindParam(':flight_purchase_id', $row['flight_purchase_id']);
        $query2->execute();
        while ($row2 = $query2->fetch()) {
            $tbody .= sprintf('
                <tr>
                    <td>%s</td>
                    <td>%d</td>
                </tr>', substr($row['flight_time'],0,-3), $row['duration']);

        }
    }



    echo json_encode(array(
        'success'=>1,
        'msg'=>'',
        'data'=>$a
    ));
}