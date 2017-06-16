<link href="../style.css" media="screen" rel="stylesheet" type="text/css"/>
<form action="savepur.php" method="post" enctype="multipart/form-data">
    <div style="text-align: center;"><h4><i class="icon-plus-sign icon-large"></i> Add Purchase</h4></div>
    <hr>
    <div style="text-align:left;">
        <div id="ac">
            <span>Date: <br></span><input type="date" style="width:265px; height:30px;" name="date"
                                          placeholder="MM/DD/YYYY"/><br>
            <span>Invoice Number: </span><input type="text" style="width:265px; height:30px;" name="iv"/><br>
            <span>Supplier : </span>
            <select name="supplier" style="width:265px; height:30px;">
                <?php
                include('../connect.php');
                $result = $db->prepare("SELECT * FROM supliers");
                $result->bindParam(':userid', $res);
                $result->execute();
                for ($i = 0; $row = $result->fetch(); $i++) {
                    ?>
                    <option><?php echo $row['suplier_name']; ?></option>
                    <?php
                }
                ?>
            </select><br>
            <span>Invoice Amount:<br> </span><input type="text" style="width:265px; height:30px;" name="invoice_amount"/><br>
            <span>PO Number:<br> </span><input type="text" style="width:265px; height:30px;" id="po_no" name="po_no" autocomplete="off" /><br>
            <span>PO Amount:<br> </span><input type="text" style="width:265px; height:30px;" id="po_amount" name="po_amount"  autocomplete="off" /><br>
            <span>Attachments:<br> </span><input type="file" multiple style="width:265px; height:30px;" name="attachments[]"/><br>
            <span>Remarks:<br> </span><input type="text" style="width:265px; height:30px;" name="remarks"/><br>
            <span>&nbsp;</span><input id="btn" type="submit" value="save"/>
        </div>
    </div>
</form>

<script src="js/bootstrap-typeahead.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#po_no").typeahead({
            onSelect: function (item) {
                if(item) {
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