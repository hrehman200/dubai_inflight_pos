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
            $query = $db->prepare('UPDATE approval_requests SET status = ? WHERE token = ?');
            $query->execute([GIVEAWAY_APPROVAL_DISAPPROVED, $_GET['t']]);
            echo 'Approval denied';

            $query = $db->prepare('SELECT email FROM user WHERE id = ?');
            $query->execute([$row['made_by']]);
            $user = $query->fetch();

            $body = '<div>
                <img src="' . BASE_URL . 'main/img/inflight_logo.png" width="200" />
                <p>The approval request you made has been denied. </p>
            </div>';

            sendEmail($user['email'], 'Giveaway Approval Denied', $body);

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

