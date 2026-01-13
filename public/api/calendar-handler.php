<?php
/**
 * Manejador de Google Calendar
 * Crea eventos en Google Calendar cuando se agenda una reunión
 */

// Configurar headers
header('Content-Type: application/json; charset=utf-8');

// Cargar configuración
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de configuración del servidor.'
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

// Cargar Google API Client
require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Obtener datos del formulario
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Datos inválidos');
    }
    
    // Validar campos requeridos para reunión
    $requiredFields = ['name', 'email', 'preferredDate', 'preferredTime', 'meetingType'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }
    
    // Crear cliente de Google
    $client = new Google_Client();
    $client->setClientId($config['google_calendar']['client_id']);
    $client->setClientSecret($config['google_calendar']['client_secret']);
    $client->setAccessType('offline');
    $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
    
    // Configurar refresh token
    $client->refreshToken($config['google_calendar']['refresh_token']);
    
    // Crear servicio de Calendar
    $service = new Google_Service_Calendar($client);
    
    // Preparar datos del evento
    $eventData = prepareEventData($data, $config);
    
    // Crear evento en el calendario principal
    $event = new Google_Service_Calendar_Event($eventData);
    $calendarId = $config['google_calendar']['calendar_id'];
    
    $createdEvent = $service->events->insert($calendarId, $event, [
        'sendUpdates' => 'all', // Enviar invitaciones por email
        'conferenceDataVersion' => 1 // Necesario para Google Meet
    ]);
    
    // Crear evento en calendarios adicionales si están configurados
    $additionalCalendars = $config['google_calendar']['additional_calendars'] ?? [];
    $additionalEvents = [];
    
    foreach ($additionalCalendars as $additionalCalendarId) {
        try {
            $additionalEvent = $service->events->insert($additionalCalendarId, $event, [
                'sendUpdates' => 'all',
                'conferenceDataVersion' => 1
            ]);
            $additionalEvents[] = [
                'calendar_id' => $additionalCalendarId,
                'event_id' => $additionalEvent->getId(),
                'link' => $additionalEvent->getHtmlLink()
            ];
        } catch (Exception $e) {
            // Log error pero continuar con otros calendarios
            error_log("Error al crear evento en calendario $additionalCalendarId: " . $e->getMessage());
        }
    }
    
    // Respuesta exitosa
    http_response_code(200);
    $response = [
        'success' => true,
        'message' => 'Reunión agendada exitosamente',
        'event' => [
            'id' => $createdEvent->getId(),
            'link' => $createdEvent->getHtmlLink(),
            'start' => $createdEvent->getStart()->getDateTime(),
        ]
    ];
    
    // Agregar información de calendarios adicionales si existen
    if (!empty($additionalEvents)) {
        $response['additional_calendars'] = $additionalEvents;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    
    $errorMessage = 'Error al agendar la reunión. Por favor intenta nuevamente.';
    
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
 * Prepara los datos del evento para Google Calendar
 */
function prepareEventData($data, $config) {
    $timezone = $config['google_calendar']['timezone'];
    
    // Parsear fecha y hora
    $date = $data['preferredDate']; // formato: YYYY-MM-DD
    $time = convertTimeToHour($data['preferredTime']); // convertir "9am" a "09:00"
    
    // Crear fecha/hora de inicio
    $startDateTime = $date . 'T' . $time . ':00';
    
    // Calcular fecha/hora de fin (1 hora después por defecto)
    $startTimestamp = strtotime($startDateTime);
    $endTimestamp = $startTimestamp + 3600; // +1 hora
    $endDateTime = date('Y-m-d\TH:i:s', $endTimestamp);
    
    // Preparar descripción del evento
    $description = prepareEventDescription($data);
    
    // Determinar título según tipo de reunión
    $meetingTypes = [
        'virtual' => '💻 Reunión Virtual',
        'presencial' => '🏢 Reunión Presencial',
        'phone' => '📞 Llamada Telefónica'
    ];
    $meetingTypeLabel = $meetingTypes[$data['meetingType']] ?? 'Reunión';
    
    // Preparar datos del evento
    $eventData = [
        'summary' => $meetingTypeLabel . ' - ' . $data['name'],
        'description' => $description,
        'start' => [
            'dateTime' => $startDateTime,
            'timeZone' => $timezone,
        ],
        'end' => [
            'dateTime' => $endDateTime,
            'timeZone' => $timezone,
        ],
        'attendees' => prepareAttendees($data, $config),
        'reminders' => [
            'useDefault' => false,
            'overrides' => [
                ['method' => 'email', 'minutes' => 24 * 60], // 1 día antes
                ['method' => 'popup', 'minutes' => 30], // 30 minutos antes
            ],
        ],
        'guestsCanModify' => false,
        'guestsCanInviteOthers' => false,
        'guestsCanSeeOtherGuests' => true,
    ];
    
    // Agregar ubicación si es reunión presencial
    if ($data['meetingType'] === 'presencial') {
        $eventData['location'] = 'Por confirmar';
    }
    
    // Agregar link de videollamada si es virtual
    if ($data['meetingType'] === 'virtual') {
        $eventData['conferenceData'] = [
            'createRequest' => [
                'requestId' => uniqid(),
                'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
            ]
        ];
    }
    
    return $eventData;
}

/**
 * Convierte formato de hora del formulario a formato HH:MM
 */
function convertTimeToHour($timeString) {
    $timeMap = [
        '9am' => '09:00',
        '10am' => '10:00',
        '11am' => '11:00',
        '2pm' => '14:00',
        '3pm' => '15:00',
        '4pm' => '16:00',
    ];
    
    return $timeMap[$timeString] ?? '09:00';
}

/**
 * Prepara la descripción del evento
 */
function prepareEventDescription($data) {
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $company = $data['company'] ?? 'No especificada';
    $message = $data['message'];
    $meetingType = $data['meetingType'];
    
    $meetingTypeLabels = [
        'virtual' => 'Virtual (Zoom/Meet)',
        'presencial' => 'Presencial',
        'phone' => 'Llamada Telefónica'
    ];
    $meetingTypeLabel = $meetingTypeLabels[$meetingType] ?? $meetingType;
    
    return <<<DESC
INFORMACIÓN DEL CONTACTO:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
👤 Nombre: $name
📧 Email: $email
📱 Teléfono: $phone
🏢 Empresa: $company

TIPO DE REUNIÓN:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
$meetingTypeLabel

TEMAS A DISCUTIR:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
$message

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Reunión agendada desde: The Real Devs Contact Form
DESC;
}

/**
 * Prepara la lista de invitados para el evento
 * Incluye al cliente y a los invitados adicionales configurados
 */
function prepareAttendees($data, $config) {
    $attendees = [];
    
    // Agregar al cliente que solicitó la reunión
    $attendees[] = [
        'email' => $data['email'],
        'displayName' => $data['name'],
        'responseStatus' => 'needsAction',
        'organizer' => false
    ];
    
    // Agregar invitados adicionales de la configuración
    $additionalAttendees = $config['google_calendar']['additional_attendees'] ?? [];
    
    foreach ($additionalAttendees as $email => $name) {
        // Si el array es numérico (sin nombres), usar el valor como email
        if (is_numeric($email)) {
            $email = $name;
            $name = '';
        }
        
        $attendees[] = [
            'email' => $email,
            'displayName' => $name,
            'responseStatus' => 'accepted', // Los miembros del equipo se marcan como aceptados
            'organizer' => false
        ];
    }
    
    return $attendees;
}
