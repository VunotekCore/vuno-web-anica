<?php
/**
 * Endpoint para envío de emails desde el formulario de contacto
 * Maneja tanto cotizaciones como agendamiento de reuniones
 */

// Configurar headers CORS y JSON
header('Content-Type: application/json; charset=utf-8');

// Cargar configuración
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de configuración del servidor. Por favor contacta al administrador.'
    ]);
    exit;
}

$config = require $configFile;

// Validar origen de la petición (CORS)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $config['app']['allowed_origins'])) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Cargar PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    // Obtener datos del formulario
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Datos del formulario inválidos');
    }
    
    // Validar campos requeridos
    $requiredFields = ['name', 'email', 'phone', 'message', 'formType'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }
    
    // Validar email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }
    
    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);
    
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = $config['email']['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['email']['smtp_username'];
    $mail->Password = $config['email']['smtp_password'];
    $mail->SMTPSecure = $config['email']['smtp_encryption'];
    $mail->Port = $config['email']['smtp_port'];
    $mail->CharSet = 'UTF-8';
    
    // Configurar remitente
    $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);
    
    // Agregar destinatarios principales
    addRecipients($mail, $config['email']['to_email'], $config['email']['to_name'] ?? '');
    
    // Agregar destinatarios en copia (CC) si existen
    if (!empty($config['email']['cc_email'])) {
        addRecipients($mail, $config['email']['cc_email'], '', 'cc');
    }
    
    // Agregar destinatarios en copia oculta (BCC) si existen
    if (!empty($config['email']['bcc_email'])) {
        addRecipients($mail, $config['email']['bcc_email'], '', 'bcc');
    }
    
    // Configurar reply-to (responder al cliente)
    $mail->addReplyTo($data['email'], $data['name']);
    
    // Determinar tipo de formulario y preparar contenido
    $formType = $data['formType'];
    
    if ($formType === 'cotizar') {
        // Email para cotización de proyecto
        $mail->Subject = '🎯 Nueva Solicitud de Cotización - ' . $data['name'];
        $mail->isHTML(true);
        $mail->Body = generateQuoteEmailHTML($data);
        $mail->AltBody = generateQuoteEmailText($data);
        
    } elseif ($formType === 'agendar') {
        // Email para agendamiento de reunión
        $mail->Subject = '📅 Nueva Solicitud de Reunión - ' . $data['name'];
        $mail->isHTML(true);
        $mail->Body = generateMeetingEmailHTML($data);
        $mail->AltBody = generateMeetingEmailText($data);
        
    } else {
        throw new Exception('Tipo de formulario inválido');
    }
    
    // Enviar email
    $mail->send();
    
    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Mensaje enviado exitosamente. Nos pondremos en contacto contigo pronto.'
    ]);
    
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    
    $errorMessage = 'Error al enviar el mensaje. Por favor intenta nuevamente.';
    
    // En modo debug, mostrar el error real
    if ($config['app']['debug'] ?? false) {
        $errorMessage .= ' Debug: ' . $e->getMessage();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
}

/**
 * Genera el contenido HTML del email para cotización
 */
function generateQuoteEmailHTML($data) {
    $name = htmlspecialchars($data['name']);
    $email = htmlspecialchars($data['email']);
    $phone = htmlspecialchars($data['phone']);
    $company = htmlspecialchars($data['company'] ?? 'No especificada');
    $projectType = htmlspecialchars($data['projectType'] ?? 'No especificado');
    $budget = htmlspecialchars($data['budget'] ?? 'No especificado');
    $timeline = htmlspecialchars($data['timeline'] ?? 'No especificado');
    $message = nl2br(htmlspecialchars($data['message']));
    
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #18A0BA 0%, #0d7a8f 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #18A0BA; margin-bottom: 5px; }
        .value { background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #18A0BA; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Nueva Solicitud de Cotización</h1>
        </div>
        <div class="content">
            <div class="field">
                <div class="label">👤 Nombre Completo:</div>
                <div class="value">$name</div>
            </div>
            
            <div class="field">
                <div class="label">📧 Email:</div>
                <div class="value"><a href="mailto:$email">$email</a></div>
            </div>
            
            <div class="field">
                <div class="label">📱 Teléfono:</div>
                <div class="value">$phone</div>
            </div>
            
            <div class="field">
                <div class="label">🏢 Empresa:</div>
                <div class="value">$company</div>
            </div>
            
            <div class="field">
                <div class="label">💼 Tipo de Proyecto:</div>
                <div class="value">$projectType</div>
            </div>
            
            <div class="field">
                <div class="label">💰 Presupuesto Estimado:</div>
                <div class="value">$budget</div>
            </div>
            
            <div class="field">
                <div class="label">⏱️ Tiempo de Entrega:</div>
                <div class="value">$timeline</div>
            </div>
            
            <div class="field">
                <div class="label">💬 Mensaje:</div>
                <div class="value">$message</div>
            </div>
        </div>
        <div class="footer">
            <p>Este mensaje fue enviado desde el formulario de contacto de The Real Devs</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Genera el contenido de texto plano del email para cotización
 */
function generateQuoteEmailText($data) {
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $company = $data['company'] ?? 'No especificada';
    $projectType = $data['projectType'] ?? 'No especificado';
    $budget = $data['budget'] ?? 'No especificado';
    $timeline = $data['timeline'] ?? 'No especificado';
    $message = $data['message'];
    
    return <<<TEXT
NUEVA SOLICITUD DE COTIZACIÓN
================================

Nombre Completo: $name
Email: $email
Teléfono: $phone
Empresa: $company

Tipo de Proyecto: $projectType
Presupuesto Estimado: $budget
Tiempo de Entrega: $timeline

Mensaje:
$message

================================
Este mensaje fue enviado desde el formulario de contacto de The Real Devs
TEXT;
}

/**
 * Genera el contenido HTML del email para reunión
 */
function generateMeetingEmailHTML($data) {
    $name = htmlspecialchars($data['name']);
    $email = htmlspecialchars($data['email']);
    $phone = htmlspecialchars($data['phone']);
    $company = htmlspecialchars($data['company'] ?? 'No especificada');
    $preferredDate = htmlspecialchars($data['preferredDate'] ?? 'No especificada');
    $preferredTime = htmlspecialchars($data['preferredTime'] ?? 'No especificada');
    $meetingType = htmlspecialchars($data['meetingType'] ?? 'No especificado');
    $message = nl2br(htmlspecialchars($data['message']));
    
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #18A0BA 0%, #0d7a8f 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #18A0BA; margin-bottom: 5px; }
        .value { background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #18A0BA; }
        .highlight { background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Nueva Solicitud de Reunión</h1>
        </div>
        <div class="content">
            <div class="highlight">
                <strong>📆 Fecha Solicitada:</strong> $preferredDate<br>
                <strong>🕐 Hora Solicitada:</strong> $preferredTime<br>
                <strong>💻 Tipo de Reunión:</strong> $meetingType
            </div>
            
            <div class="field">
                <div class="label">👤 Nombre Completo:</div>
                <div class="value">$name</div>
            </div>
            
            <div class="field">
                <div class="label">📧 Email:</div>
                <div class="value"><a href="mailto:$email">$email</a></div>
            </div>
            
            <div class="field">
                <div class="label">📱 Teléfono:</div>
                <div class="value">$phone</div>
            </div>
            
            <div class="field">
                <div class="label">🏢 Empresa:</div>
                <div class="value">$company</div>
            </div>
            
            <div class="field">
                <div class="label">💬 Temas a Discutir:</div>
                <div class="value">$message</div>
            </div>
        </div>
        <div class="footer">
            <p>Este mensaje fue enviado desde el formulario de contacto de The Real Devs</p>
            <p><em>Recuerda confirmar la reunión y agregar el evento al calendario</em></p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Genera el contenido de texto plano del email para reunión
 */
function generateMeetingEmailText($data) {
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $company = $data['company'] ?? 'No especificada';
    $preferredDate = $data['preferredDate'] ?? 'No especificada';
    $preferredTime = $data['preferredTime'] ?? 'No especificada';
    $meetingType = $data['meetingType'] ?? 'No especificado';
    $message = $data['message'];
    
    return <<<TEXT
NUEVA SOLICITUD DE REUNIÓN
================================

DETALLES DE LA REUNIÓN:
Fecha Solicitada: $preferredDate
Hora Solicitada: $preferredTime
Tipo de Reunión: $meetingType

INFORMACIÓN DEL CONTACTO:
Nombre Completo: $name
Email: $email
Teléfono: $phone
Empresa: $company

Temas a Discutir:
$message

================================
Este mensaje fue enviado desde el formulario de contacto de The Real Devs
Recuerda confirmar la reunión y agregar el evento al calendario
TEXT;
}

/**
 * Agrega destinatarios al email (soporta string o array)
 * 
 * @param PHPMailer $mail Instancia de PHPMailer
 * @param string|array $emails Email(s) a agregar
 * @param string $defaultName Nombre por defecto si no se especifica
 * @param string $type Tipo: 'to', 'cc', o 'bcc'
 */
function addRecipients($mail, $emails, $defaultName = '', $type = 'to') {
    // Si es un string simple, agregar un solo destinatario
    if (is_string($emails)) {
        switch ($type) {
            case 'cc':
                $mail->addCC($emails, $defaultName);
                break;
            case 'bcc':
                $mail->addBCC($emails, $defaultName);
                break;
            default:
                $mail->addAddress($emails, $defaultName);
        }
        return;
    }
    
    // Si es un array, agregar múltiples destinatarios
    if (is_array($emails)) {
        foreach ($emails as $email => $name) {
            // Si el array es numérico (sin nombres), usar el valor como email
            if (is_numeric($email)) {
                $email = $name;
                $name = '';
            }
            
            switch ($type) {
                case 'cc':
                    $mail->addCC($email, $name);
                    break;
                case 'bcc':
                    $mail->addBCC($email, $name);
                    break;
                default:
                    $mail->addAddress($email, $name);
            }
        }
    }
}
