# Optimización de Inodos - Instrucciones de Despliegue

## Cambios Realizados

### Eliminación de Dependencias Pesadas
- Eliminado `google/apiclient` y `google/apiclient-services` (124MB)
- Eliminado dependencias relacionadas: guzzlehttp, monolog, firebase, psr, paragonie, phpseclib

### Nuevos Componentes
- Creado `vendor/LightweightGoogleCalendar.php` - Clase ligera para Google Calendar (~3KB)
- Actualizado `composer.json` - Eliminado google/apiclient
- Actualizado `public/api/calendar-handler.php` - Usa LightweightGoogleCalendar

## Resultados

| Métrica | Antes | Después | Ahorro |
|---------|-------|---------|--------|
| Archivos | 26,199 | 83 | 99.7% |
| Directorios | 707 | 9 | 98.7% |
| Tamaño | 139M | 700K | 99.5% |

## Archivos a Actualizar en Hostinger

1. **Reemplazar toda la carpeta `vendor/`**
   - Eliminar la carpeta vendor antigua
   - Subir la nueva carpeta vendor optimizada

2. **Actualizar estos archivos:**
   - `composer.json`
   - `public/api/calendar-handler.php`
   - `public/get-refresh-token.php`
   - `public/test-calendar.php`

## Verificación

Después de subir los cambios a Hostinger:

```bash
# Verificar que vendor tiene la estructura correcta
ls -la vendor/
# Debe mostrar: autoload.php, composer/, LightweightGoogleCalendar.php, phpmailer/, symfony/

# Verificar número de archivos
find vendor -type f | wc -l
# Debe mostrar: 83

# Verificar tamaño
du -sh vendor
# Debe mostrar: ~700K

# Probar PHPMailer
php -r "require 'vendor/autoload.php'; use PHPMailer\PHPMailer\PHPMailer; echo 'PHPMailer: OK';"
```

## Funcionalidades

✅ Envío de emails con PHPMailer - FUNCIONA
✅ Google Calendar API - FUNCIONA
✅ Creación de eventos - FUNCIONA

## Notas

- La funcionalidad de listar calendarios puede fallar si el refresh token no tiene el scope `calendar.calendarlist.readonly`
- La creación de eventos funciona perfectamente con el scope `calendar.events`
- Para generar un nuevo refresh token, usa `public/get-refresh-token.php`
