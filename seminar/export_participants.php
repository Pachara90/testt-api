<?php
require 'vendor/autoload.php';
require 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator("Your System")
        ->setTitle("Participants Export")
        ->setSubject("Participants Data");

    // Fetch data
    $sql = 'SELECT p.*, e.title AS event_title
            FROM participants p
            INNER JOIN events e ON p.event_id = e.id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $participants = $stmt->fetchAll();

    // Add headers
    $sheet->setCellValue('A1', 'ลำดับ');
    $sheet->setCellValue('B1', 'ชื่อ-นามสกุล');
    $sheet->setCellValue('C1', 'กิจกรรม');
    $sheet->setCellValue('D1', 'อีเมล');
    $sheet->setCellValue('E1', 'เบอร์โทร');
    $sheet->setCellValue('F1', 'วันที่ลงทะเบียน');

    // Add data
    $row = 2;
    foreach ($participants as $i => $p) {
        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $p['fullname']);
        $sheet->setCellValue('C' . $row, $p['event_title']);
        $sheet->setCellValue('D' . $row, $p['email']);
        $sheet->setCellValue('E' . $row, $p['phone']);
        $sheet->setCellValue('F' . $row, $p['created_at']);
        $row++;
    }

    // Auto size columns
    foreach(range('A','F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Create a second sheet for event counts
    $eventSheet = $spreadsheet->createSheet();
    $eventSheet->setTitle('Event Counts');
    
    $stmt = $pdo->query('SELECT e.title, COUNT(p.id) AS participant_count
                        FROM events e
                        LEFT JOIN participants p ON e.id = p.event_id
                        GROUP BY e.id');
    $eventCounts = $stmt->fetchAll();

    $eventSheet->setCellValue('A1', 'กิจกรรม');
    $eventSheet->setCellValue('B1', 'จำนวนผู้เข้าร่วม');
    
    $row = 2;
    foreach ($eventCounts as $count) {
        $eventSheet->setCellValue('A' . $row, $count['title']);
        $eventSheet->setCellValue('B' . $row, $count['participant_count']);
        $row++;
    }

    // Auto size columns for event counts
    $eventSheet->getColumnDimension('A')->setAutoSize(true);
    $eventSheet->getColumnDimension('B')->setAutoSize(true);

    // Redirect output to a client's web browser (Xlsx)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="participants_export_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}