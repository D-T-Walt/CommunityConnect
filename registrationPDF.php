<?php
// Generates a PDF for the selected logged-in user's registration.
session_start();
if (empty($_SESSION['logged_in']) || empty($_SESSION['Email_Address'])) {
    http_response_code(403); exit('Please log in first.');
}
$id = filter_input(INPUT_POST, 'registrationID', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Select a registration first.'); }
require __DIR__ . '/connectDatabase.php';
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$stmt = $conn->prepare("SELECT reg.registrationID, reg.registrationDate,
    CONCAT(r.firstName, ' ', r.lastName) AS fullName, r.email, r.phoneNumber,
    reg.parish, a.activity, reg.participationDate,
    GROUP_CONCAT(s.skill ORDER BY s.skill SEPARATOR ', ') AS skills
    FROM registrations reg
    JOIN registrants r ON reg.email = r.email
    JOIN activities a ON reg.activityID = a.activityID
    LEFT JOIN registrants_skills s ON reg.email = s.email
    WHERE reg.registrationID = ? AND reg.email = ?
    GROUP BY reg.registrationID");
$email = $_SESSION['Email_Address'];
$stmt->bind_param('is', $id, $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close(); $conn->close();
if (!$row) { http_response_code(404); exit('Registration not found.'); }
$e = static function ($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$fields = [
    'Registration ID' => $row['registrationID'], 'Registration Date' => $row['registrationDate'],
    'Full Name' => $row['fullName'], 'Email' => $row['email'],
    'Phone Number' => $row['phoneNumber'], 'Parish' => $row['parish'],
    'Activity' => $row['activity'], 'Participation Date' => $row['participationDate'],
    'Interests / Skills' => $row['skills'],
];
$html = '<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#183e43}h1{background:#176b5b;color:white;padding:16px;font-size:22px}table{border-collapse:collapse;width:100%}td{border:1px solid #ccd8d7;padding:10px}td:first-child{background:#eef6f4;font-weight:bold;width:36%}p{font-size:12px}</style></head><body>';
$html .= '<h1>CommunityConnect — Registration Confirmation</h1><p>Thank you for registering for a community activity.</p><table>';
foreach ($fields as $label => $value) { $html .= '<tr><td>' . $e($label) . '</td><td>' . $e($value) . '</td></tr>'; }
$html .= '</table></body></html>';
$options = new Options(); $options->set('isRemoteEnabled', false);
$pdf = new Dompdf($options); $pdf->loadHtml($html); $pdf->setPaper('A4'); $pdf->render();
$pdf->stream('CommunityConnect_Registration_' . $id . '.pdf', ['Attachment' => true]);
exit;
