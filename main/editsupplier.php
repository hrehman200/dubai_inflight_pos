<?php
include_once('../connect.php');
$id     = $_GET['id'];
$result = $db->prepare("SELECT * FROM supliers WHERE suplier_id= :userid");
$result->bindParam(':userid', $id);
$result->execute();
for ($i = 0; $row = $result->fetch(); $i++) {
    ?>
    <link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
    <form action="saveeditsupplier.php" method="post" enctype="multipart/form-data">
        <div style="text-align: center;"><h4><i class="icon-edit icon-large"></i> Edit Supplier</h4></div>
        <hr>
        <div id="ac">
            <input type="hidden" name="memi" value="<?php echo $id; ?>"/>
            <span>Supplier Name : </span><input type="text" style="width:265px; height:30px;" name="name"
                                                value="<?php echo $row['suplier_name']; ?>"/><br>
            <span>Address : </span><input type="text" style="width:265px; height:30px;" name="address"
                                          value="<?php echo $row['suplier_address']; ?>"/><br>
            <span>Contact Person : </span><input type="text" style="width:265px; height:30px;" name="cperson"
                                                 value="<?php echo $row['contact_person']; ?>"/><br>
            <span>Contact No.: </span><input type="text" style="width:265px; height:30px;" name="contact"
                                             value="<?php echo $row['suplier_contact']; ?>"/><br>
            <span>Email: </span><input type="text" style="width:265px; height:30px;" name="email"
                                             value="<?php echo $row['email']; ?>"/><br>
            <span>Category: </span><input type="text" style="width:265px; height:30px;" name="category"
                                       value="<?php echo $row['category']; ?>"/><br>
            <span>Payment Term: </span><input type="text" style="width:265px; height:30px;" name="payment_term"
                                       value="<?php echo $row['payment_term']; ?>"/><br>
            <span>Note : </span><textarea style="width:265px; height:80px;"
                                          name="note"><?php echo $row['note']; ?></textarea><br>
            <span>Attachment 1:<br> </span><input type="file" name="attachment_1" style="width:265px;height:30px;"/><br>
            <span>Attachment 2:<br> </span><input type="file" name="attachment_2" style="width:265px;height:30px;"/><br>
            <div style="float:right; margin-right:10px;">

                <button class="btn btn-success btn-block btn-large" style="width:267px;"><i
                        class="icon icon-save icon-large"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
    <?php
}
?>