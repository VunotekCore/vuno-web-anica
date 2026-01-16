<?php
/**
 * Test de envío real de email
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/api/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== TEST DE ENVÍO DE EMAIL ===\n\n";

try {
    $mail = new PHPMailer(true);
    
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host = $config['email']['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['email']['smtp_username'];
    $mail->Password = $config['email']['smtp_password'];
    $mail->SMTPSecure = $config['email']['smtp_encryption'];
    $mail->Port = $config['email']['smtp_port'];
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 2; // Ver debug completo
    
    // Configurar remitente
    $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);
    
    // Agregar destinatario
    $toEmail = $config['email']['to_email'];
    if (is_string($toEmail)) {
        $mail->addAddress($toEmail, $config['email']['to_name'] ?? '');
        echo "\n✅ Destinatario agregado: $toEmail\n";
    } else {
        echo "\n❌ ERROR: to_email debe ser un string para este test\n";
        exit(1);
    }
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Test desde formulario de contacto';
    $mail->Body = '<h1>Email de Prueba</h1><p>Si recibes este email, la configuración funciona correctamente.</p>';
    $mail->AltBody = 'Email de Prueba - Si recibes este email, la configuración funciona correctamente.';
    
    // Enviar
    echo "\n\n=== INTENTANDO ENVIAR EMAIL ===\n\n";
    $mail->send();
    
    echo "\n\n✅ ¡EMAIL ENVIADO EXITOSAMENTE!\n";
    echo "Revisa tu bandeja de entrada en: {$config['email']['to_email']}\n";
    
} catch (Exception $e) {
    echo "\n\n❌ ERROR AL ENVIAR EMAIL:\n";
    echo "Mensaje: {$mail->ErrorInfo}\n";
    echo "Excepción: {$e->getMessage()}\n";
}
