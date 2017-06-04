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

    while ($tNow <= $tEnd) {

        $query = $db->prepare("SELECT SUM(flight_duration) AS bookedDuration FROM sales_order
              WHERE flight_date = :flight_date AND flight_time = :flight_time");
        $query->execute([
            'flight_date' => $_POST['flight_date'],
            'flight_time' => date("H:i", $tNow),
        ]);

        $row = $query->fetch();

        $percent_booked = (int)floor($row['bookedDuration'] / $duration_required * 100);
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

        $str .= sprintf('<span class="label lb-lg" style="
            background: %s;
            margin:10px;
            padding:10px;
            color:white;">%s</span>', $background, date("H:i", $tNow));

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