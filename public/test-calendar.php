<?php
/**
 * Test de Google Calendar API
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/api/config.php';

echo "=== TEST DE GOOGLE CALENDAR API ===\n\n";

// 1. Verificar credenciales
echo "1. Verificando credenciales de Google Calendar...\n";
$requiredKeys = ['client_id', 'client_secret', 'refresh_token', 'calendar_id'];
foreach ($requiredKeys as $key) {
    if (empty($config['google_calendar'][$key])) {
        echo "❌ ERROR: Falta configurar 'google_calendar.$key'\n";
    } else {
        $value = in_array($key, ['client_secret', 'refresh_token']) ? '***' : $config['google_calendar'][$key];
        echo "✅ google_calendar.$key = $value\n";
    }
}
echo "\n";

// 2. Intentar crear cliente de Google
echo "2. Creando cliente de Google...\n";
try {
    $client = new Google_Client();
    $client->setClientId($config['google_calendar']['client_id']);
    $client->setClientSecret($config['google_calendar']['client_secret']);
    $client->setAccessType('offline');
    $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
    echo "✅ Cliente de Google creado\n\n";
} catch (Exception $e) {
    die("❌ ERROR al crear cliente: " . $e->getMessage() . "\n");
}

// 3. Intentar refrescar token
echo "3. Intentando refrescar token de acceso...\n";
try {
    $tokenResponse = $client->refreshToken($config['google_calendar']['refresh_token']);
    $accessToken = $client->getAccessToken();
    
    if ($accessToken && isset($accessToken['access_token'])) {
        echo "✅ Token de acceso obtenido exitosamente\n";
        echo "   Expira en: " . ($accessToken['expires_in'] ?? 'N/A') . " segundos\n\n";
    } else {
        echo "❌ ERROR: No se pudo obtener access token\n";
        echo "   Respuesta del refresh:\n";
        print_r($tokenResponse);
        echo "\n   Access token actual:\n";
        print_r($accessToken);
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ ERROR al refrescar token:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n";
    echo "\nPosibles causas:\n";
    echo "   1. Refresh token inválido o expirado\n";
    echo "   2. Client ID o Client Secret incorrectos\n";
    echo "   3. Aplicación no autorizada en Google Cloud Console\n";
    echo "   4. Refresh token no tiene los permisos correctos\n";
    echo "\nSolución: Genera un nuevo refresh token siguiendo la guía google-calendar-setup.md\n";
    exit(1);
}

// 4. Crear servicio de Calendar
echo "4. Creando servicio de Google Calendar...\n";
try {
    $service = new Google_Service_Calendar($client);
    echo "✅ Servicio de Calendar creado\n\n";
} catch (Exception $e) {
    die("❌ ERROR al crear servicio: " . $e->getMessage() . "\n");
}

// 5. Listar calendarios
echo "5. Listando calendarios disponibles...\n";
try {
    $calendarList = $service->calendarList->listCalendarList();
    echo "✅ Calendarios encontrados:\n";
    foreach ($calendarList->getItems() as $calendar) {
        $isPrimary = $calendar->getId() === 'primary' || $calendar->getPrimary();
        $marker = $isPrimary ? ' [PRIMARY]' : '';
        echo "   - " . $calendar->getSummary() . " (ID: " . $calendar->getId() . ")$marker\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ ERROR al listar calendarios:\n";
    echo "   " . $e->getMessage() . "\n\n";
}

// 6. Intentar crear un evento de prueba
echo "6. Intentando crear evento de prueba...\n";
try {
    $event = new Google_Service_Calendar_Event([
        'summary' => 'Test desde formulario de contacto',
        'description' => 'Este es un evento de prueba. Puedes eliminarlo.',
        'start' => [
            'dateTime' => date('Y-m-d\TH:i:s', strtotime('+1 day')),
            'timeZone' => $config['google_calendar']['timezone'],
        ],
        'end' => [
            'dateTime' => date('Y-m-d\TH:i:s', strtotime('+1 day +1 hour')),
            'timeZone' => $config['google_calendar']['timezone'],
        ],
        'attendees' => [
            [
                'email' => 'dflores2t@gmail.com',
                'displayName' => 'Usuario de Prueba',
            ]
        ],
    ]);
    
    $calendarId = $config['google_calendar']['calendar_id'];
    $createdEvent = $service->events->insert($calendarId, $event, [
        'sendUpdates' => 'none', // No enviar invitaciones para el test
        'conferenceDataVersion' => 1
    ]);
    
    echo "✅ ¡EVENTO CREADO EXITOSAMENTE!\n";
    echo "   ID: " . $createdEvent->getId() . "\n";
    echo "   Link: " . $createdEvent->getHtmlLink() . "\n";
    echo "   Inicio: " . $createdEvent->getStart()->getDateTime() . "\n\n";
    
    echo "NOTA: Revisa tu Google Calendar. Puedes eliminar este evento de prueba.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR al crear evento:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n";
    
    if (method_exists($e, 'getErrors')) {
        echo "   Errores detallados:\n";
        print_r($e->getErrors());
    }
}

echo "\n=== FIN DEL TEST ===\n";
