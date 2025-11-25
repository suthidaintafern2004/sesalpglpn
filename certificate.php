<?php
session_start();
require_once 'db_connect.php';

if (!isset($_POST['session_id'])) {
    echo "Session ID is missing.";
    exit;
}

$session_id = $_POST['session_id'];

// ดึงข้อมูล session
$sql = "SELECT s.*, 
               CONCAT(t.PrefixName, t.fname, ' ', t.lname) AS teacher_full_name, 
               CONCAT(sp.PrefixName, sp.fname, ' ', sp.lname) AS supervisor_full_name
        FROM supervision_sessions s
        LEFT JOIN teacher t ON s.teacher_t_pid = t.t_pid
        LEFT JOIN supervisor sp ON s.supervisor_p_id = sp.p_id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Session not found.";
    exit;
}

$session = $result->fetch_assoc();

// ตรวจสอบว่า session นี้ถูกประเมินแล้วหรือยัง
if ($session['satisfaction_submitted'] != 1) {
    echo "This session has not been evaluated yet.";
    exit;
}

// ข้อมูลสำหรับ Certificate
$teacher_name = $session['teacher_full_name'];
$supervisor_name = $session['supervisor_full_name'];
$supervision_date_formatted = date("j F Y", strtotime($session['supervision_date'])); // Format date

// --- START: Thai Date Formatting ---
function toThaiDate($dateStr) {
    $thai_months = [
        'January' => 'มกราคม', 'February' => 'กุมภาพันธ์', 'March' => 'มีนาคม',
        'April' => 'เมษายน', 'May' => 'พฤษภาคม', 'June' => 'มิถุนายน',
        'July' => 'กรกฎาคม', 'August' => 'สิงหาคม', 'September' => 'กันยายน',
        'October' => 'ตุลาคม', 'November' => 'พฤศจิกายน', 'December' => 'ธันวาคม'
    ];
    $date = new DateTime($dateStr);
    $day = $date->format('j');
    $month = $thai_months[$date->format('F')];
    $year = (int)$date->format('Y') + 543;
    return ['day' => $day, 'month' => $month, 'year' => $year];
}

$issue_date_parts = toThaiDate('now');
// --- END: Thai Date Formatting ---

// Include TCPDF library
require_once __DIR__ . '/vendor/autoload.php';

// Create new PDF document
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // 'L' for Landscape

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SESA System');
$pdf->SetTitle('Supervision Certificate');
$pdf->SetSubject('Certificate of Supervision');
$pdf->SetKeywords('TCPDF, certificate, supervision');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(10, 10, 10, true);

// Add a page
$pdf->AddPage();

// --- START: Add background image ---
// Get current auto-page-break mode
$auto_page_break = $pdf->getAutoPageBreak();
// Disable auto-page-break
$pdf->SetAutoPageBreak(false, 0);
// Set background image
// Assuming ctest.png is in the same directory as certificate.php
// The image is stretched to fit the page (A4 size: 210x297 mm)
$pdf->Image('images/ctest.png', 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0); // Adjusted for Landscape A4
// Restore auto-page-break status
$pdf->SetAutoPageBreak($auto_page_break, 10); // Restore with original margin
// Set the starting point for the page content
$pdf->setPageMark();
// --- END: Add background image ---

// --- START: Add Thai font ---
// Define the path to the fonts directory
$fontPath = __DIR__ . '/fonts/';

// Add the THSarabun font by specifying the full path to the definition file.
// This is the most reliable method.
$pdf->AddFont('thsarabun', 'B', $fontPath . 'thsarabunb.php'); // Bold
$pdf->AddFont('thsarabun', '', $fontPath . 'thsarabun.php');  // Regular
// --- END: Add Thai font ---

// Set font for the content
$pdf->SetFont('thsarabun', '', 20);

// ... (ส่วนโหลด Library และตั้งค่า Font ก่อนหน้า ให้คงไว้เหมือนเดิม) ...

// ตั้งค่าสีตัวอักษร (สีน้ำเงินเข้ม #000033)
$pdf->SetTextColor(0, 0, 51); 

// --- ส่วนที่ 1: ชื่อครู (Teacher Name) ---
// ปรับตำแหน่ง Y (แนวตั้ง) ตรงนี้: ยิ่งเลขมาก ยิ่งลงมาข้างล่าง
// จากรูปเกียรติบัตร พื้นที่ว่างน่าจะอยู่ประมาณ 75-85 มม. จากขอบบน
$pdf->SetFont('thsarabun', 'B', 40); // ปรับขนาดตัวอักษรตรงนี้ (B = ตัวหนา)
$pdf->SetY(87);  
// Cell(width, height, text, border, ln, align) -> Align 'C' คือจัดกึ่งกลางหน้ากระดาษอัตโนมัติ
$pdf->Cell(0, 0, $teacher_name, 0, 1, 'C', 0, '', 0);


// --- ส่วนที่ 2: วันที่ (Date) ---
// จากรูปเกียรติบัตร บรรทัดวันที่อยู่ด้านล่าง ก่อนลายเซ็น
// กะประมาณด้วยสายตา น่าจะอยู่ที่ Y = 155 มม.
$y_date = 155; 
$pdf->SetFont('thsarabun', '', 18); // ขนาดตัวอักษรวันที่

// 2.1 วันที่ (Day)
// ปรับค่า X (แนวนอน) เพื่อขยับซ้าย-ขวา
$pdf->SetXY(130,151); 
$pdf->Cell(10, 0, $issue_date_parts['day'], 0, 0, 'C');

// 2.2 เดือน (Month)
// ปรับค่า X ให้ตรงกับช่องว่างของเดือน
$pdf->SetXY(148, 151); 
$pdf->Cell(30, 0, $issue_date_parts['month'], 0, 0, 'C');

// 2.3 พ.ศ. (Year)
// ปรับค่า X ให้ตรงกับช่องว่างของปี
$pdf->SetXY(93, 151); 
$pdf->Cell(0,0, $issue_date_parts['year'], 0, 0, 'C');

// Output the PDF
$pdf->Output('certificate_' . $session_id . '.pdf', 'I');

// Print text using HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF to the browser
$pdf->Output('certificate_' . $session_id . '.pdf', 'I');
?>
