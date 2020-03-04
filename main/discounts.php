<?php
include('header.php');
?>

<div class="modal fade modal-discount">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Partner under <i id="spPartner"></i></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="formDiscount">
                    <input type="hidden" name="call" id="call" value="savePartner" />
                    <input type="hidden" name="rnl_parent" id="rnl_parent" value="" />
                    <input type="hidden" name="type" id="type" value="Service" />
                    <input type="hidden" name="parent" id="parent" value="FTF" />
                    <div class="control-group">
                        <label class="control-label" for="category">Name</label>
                        <div class="controls">
                            <input type="text" id="category" name="category" placeholder="Name" style="height: 30px;">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="percent">Percent</label>
                        <div class="controls">
                            <input type="number" id="percent" name="percent" placeholder="Percent" style="height: 30px;">
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <div class="msg"></div>
                <button type="button" class="btn btn-primary btn-save">Add</button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span2">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">

                    <?php
                    include "side-menu.php";
                    ?>

                </ul>
            </div>
            <!--/.well -->
        </div>
        <!--/span-->
        <div class="span10">
            <h3>Partners</h3>
            <table class="table table-striped">
                <?php
                $query = $db->query('SELECT DISTINCT(rnl_parent) AS rnl_parent FROM discounts WHERE rnl_parent != "" ');
                $query->execute();
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    echo sprintf('<tr><td colspan=5><h4>%s %s</h4></td></tr>', $row['rnl_parent'], '<button class="btn btn-primary btn-add-discount" data-rnl-parent="' . $row['rnl_parent'] . '">Add</button>');

                    $query2 = $db->prepare('SELECT * FROM discounts WHERE rnl_parent = ? AND status = 1');
                    $query2->execute([$row['rnl_parent']]);
                    echo '<tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Percent</th>
                        <th>Parent</th>
                        <th>Action</th>
                    </tr>';
                    while ($row2 = $query2->fetch(PDO::FETCH_ASSOC)) {
                        echo sprintf('<tr>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td><button class="btn btn-delete btn-danger" data-id="%d"><i class="icon-trash"></i></button></td>
                        </tr>', $row2['type'], $row2['category'], $row2['percent'] . '%', $row2['parent'], $row2['id']);
                    }
                }
                ?>
            </table>
        </div>
    </div>
    </body>

    <?php include('footer.php'); ?>

    </html>

    <script type="text/javascript">
        $('.btn-add-discount').on('click', function(e) {
            var rnlParent = $(this).data('rnl-parent');
            $('.modal-discount').modal('show')
                .find('#category').val('').end()
                .find('#percent').val('').end()
                .find('#spPartner').html(rnlParent).end()
                .find('#rnl_parent').val(rnlParent).end()
                .find('.btn-save').on('click', onSave);
        });

        var onSave = function(e) {
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: $('#formDiscount').serializeArray(),
                dataType: 'json',
                success: function(response) {
                    if (response.success == 1) {
                        $('.modal-discount').modal('hide');
                        alert('Partner saved');
                        //window.location.href = window.location.href;
                    }
                }
            });
        }

        $('.btn-delete').on('click', function(e) {

            var discountId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure you want to delete it?',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return fetch(`api.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'call=deletePartner&id=' + discountId
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value) {
                    Swal.fire('Partner deleted');
                    $(e.target).parents('tr').remove();
                }
            })
        });
    </script>