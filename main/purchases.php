<?php
require_once '../connect.php';

$id     = (int)$_GET['id'];
$result = $db->prepare("SELECT * FROM purchases WHERE transaction_id = :id");
$result->execute(array(
    ':id' => $id
));
if ($result->rowCount() > 0) {
    $row = $result->fetch();
}
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
<form action="savepur.php" method="post" enctype="multipart/form-data">
    <div style="text-align: center;"><h4><i class="icon-plus-sign icon-large"></i> <?= ($id > 0) ? 'Edit' : 'Add' ?>
            Purchase</h4></div>
    <hr>
    <div style="text-align:left;">
        <div id="ac">
            <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>"
                   style="width:265px;height:30px;"/>
            <span>Date: <br></span><input type="date" name="date"
                                          placeholder="MM/DD/YYYY" value="<?= $row['date'] ?>"
                                          style="width:265px;height:30px;"/><br>
            <span>Invoice Number: </span><input type="text" name="iv" value="<?= $row['invoice_number'] ?>"
                                                style="width:265px;height:30px;"/><br>
            <span>Supplier : </span>
            <select name="supplier" style="width:265px; height:30px;">
                <?php
                include_once('../connect.php');
                $result = $db->prepare("SELECT * FROM supliers");
                $result->bindParam(':userid', $res);
                $result->execute();
                for ($i = 0; $row2 = $result->fetch(); $i++) {
                    ?>
                    <option <?= ($row['suplier'] == $row2['suplier_name']) ? 'selected' : '' ?> >
                        <?php echo $row2['suplier_name']; ?>
                    </option>
                    <?php
                }
                ?>
            </select><br>
            <span>Invoice Amount:<br> </span><input type="text" name="invoice_amount"
                                                    value="<?= $row['invoice_amount'] ?>"
                                                    style="width:265px;height:30px;"/>

            <input type="hidden" name="prev_invoice_amount" value="<?=$row['invoice_amount']?>" />
            <input type="hidden" name="prev_po_amount" value="<?=$row['po_amount']?>" />

            <br>
            <span>PO Number:<br> </span><input type="text" id="po_no" name="po_no" value="<?=$row['po_no']?>"
                                               style="width:265px;height:30px;" autocomplete="off"/><br>
            <span>PO Amount:<br> </span><input type="text" id="po_amount" name="po_amount"
                                               value="<?= $row['po_amount'] ?>" style="width:265px;height:30px;"
                                               autocomplete="off"/><br>
            <span>Attachment 1:<br> </span><input type="file" name="attachment_1" style="width:265px;height:30px;"/><br>
            <span>Attachment 2:<br> </span><input type="file" name="attachment_2" style="width:265px;height:30px;"/><br>
            <span>Attachment 3:<br> </span><input type="file" name="attachment_3" style="width:265px;height:30px;"/><br>
            <span>Remarks:<br> </span><input type="text" name="remarks" value="<?= $row['remarks'] ?>"
                                             style="width:265px;height:30px;"/><br>
            <span>&nbsp;</span><input type="submit" value="Save" class="btn btn-info"/>
        </div>
    </div>
</form>

<script src="js/bootstrap-typeahead.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {

        $("#po_no").typeahead({
            onSelect: function (item) {
                if (item) {
                    $('#po_no').val(item.text);
                    $('#po_amount').val(item.value);
                }
            },
            ajax: {
                url: "api.php",
                timeout: 500,
                valueField: "po_amount",
                displayField: "po_no",
                triggerLength: 1,
                method: "post",
                preDispatch: function (query) {
                    return {
                        search: query,
                        call: 'getPONo',
                    }
                },
                preProcess: function (response) {
                    if (response.success == false) {
                        return false;
                    }
                    return response.data;
                }
            }
        });

    });
</script>