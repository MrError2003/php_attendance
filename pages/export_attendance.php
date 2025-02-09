<?php
require __DIR__ . '../../vendor/autoload.php';
require __DIR__ . '../../db-connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Fill, Color, Border};
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validar parámetros
if (!isset($_GET['class_id'], $_GET['class_month']) || empty($_GET['class_id']) || empty($_GET['class_month'])) {
    die('Parámetros inválidos');
}

// Sanitizar y validar
$class_id = $conn->real_escape_string($_GET['class_id']);
$class_month = $conn->real_escape_string($_GET['class_month']);

// Validar formato de fecha YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $class_month)) {
    die('Formato de fecha inválido. Use YYYY-MM');
}

// Obtener datos
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$studentList = [];
$students = $conn->query("SELECT * FROM students_tbl WHERE class_id = '{$class_id}' ORDER BY name ASC");

if ($students->num_rows > 0) {
    while ($student = $students->fetch_assoc()) {
        $attendance = [];
        $att = $conn->query("SELECT status, DATE_FORMAT(class_date, '%Y-%m-%d') as fecha 
                           FROM attendance_tbl 
                           WHERE student_id = '{$student['id']}' 
                           AND DATE_FORMAT(class_date, '%Y-%m') = '{$class_month}'");
        
        while ($row = $att->fetch_assoc()) {
            $attendance[$row['fecha']] = $row['status'];
        }
        
        $studentList[] = [
            'id' => $student['id'],
            'name' => $student['name'],
            'attendance' => $attendance
        ];
    }
}

// Calcular días del mes CORREGIDO
$monthLastDay = (int)date("t", strtotime($class_month . '-01'));
$numero_mes = (int)date("n", strtotime($class_month . '-01'));
$nombre_mes = $meses[$numero_mes] ?? 'Mes Desconocido';

// Crear spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar título
$lastColumn = Coordinate::stringFromColumnIndex(1 + $monthLastDay + 4);
$sheet->mergeCells("A1:{$lastColumn}1");
$sheet->setCellValue('A1', "Reporte de asistencia - {$nombre_mes}");
$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);

// Encabezados
$headerRow = 3;
$columns = ['Estudiantes'];
for ($i = 1; $i <= $monthLastDay; $i++) {
    $columns[] = $i;
}
$columns = array_merge($columns, ['TP', 'TT', 'TA', 'TF']);

// Estilos
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

$totalStyle = [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6C757D']],
    'font' => ['color' => ['rgb' => 'FFFFFF']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// Escribir encabezados
$sheet->fromArray([$columns], null, "A{$headerRow}");
$sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray($headerStyle);

// Llenar datos
$currentRow = $headerRow + 1;
foreach ($studentList as $student) {
    $rowData = [$student['name']];
    $tp = $tt = $ta = $tf = 0;

    for ($day = 1; $day <= $monthLastDay; $day++) {
        $fecha = date("Y-m", strtotime($class_month . '-01')) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        $status = $student['attendance'][$fecha] ?? 0;
        
        switch ($status) {
            case 1: $cell = 'P'; $color = '28A745'; $tp++; break;
            case 2: $cell = 'T'; $color = '212529'; $tt++; break;
            case 3: $cell = 'A'; $color = 'DC3545'; $ta++; break;
            case 4: $cell = 'F'; $color = '0D6EFD'; $tf++; break;
            default: $cell = ''; $color = null;
        }
        
        $rowData[] = $cell;
        if ($color) {
            $colIndex = count($rowData); // Índice correcto base 1
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getStyle("{$colLetter}{$currentRow}")->getFont()
                  ->setBold(true)->setColor(new Color($color));
        }
    }
    
    // Añadir totales
    $rowData = array_merge($rowData, [$tp, $tt, $ta, $tf]);
    $sheet->fromArray([$rowData], null, "A{$currentRow}");
    
    // Aplicar estilo a totales CORREGIDO
    $startCol = Coordinate::stringFromColumnIndex($monthLastDay + 2);
    $endCol = Coordinate::stringFromColumnIndex($monthLastDay + 5);
    $totalRange = "{$startCol}{$currentRow}:{$endCol}{$currentRow}";
    $sheet->getStyle($totalRange)->applyFromArray($totalStyle);
    
    $currentRow++;
}

// Ajustar columnas
$sheet->getColumnDimension('A')->setWidth(30);
for ($col = 2; $col <= (1 + $monthLastDay + 4); $col++) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $sheet->getColumnDimension($colLetter)->setWidth(5);
}

// Congelar paneles
$sheet->freezePane('B4');

// Generar archivo
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Reporte_Asistencia_' . urlencode($nombre_mes) . '.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;