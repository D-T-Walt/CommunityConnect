<?php
/*Shemael Deslandes - 2306823*/
// Sends a registration confirmation using PHPMailer.
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function cc_send_registration_email($email, $name, $activity, $date, $parish, $registrationID): bool
{
    $configPath = __DIR__ . '/mailConfiguration.php';
    if (!is_file($configPath)) {
        error_log('CommunityConnect: mailConfiguration.php is missing.');
        return false;
    }
    $config = require $configPath;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)$config['port'];
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'CommunityConnect - Registration Confirmation';
        $safe = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
        $mail->Body = '<div style="background-color: #f0f7f5; padding: 30px; font-family: Arial, sans-serif; color: #263b3a;">
                            <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 10px; overflow: hidden;">
                                <!-- Header -->
                                <div style="background-color: #176b5b; padding: 25px; text-align: center; color: #ffffff;">
                                    <h1 style="margin: 0; font-size: 26px;">CommunityConnect</h1>
                                    <p style="margin: 8px 0 0; font-size: 14px;">
                                        Connecting People. Strengthening Communities.
                                    </p>
                                </div>

                                <!-- Email Content -->
                                <div style="padding: 30px;">
                                    <h2 style="color: #176b5b; margin-top: 0;">
                                        Registration Confirmed!
                                    </h2>
                                    <p style="font-size: 15px; line-height: 1.6;">
                                        Hello <strong>' . $safe($name) . '</strong>,
                                    </p>
                                    <p style="font-size: 15px; line-height: 1.6;">
                                        Thank you for registering with CommunityConnect!
                                        Your registration has been successfully received.
                                        We appreciate your willingness to participate and make
                                        a positive difference in your community.
                                    </p>
                                    <!-- Registration ID -->
                                    <div style="background-color: #e6f3ef; border-left: 4px solid #176b5b; padding: 15px; margin: 25px 0;">
                                        <p style="margin: 0; font-size: 13px; color: #526b65;">
                                            YOUR REGISTRATION ID
                                        </p>
                                        <h2 style="margin: 5px 0 0; color: #176b5b;">
                                            ' . $safe($registrationID) . '
                                        </h2>
                                    </div>
                                    <!-- Registration Details -->
                                    <h3 style="color: #176b5b; border-bottom: 1px solid #e0e8e5; padding-bottom: 10px;">
                                        Registration Details
                                    </h3>
                                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                        <tr>
                                            <td style="padding: 12px 8px; color: #647873; border-bottom: 1px solid #edf1ef;">
                                                Activity
                                            </td>
                                            <td style="padding: 12px 8px; font-weight: bold; border-bottom: 1px solid #edf1ef;">
                                                ' . $safe($activity) . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 12px 8px; color: #647873; border-bottom: 1px solid #edf1ef;">
                                                Parish
                                            </td>
                                            <td style="padding: 12px 8px; font-weight: bold; border-bottom: 1px solid #edf1ef;">
                                                ' . $safe($parish) . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 12px 8px; color: #647873;">
                                                Participation Date
                                            </td>
                                            <td style="padding: 12px 8px; font-weight: bold;">
                                                ' . $safe($date) . '
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="font-size: 15px; margin-bottom: 0;">
                                        Best regards,<br>
                                        <strong style="color: #176b5b;">The CommunityConnect Team</strong>
                                    </p>

                                </div>

                                <!-- Footer -->
                                <div style="background-color: #f5f8f7; padding: 18px; text-align: center; border-top: 1px solid #e5ece9;">
                                    <p style="font-size: 12px; color: #71817c; margin: 0;">
                                        Thank you for being part of our community!
                                    </p>
                                    <p style="font-size: 11px; color: #9aa7a2; margin: 8px 0 0;">
                                        &copy; ' . date('Y') . ' CommunityConnect. All rights reserved.
                                    </p>
                                </div>

                            </div>
                        </div>';
        $mail->AltBody = "Registration Confirmed\nHello {$name},\nRegistration ID: {$registrationID}\nActivity: {$activity}\nParish: {$parish}\nParticipation Date: {$date}";
        return $mail->send();
    } catch (Exception $e) {
        error_log('CommunityConnect PHPMailer: ' . $mail->ErrorInfo);
        return false;
    }
}
