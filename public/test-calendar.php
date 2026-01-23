<?php
/**
 * Test de Google Calendar API (versión ligera)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/LightweightGoogleCalendar.php';
$config = require __DIR__ . '/api/config.php';
echo "config -> " . json_encode($config);
echo "=== TEST DE GOOGLE CALENDAR API (LIGERO) ===\n\n";

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

// 2. Crear cliente ligero
echo "2. Creando cliente ligero de Google Calendar...\n";
try {
    $googleConfig = [
        'client_id' => $config['google_calendar']['client_id'],
        'client_secret' => $config['google_calendar']['client_secret'],
        'refresh_token' => $config['google_calendar']['refresh_token']
    ];
    $calendar = new LightweightGoogleCalendar($googleConfig);
    echo "✅ Cliente de Google Calendar creado\n\n";
} catch (Exception $e) {
    die("❌ ERROR al crear cliente: " . $e->getMessage() . "\n");
}

// 3. Intentar refrescar token
echo "3. Intentando refrescar token de acceso...\n";
try {
    $accessToken = $calendar->refreshToken();
    echo "✅ Token de acceso obtenido exitosamente\n";
    echo "   Token: " . substr($accessToken, 0, 20) . "...\n\n";
} catch (Exception $e) {
    echo "❌ ERROR al refrescar token:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n";
    echo "\nPosibles causas:\n";
    echo "   1. Refresh token inválido o expirado\n";
    echo "   2. Client ID o Client Secret incorrectos\n";
    echo "   3. Aplicación no autorizada en Google Cloud Console\n";
    echo "   4. Refresh token no tiene los permisos correctos\n";
    echo "\nSolución: Genera un nuevo refresh token usando get-refresh-token.php\n";
    exit(1);
}

// 4. Listar calendarios
echo "4. Listando calendarios disponibles...\n";
try {
    // Crear un endpoint simple para listar calendarios
    $listUrl = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
    $response = $calendar->makeRequest($listUrl, 'GET', null);
    
    if (isset($response['items'])) {
        echo "✅ Calendarios encontrados:\n";
        foreach ($response['items'] as $calendarInfo) {
            $isPrimary = $calendarInfo['id'] === 'primary' || ($calendarInfo['primary'] ?? false);
            $marker = $isPrimary ? ' [PRIMARY]' : '';
            echo "   - " . ($calendarInfo['summary'] ?? 'Sin nombre') . " (ID: " . $calendarInfo['id'] . ")$marker\n";
        }
        echo "\n";
    } else {
        echo "⚠️ No se encontraron calendarios\n\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR al listar calendarios:\n";
    echo "   " . $e->getMessage() . "\n\n";
}

// 5. Intentar crear un evento de prueba
echo "5. Intentando crear evento de prueba...\n";
try {
    $eventData = [
        'summary' => 'Test desde formulario de contacto (LIGERO)',
        'description' => 'Este es un evento de prueba usando la librería ligera. Puedes eliminarlo.',
        'start' => [
            'dateTime' => date('c', strtotime('+1 day')),
            'timeZone' => $config['google_calendar']['timezone'],
        ],
        'end' => [
            'dateTime' => date('c', strtotime('+1 day +1 hour')),
            'timeZone' => $config['google_calendar']['timezone'],
        ],
        'attendees' => [
            [
                'email' => 'dflores2t@gmail.com',
                'displayName' => 'Usuario de Prueba',
            ]
        ],
    ];
    
    $calendarId = $config['google_calendar']['calendar_id'];
    $createdEvent = $calendar->createEvent($calendarId, $eventData);
    
    echo "✅ ¡EVENTO CREADO EXITOSAMENTE!\n";
    echo "   ID: " . $createdEvent['id'] . "\n";
    echo "   Link: " . $createdEvent['htmlLink'] . "\n";
    echo "   Inicio: " . $createdEvent['start']['dateTime'] . "\n\n";
    
    echo "NOTA: Revisa tu Google Calendar. Puedes eliminar este evento de prueba.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR al crear evento:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
