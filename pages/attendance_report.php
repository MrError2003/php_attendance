<div><h1><i class="bi bi-calendar2-check"></i> Reporte de asistencia</h1></div>
<hr>
<?php 
// $studentList = $actionClass->list_student();
$classList = $actionClass->list_class();
$class_id = $_GET['class_id'] ?? "";
$class_month = $_GET['class_month'] ?? "";

$studentList = $actionClass->attendanceStudentsMonthly($class_id, $class_month);
$monthLastDay = 0;
$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];
$nombre_mes = '';
if(!empty($class_month)){
    $monthLastDay = date("d", strtotime("{$class_month}-1 -1 day -1 month"));
    $numero_mes = date("n", strtotime($class_month)); // Obtiene el número del mes
    $nombre_mes = $meses[$numero_mes]; // Asigna el nombre en español
}
// echo $monthLastDay;
?>
<form action="" id="manage-attendance">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-12 col-sm-12 col-12">
            <div id="msg"></div>
            <div class="card shadow mb-3">
                <div class="card-body rounded-0">
                    <div class="container-fluid">
                        <div class="row align-items-end">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                <label for="class_id" class="form-label">Clase</label>
                                <select name="class_id" id="class_id" class="form-select" required="required">
                                    <option value="" disabled <?= empty($class_id) ? "selected" : "" ?>> -- Seleccionar clase -- </option>
                                    <?php if(!empty($classList) && is_array($classList)): ?>
                                    <?php foreach($classList as $row): ?>
                                        <option value="<?= $row['id'] ?>" <?= (isset($class_id) && $class_id == $row['id']) ? "selected" : "" ?>><?= $row['name'] ?></option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                <label for="class_month" class="form-label">Fecha</label>
                                <input type="month" name="class_month" id="class_month" 
                                    class="form-control" value="<?= $class_month ?? '' ?>" 
                                    required="required"
                                    style="-webkit-text-fill-color: transparent; color: transparent;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(!empty($class_id) && !empty($class_month)): ?>
            <div class="card shadow mb-3">
                <div class="card-body">
                    <div class="container-fluid">
                        <fieldset>
                            <legend class="h6"><strong>Abreviaturas:</strong></legend>
                            <div class="ps-4">
                                <div><span class="text-success fw-bold">P</span> <span class="ms-1">= Presente</span></div>
                                <div><span class="text-body-emphasis fw-bold">T</span> <span class="ms-1">= Tarde</span></div>
                                <div><span class="text-danger fw-bold">A</span> <span class="ms-1">= Ausente</span></div>
                                <div><span class="text-primary fw-bold">F</span> <span class="ms-1">= Festivo</span></div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-3">
                <div class="card-body">
                    <div class="container-fluid">
                    <div class="px-2 py-2 text-center bg-primary text-light fw-bolder"><?= $nombre_mes ?></div>
                        <div class="table-responsive position-relative">
                            <table id="attendance-rpt-tbl" class="table table-bordered">
                                <thead>
                                    <tr class="bg-primary bg-opacity-75">
                                        <th class="text-center bg-primary text-light">Estudiantes</th>
                                        <?php for($i=1; $i <= $monthLastDay; $i++): ?>
                                            <th class="text-center bg-transparent text-light" style="width:80px !important"><?= $i ?></th>
                                        <?php endfor; ?>
                                        <th class="text-center bg-primary text-light">TP</th>
                                        <th class="text-center bg-primary text-light">TT</th>
                                        <th class="text-center bg-primary text-light">TA</th>
                                        <th class="text-center bg-primary text-light">TF</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($studentList) && is_array($studentList)): ?>
                                    <?php foreach($studentList as $row): ?>
                                        <tr class="student-row">
                                            <td class="px-2 py-1" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <input type="hidden" name="student_id[]" value="<?= $row['id'] ?>">
                                                <?= $row['name'] ?>
                                            </td>
                                            <?php 
                                            $tp = 0;
                                            $tt = 0;
                                            $ta = 0;
                                            $tf = 0;
                                            ?>
                                            <?php for($i=1; $i <= $monthLastDay; $i++): ?>
                                                <td class="text-center px-2 py-1 text-dark-emphasis">
                                                    <?php 
                                                        $i = str_pad($i, 2, 0, STR_PAD_LEFT);
                                                        switch(($row['attendance'][$class_month."-".$i] ?? '')){
                                                            case 1:
                                                                echo "<span class='text-success fw-bold'>P</span>";
                                                                $tp += 1;
                                                                break;
                                                            case 2:
                                                                echo "<span class='text-body-emphasis fw-bold'>T</span>";
                                                                $tt += 1;
                                                                break;
                                                            case 3:
                                                                echo "<span class='text-danger fw-bold'>A</span>";
                                                                $ta += 1;
                                                                break;
                                                            case 4:
                                                                echo "<span class='text-primary fw-bold'>F</span>";
                                                                $tf += 1;
                                                                break;
                                                        }
                                                    ?>
                                                </td>
                                            <?php endfor; ?>
                                            <th class="text-center bg-secondary text-light"><?= $tp ?></th>
                                            <th class="text-center bg-secondary text-light"><?= $tt ?></th>
                                            <th class="text-center bg-secondary text-light"><?= $ta ?></th>
                                            <th class="text-center bg-secondary text-light"><?= $tf ?></th>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="<?= $monthLastDay + 5 ?>" class="px-2 py-1 text-center">No hay registro de estudiantes</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>
<script>
    $(document).ready(function(){
        $('#class_id, #class_month').change(function(e){
            var class_id = $('#class_id').val()
            var class_month = $('#class_month').val()
            location.replace(`./?page=attendance_report&class_id=${class_id}&class_month=${class_month}`)
        })
    })
</script>