<div><h1><i class="bi bi-backpack"></i> Lista de estudiantes</h1></div>
<hr>
<?php 
$studentList = $actionClass->list_student();
?>
<div class="row justify-content-center">
    <div class="col-lg-10 col-md-12 col-sm-12 col-12">
        <div class="card shadow">
            <div class="card-header rounded-0">
                <div class="d-flex w-100 justify-content-end align-items-center">
                    <button class="btn btn-sm rounded-1 btn-primary" type="button" id="add_student"><i class="bi bi-plus-square"></i> Agregar estudiante</button>
                </div>
            </div>
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hovered table-stripped">
                            <colgroup>
                                <col width="10%">
                                <col width="30%">
                                <col width="40%">
                                <col width="20%">
                            </colgroup>
                            <thead class="bg-dark-subtle">
                                <tr class="bg-transparent">
                                    <th class="bg-transparent text-center">ID</th>
                                    <th class="bg-transparent text-center">Grupo - Programa</th>
                                    <th class="bg-transparent text-center">Nombre completo</th>
                                    <th class="bg-transparent text-center">Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($studentList) && is_array($studentList)): ?>
                                <?php foreach($studentList as $row): ?>
                                    <tr>
                                        <td class="text-center px-2 py-1"><?= $row['id'] ?></td>
                                        <td class="px-2 py-1"><?= $row['class'] ?></td>
                                        <td class="px-2 py-1"><?= $row['name'] ?></td>
                                        <td class="text-center px-2 py-1">
                                            <div class="input-group input-group-sm justify-content-center">
                                                <button class="btn btn-sm btn-primary rounded-1 edit_student me-2" type="button" data-id="<?= $row['id'] ?>" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                                                <button class="btn btn-sm btn-danger rounded-1 delete_student" type="button" data-id="<?= $row['id'] ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <th class="text-center px-2 py-1" colspan="4">No encontrado.</th>
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
        $('#add_student').click(function(e){
            e.preventDefault()
            open_modal('student_form.php', `<?= isset($id) ? "Agregar nuevo estudiante" : "Actualizar estudiante" ?>`)
        })
        $('.edit_student').click(function(e){
            e.preventDefault()
            var id = $(this)[0].dataset?.id || ''
            open_modal('student_form.php', `<?= isset($id) ? "Agregar nuevo estudiante" : "Actualizar estudiante" ?>`, {id: id})
        })
        $('.delete_student').click(function(e){
            e.preventDefault()
            var id = $(this)[0].dataset?.id || ''
            start_loader()
            if(confirm(`¿Estás seguro de eliminar el estudiante seleccionado? Esta acción no se puede deshacer.`) == true){
                $.ajax({
                    url: "./ajax-api.php?action=delete_student",
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

