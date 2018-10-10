<?php
include('header.php');
?>

<div class="container-fluid">
    <div class="row-fluid">
        <h3>
        <?php
        $query = $db->prepare('SELECT * FROM approval_requests WHERE token = ? AND status = ?');
        $query->execute([$_GET['t'], GIVEAWAY_APPROVAL_PENDING]);
        if($query->rowCount() > 0) {
            $row = $query->fetch();

            $query = $db->prepare('UPDATE approval_requests SET status = ? WHERE token = ?');
            $query->execute([GIVEAWAY_APPROVAL_APPROVED, $_GET['t']]);
            echo 'Approval granted';

            $query = $db->prepare('SELECT email FROM user WHERE id = ?');
            $query->execute([$row['made_by']]);
            $user = $query->fetch();

            $link = sprintf('<a href="%smain/flight_picker.php?pkg_id=%d&invoice=%s&t=%s&flight_offer_id=%d&customer_id=%d">Proceed to Giveaway</a>',
                LOCAL_URL, 16, 'RS-' . createRandomPassword(), $row['token'], $row['flight_offer_id'], $row['customer_id']);
            $body = '<div>
                <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
                <p>The approval request you made has been granted. Please go to the following link for giveaways: </p>
                <p>' . $link .'</p>
            </div>';

            sendEmail($user['email'], 'Giveaway Approval Granted', $body);

        } else {
            echo 'Invalid request';
        }
        ?>
        </h3>
    </div>
</div>
</body>

<?php include('footer.php'); ?>
</html>

