<?php
// This is an administrator only report of registered activities.
session_start();
if (empty($_SESSION['cc_admin_authenticated'])) {
    http_response_code(403); exit('Administrator login required.');
}
require __DIR__ . '/connectDatabase.php';
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$sql = "SELECT reg.registrationID, reg.registrationDate,
    CONCAT(r.firstName, ' ', r.lastName) AS fullName, r.email, r.phoneNumber,
    reg.parish, a.activity, reg.participationDate,
    GROUP_CONCAT(s.skill ORDER BY s.skill SEPARATOR ', ') AS skills
    FROM registrations reg
    JOIN registrants r ON reg.email = r.email
    JOIN activities a ON reg.activityID = a.activityID
    LEFT JOIN registrants_skills s ON reg.email = s.email
    GROUP BY reg.registrationID
    ORDER BY reg.registrationDate DESC, reg.registrationID DESC";
$result = $conn->query($sql);
if (!$result) { http_response_code(500); exit('Unable to build report.'); }
$e = static function ($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$headers = ['ID','Registration Date','Full Name','Email','Phone','Parish','Activity','Participation Date','Skills'];
$html = '<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#153b3f;font-size:9px}h1{background:#176b5b;color:white;padding:15px}table{width:100%;border-collapse:collapse}th{background:#176b5b;color:#fff}th,td{border:1px solid #cbdad8;padding:5px;text-align:left;word-break:break-word}tr:nth-child(even){background:#edf5f3}thead{display:table-header-group}</style></head><body>';
$html .= '<h1>CommunityConnect — Administrative Registration Report</h1>';
$html .= '<p>Generated on ' . $e(date('Y-m-d H:i')) . ' | Total registrations: ' . $result->num_rows . '</p>';
$html .= '<table><thead><tr>';
foreach ($headers as $heading) { $html .= '<th>' . $e($heading) . '</th>'; }
$html .= '</tr></thead><tbody>';
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    foreach (['registrationID','registrationDate','fullName','email','phoneNumber','parish','activity','participationDate','skills'] as $column) {
        $html .= '<td>' . $e($row[$column]) . '</td>';
    }
    $html .= '</tr>';
}
$html .= '</tbody></table></body></html>';
$result->free(); $conn->close();
$options = new Options(); $options->set('isRemoteEnabled', false);
$pdf = new Dompdf($options); $pdf->loadHtml($html); $pdf->setPaper('A4', 'landscape'); $pdf->render();
$pdf->stream('CommunityConnect_Admin_Report_' . date('Ymd') . '.pdf', ['Attachment' => true]);
exit;
