<div class="page-title mb-3">Lista de clases</div>
<hr>
<?php 
$classList = $actionClass->list_class();
?>
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10 col-sm-12 col-12">
        <div class="card shadow">
            <div class="card-header rounded-0">
                <div class="d-flex w-100 justify-content-end align-items-center">
                    <button class="btn btn-sm rounded-0 btn-primary" type="button" id="add_class"><i class="far fa-plus-square"></i> Add New</button>
                </div>
            </div>
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hovered table-stripped">
                            <colgroup>
                                <col width="10%">
                                <col width="70%">
                                <col width="20%">
                            </colgroup>
                            <thead class="bg-dark-subtle">
                                <tr class="bg-transparent">
                                    <th class="bg-transparent text-center">ID</th>
                                    <th class="bg-transparent text-center">Grupo - Programa</th>
                                    <th class="bg-transparent text-center">Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($classList) && is_array($classList)): ?>
                                <?php foreach($classList as $row): ?>
                                    <tr>
                                        <td class="text-center px-2 py-1"><?= $row['id'] ?></td>
                                        <td class="px-2 py-1"><?= $row['name'] ?></td>
                                        <td class="text-center px-2 py-1">
                                            <div class="input-group input-group-sm justify-content-center">
                                                <button class="btn btn-sm btn-outline-primary rounded-0 edit_class" type="button" data-id="<?= $row['id'] ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger rounded-0 delete_class" type="button" data-id="<?= $row['id'] ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <th class="text-center px-2 py-1" colspan="3">No encontrado.</th>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#add_class').click(function(e){
            e.preventDefault()
            open_modal('class_form.php', `<?= isset($id) ? "Crear nueva clase" : "Actualizar clase" ?>`)
        })
        $('.edit_class').click(function(e){
            e.preventDefault()
            var id = $(this)[0].dataset?.id || ''
            open_modal('class_form.php', `<?= isset($id) ? "Crear nueva clase" : "Actualizar clase" ?>`, {id: id})
        })
        $('.delete_class').click(function(e){
            e.preventDefault()
            var id = $(this)[0].dataset?.id || ''
            start_loader()
            if(confirm(`¿Estás seguro de eliminar la clase seleccionada? Esta acción no se puede deshacer.`) == true){
                $.ajax({
                    url: "./ajax-api.php?action=delete_class",
                    method: "POST",
                    data: { id : id},
                    dataType: 'JSON',
                    error: (error) => {
                        console.error(error)
                        alert('An error occurred.')
                    },
                    success:function(resp){
                        if(resp?.status != '')
                            location.reload();
                        else
                            end_loader();
                    }
                })
            }else{
                end_loader();
            }
        })
    })
</script>