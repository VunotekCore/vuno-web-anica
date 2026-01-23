<?php
/**
 * Script para generar Refresh Token de Google Calendar (versión ligera)
 * Ejecuta este script y sigue las instrucciones
 */

require_once __DIR__ . '/../vendor/LightweightGoogleCalendar.php';
$config = require __DIR__ . '/api/config.php';

// Configuración
$clientId = $config['google_calendar']['client_id'];
$clientSecret = $config['google_calendar']['client_secret'];
$redirectUri = $config['google_calendar']['redirect_uri'];

echo "<!DOCTYPE html>\n<html>\n<head>\n<title>Generar Refresh Token</title>\n<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}pre{background:#f4f4f4;padding:15px;border-radius:5px;overflow-x:auto;}code{background:#f4f4f4;padding:2px 6px;border-radius:3px;}.success{color:green;}.error{color:red;}.warning{color:orange;}</style>\n</head>\n<body>\n";

echo "<h1>🔑 Generador de Refresh Token para Google Calendar</h1>\n";

// Crear URL de autorización manualmente
$scope = 'https://www.googleapis.com/auth/calendar';
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'scope' => $scope,
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
]);

// Si no hay código en la URL, mostrar link de autorización
if (!isset($_GET['code'])) {
    echo "<h2>Paso 1: Autorizar la Aplicación</h2>\n";
    echo "<p>Haz clic en el botón de abajo para autorizar la aplicación:</p>\n";
    echo "<p><a href='$authUrl' style='display:inline-block;background:#4285f4;color:white;padding:12px 24px;text-decoration:none;border-radius:4px;font-weight:bold;'>🔐 Autorizar con Google</a></p>\n";
    
    echo "<h3>⚠️ Importante:</h3>\n";
    echo "<ul>\n";
    echo "<li>Asegúrate de que el servidor PHP esté corriendo en <code>localhost:8000</code></li>\n";
    echo "<li>Inicia sesión con la cuenta de Google que quieres usar para el calendario</li>\n";
    echo "<li>Acepta todos los permisos solicitados</li>\n";
    echo "<li>Serás redirigido de vuelta a esta página automáticamente</li>\n";
    echo "</ul>\n";
    
    echo "<h3>📋 Configuración Actual:</h3>\n";
    echo "<pre>";
    echo "Client ID: $clientId\n";
    echo "Redirect URI: $redirectUri\n";
    echo "Scope: Calendar Events\n";
    echo "</pre>\n";
    
    echo "<p class='warning'><strong>Nota:</strong> Si ves un error de \"redirect_uri_mismatch\", agrega esta URL en Google Cloud Console:</p>\n";
    echo "<pre>$redirectUri</pre>\n";
    
} else {
    // Intercambiar código por token
    echo "<h2>Paso 2: Generando Tokens...</h2>\n";
    
    try {
        $data = [
            'code' => $_GET['code'],
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $token = json_decode($response, true);

        if (isset($token['error'])) {
            echo "<p class='error'>❌ <strong>Error:</strong></p>\n";
            echo "<pre>";
            print_r($token);
            echo "</pre>\n";
            
            echo "<h3>Posibles soluciones:</h3>\n";
            echo "<ul>\n";
            echo "<li>Verifica que el Client ID y Client Secret sean correctos</li>\n";
            echo "<li>Asegúrate de que la redirect URI esté configurada en Google Cloud Console</li>\n";
            echo "<li>Intenta de nuevo haciendo clic <a href='?'>aquí</a></li>\n";
            echo "</ul>\n";
        } else {
            echo "<p class='success'>✅ <strong>¡Éxito! Tokens generados correctamente</strong></p>\n";
            
            if (isset($token['refresh_token'])) {
                echo "<h3>🎉 Refresh Token Generado:</h3>\n";
                echo "<pre style='background:#e8f5e9;border:2px solid #4caf50;'>";
                echo htmlspecialchars($token['refresh_token']);
                echo "</pre>\n";
                
                echo "<h3>📝 Instrucciones:</h3>\n";
                echo "<ol>\n";
                echo "<li><strong>Copia</strong> el refresh token de arriba</li>\n";
                echo "<li><strong>Abre</strong> el archivo <code>public/api/config.php</code></li>\n";
                echo "<li><strong>Pega</strong> el refresh token en la configuración:</li>\n";
                echo "</ol>\n";
                
                echo "<pre>";
                echo "'google_calendar' => [\n";
                echo "    'client_id' => '$clientId',\n";
                echo "    'client_secret' => '...',\n";
                echo "    'refresh_token' => '" . htmlspecialchars($token['refresh_token']) . "', // ← Pega aquí\n";
                echo "    'calendar_id' => 'primary',\n";
                echo "    'timezone' => 'America/Managua',\n";
                echo "],\n";
                echo "</pre>\n";
                
                echo "<h3>✅ Verificar:</h3>\n";
                echo "<p>Después de actualizar <code>config.php</code>, ejecuta:</p>\n";
                echo "<pre>php public/test-calendar.php</pre>\n";
                
                echo "<p>Si todo está correcto, deberías ver: <span class='success'>✅ ¡EVENTO CREADO EXITOSAMENTE!</span></p>\n";
                
            } else {
                echo "<p class='warning'>⚠️ <strong>Advertencia:</strong> No se generó un refresh token nuevo.</p>\n";
                echo "<p>Esto puede pasar si ya autorizaste la aplicación antes. Para forzar un nuevo refresh token:</p>\n";
                echo "<ol>\n";
                echo "<li>Ve a <a href='https://myaccount.google.com/permissions' target='_blank'>Google Account Permissions</a></li>\n";
                echo "<li>Revoca el acceso a tu aplicación</li>\n";
                echo "<li>Vuelve a intentar <a href='?'>aquí</a></li>\n";
                echo "</ol>\n";
            }
            
            echo "<h3>📊 Token Completo (para debug):</h3>\n";
            echo "<details>\n";
            echo "<summary>Ver detalles del token</summary>\n";
            echo "<pre>";
            print_r($token);
            echo "</pre>\n";
            echo "</details>\n";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<p>Intenta de nuevo haciendo clic <a href='?'>aquí</a></p>\n";
    }
}

echo "</body>\n</html>";
