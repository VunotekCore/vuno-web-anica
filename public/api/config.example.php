<?php
/**
 * Archivo de Configuración - Ejemplo
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'config.php' en el mismo directorio
 * 2. Completa todas las credenciales con tus datos reales
 * 3. NO subas config.php a Git (ya está en .gitignore)
 */

return [
    // ============================================
    // CONFIGURACIÓN DE EMAIL (HOSTINGER SMTP)
    // ============================================
    'email' => [
        // Servidor SMTP de Hostinger
        'smtp_host' => 'smtp.hostinger.com',
        
        // Puerto SMTP (587 para TLS, 465 para SSL)
        'smtp_port' => 587,
        
        // Tipo de encriptación ('tls' o 'ssl')
        'smtp_encryption' => 'tls',
        
        // Tu email de Hostinger
        'smtp_username' => 'tu-email@tudominio.com',
        
        // Contraseña de tu email
        'smtp_password' => 'tu-contraseña-aqui',
        
        // Email que aparecerá como remitente
        'from_email' => 'contacto@tudominio.com',
        'from_name' => 'The Real Devs',
        
        // Destinatarios principales (pueden ser múltiples)
        // Opción 1: Un solo destinatario (string)
        // 'to_email' => 'tu-email@tudominio.com',
        // 'to_name' => 'Equipo The Real Devs',
        
        // Opción 2: Múltiples destinatarios (array)
        'to_email' => [
            'admin@tudominio.com' => 'Administrador',
            'ventas@tudominio.com' => 'Equipo de Ventas',
            'soporte@tudominio.com' => 'Soporte Técnico',
        ],
        
        // Destinatarios en copia (CC) - opcional
        'cc_email' => [
            'gerencia@tudominio.com' => 'Gerencia',
        ],
        
        // Destinatarios en copia oculta (BCC) - opcional
        'bcc_email' => [
            'backup@tudominio.com' => 'Backup',
        ],
        
        // Email de respuesta (reply-to)
        'reply_to_email' => 'contacto@tudominio.com',
    ],

    // ============================================
    // CONFIGURACIÓN DE GOOGLE CALENDAR API
    // ============================================
    'google_calendar' => [
        // Client ID de Google Cloud Console
        'client_id' => 'tu-client-id.apps.googleusercontent.com',
        
        // Client Secret de Google Cloud Console
        'client_secret' => 'tu-client-secret',
        
        // Refresh Token (se genera durante la configuración)
        'refresh_token' => 'tu-refresh-token',
        
        // ID del calendario principal donde se creará el evento
        'calendar_id' => 'primary', // o 'tu-email@gmail.com'
        
        // Calendarios adicionales donde también se creará el evento (opcional)
        // Útil si quieres que aparezca en múltiples calendarios de tu organización
        'additional_calendars' => [
            // 'equipo@tudominio.com',
            // 'ventas@tudominio.com',
        ],
        
        // Invitados adicionales que recibirán la invitación (además del cliente)
        'additional_attendees' => [
            'admin@tudominio.com' => 'Administrador',
            'ventas@tudominio.com' => 'Equipo de Ventas',
            // Agrega más emails según necesites
        ],
        
        // Zona horaria
        'timezone' => 'America/Managua', // Ajusta según tu ubicación
    ],

    // ============================================
    // CONFIGURACIÓN GENERAL
    // ============================================
    'app' => [
        // URL de tu sitio web
        'site_url' => 'https://tudominio.com',
        
        // Dominios permitidos para CORS (seguridad)
        'allowed_origins' => [
            'https://tudominio.com',
            'http://localhost:4321', // Para desarrollo local
        ],
        
        // Activar modo debug (solo en desarrollo)
        'debug' => false,
    ],
];
