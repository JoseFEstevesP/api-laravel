# Configuración del Cron Job para Limpieza de Sesiones

Este proyecto incluye un comando de Artisan para limpiar sesiones expiradas que se ejecuta diariamente a las 12:00 AM. Para que funcione correctamente, debes configurar el cron job del sistema.

## Comando de Limpieza de Sesiones

El comando `php artisan sessions:clean` limpia:

- Sesiones expiradas (cerrando las activas y eliminando las inactivas)
- Sesiones inactivas antiguas (por defecto, después de 24 horas de inactividad)
- Sesiones muy antiguas (por defecto, después de 30 días desde el login)

## Configuración del Cron Job de Laravel

Para que el scheduler de Laravel funcione, debes agregar esta entrada al cron de tu sistema operativo:

```bash
* * * * * cd /ruta/a/tu/aplicacion && php artisan schedule:run >> /dev/null 2>&1
```

Por ejemplo, si tu aplicación está en `/home/gato99/Documentos/proyectos/trabajo/apppostgre_API`, el comando sería:

```bash
* * * * * cd /home/gato99/Documentos/proyectos/trabajo/apppostgre_API && php artisan schedule:run >> /dev/null 2>&1
```

### Cómo agregar el cron job:

1. Abre el crontab de tu usuario:

```bash
crontab -e
```

2. Agrega la línea mencionada arriba

3. Guarda y cierra el archivo

### Alternativamente, si estás usando un servidor con sudo privilegios:

```bash
sudo crontab -e
```

Y agrega la misma línea.

## Opciones del comando

El comando `sessions:clean` acepta las siguientes opciones:

- `--hours=[número]`: Número de horas después de la expiración para mantener sesiones inactivas (por defecto: 24)
- `--days=[número]`: Número de días después del login para eliminar sesiones muy antiguas (por defecto: 30)
- `--force`: Fuerza la eliminación de todas las sesiones inactivas independientemente del tiempo

Ejemplo:

```bash
php artisan sessions:clean --hours=12 --days=15
```

## Verificación

Para verificar que el comando funciona correctamente, puedes ejecutarlo manualmente:

```bash
php artisan sessions:clean
```

## Notas

- El scheduler está configurado para ejecutarse diariamente a las 00:00 (12:00 AM)
- El proceso es seguro y solo eliminará sesiones según los parámetros configurados
- Se registran logs durante la ejecución del comando para facilitar la depuración si es necesario
