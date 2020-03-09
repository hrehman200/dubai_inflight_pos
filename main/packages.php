<?php
include('header.php');
?>

<div class="modal fade modal-offer">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Offer</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="formOffer">
                    <input type="hidden" name="call" id="call" value="saveOffer" />
                    <input type="hidden" name="package_id" id="package_id" value="" />
                    <div class="control-group">
                        <label class="control-label" for="category">Offer Name</label>
                        <div class="controls">
                            <input type="text" id="offe_name" name="offer_name" placeholder="Offer Name" style="height: 30px;">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="percent">Code</label>
                        <div class="controls">
                            <input type="text" id="code" name="code" placeholder="Code" style="height: 30px;">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="percent">Price</label>
                        <div class="controls">
                            <input type="text" id="price" name="price" placeholder="Price" style="height: 30px;">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="percent">Duration</label>
                        <div class="controls">
                            <input type="text" id="duration" name="duration" placeholder="in minutes" style="height: 30px;">
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
            <h3>Packages
                <?php if (!isset($_GET['pkg_id'])) { ?>
                    <button class="btn btn-primary btn-add-package">Add</button>
                <?php } else { ?>
                    <button class="btn" onclick="javascript:history.back();">Back</button>
                <?php } ?>
            </h3>
            <table class="table table-striped">
                <?php
                if ($_GET['pkg_id'] == 0) {
                    $query = $db->query('SELECT * FROM flight_packages WHERE status = 1 ');
                    $query->execute();

                    echo '<tr>
                        <th>Package Name</th>
                        <th>Action</th>
                    </tr>';
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        echo sprintf('<tr>
                            <td><a href="packages.php?pkg_id=%d">%s</a></td>
                            <td><button class="btn btn-delete-package btn-danger" data-id="%d"><i class="icon-trash"></i></button></td>
                        </tr>', $row['id'], $row['package_name'], $row['id']);
                    }
                } else {
                    $query = $db->prepare('SELECT *, fo.id AS offer_id FROM flight_offers fo
                        INNER JOIN flight_packages fp ON fo.package_id = fp.id 
                        WHERE fo.status = 1 AND package_id = ? 
                        ORDER BY duration ASC');
                    $query->execute([$_GET['pkg_id']]);
                    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

                    echo "
                    <tr>
                        <th colspan='5'>Offers under ... {$rows[0]['package_name']}
                        <button class='btn btn-primary btn-add-offer' data-id='{$rows[0]['id']}'>Add Offer</button>
                        </th>
                    </tr>
                    <tr>
                        <th>Offer Name</th>
                        <th>Code</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Action</th>
                    </tr>";
                    foreach ($rows as $row) {
                        echo sprintf('<tr>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td><button class="btn btn-delete-offer btn-danger" data-id="%d"><i class="icon-trash"></i></button></td>
                        </tr>', $row['offer_name'], $row['code'], $row['price'], $row['duration'], $row['offer_id']);
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
        $('.btn-add-package').on('click', function(e) {
            Swal.fire({
                title: 'Enter package name to add:',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'on'
                },
                showCancelButton: true,
                confirmButtonText: 'Add',
                showLoaderOnConfirm: true,
                preConfirm: (packageName) => {
                    return fetch(`api.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'call=addPackage&name=' + packageName
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
                    Swal.fire('Package added')
                        .then(function(result2) {
                            window.location.href = window.location.href;
                        });
                }
            })
        });

        $('.btn-delete-package').on('click', function(e) {

            var packageId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure you want to delete it? <br/> You shouldn\'t be deleting it just for renaming.',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return fetch(`api.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'call=deletePackage&id=' + packageId
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
                    Swal.fire('Package deleted');
                    $(e.target).parents('tr').remove();
                }
            })
        });

        $('.btn-add-offer').on('click', function(e) {
            var pkgId = $(this).data('id');
            $('.modal-offer').modal('show')
                .find('#package_id').val(pkgId).end()
                .find('#offer_name').val('').end()
                .find('#code').val('').end()
                .find('#price').html('').end()
                .find('#duration').val('').end()
                .find('.btn-save').button('reset').on('click', onSave);
        });

        var onSave = function(e) {
            $(e.target).button('loading');
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: $('#formOffer').serializeArray(),
                dataType: 'json',
                success: function(response) {
                    if (response.success == 1) {
                        $('.modal-offer').modal('hide');
                        alert('Offer saved');
                        window.location.href = window.location.href;
                    }
                }
            });
        }

        $('.btn-delete-offer').on('click', function(e) {

            var offerId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure you want to delete it? <br/> You shouldn\'t be deleting it just for renaming.',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return fetch(`api.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'call=deleteOffer&id=' + offerId
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
                    Swal.fire('Offer deleted');
                    $(e.target).parents('tr').remove();
                }
            })
        });
    </script>