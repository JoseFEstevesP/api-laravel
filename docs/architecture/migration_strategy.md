# Estrategia de Migración: Separación de Logs de Auditoría y Técnicos

## Resumen

Este documento describe la estrategia de migración para separar los logs de auditoría de los logs técnicos en la aplicación Laravel. El objetivo es lograr una clara separación de responsabilidades mientras se mantiene la estabilidad del sistema y la integridad de los datos.

## Estado Actual

- Los logs de auditoría y técnicos están mezclados en los mismos canales/archivos
- Algunos eventos de auditoría se registran en el canal `audits`
- El sistema usa el paquete owen-it/laravel-auditing para auditoría de modelos
- Todos los logs se almacenan en archivos rotativos

## Estado Objetivo

- Separación completa de logs de auditoría y técnicos
- Canales dedicados para cada tipo de log
- Diferentes políticas de retención basadas en requerimientos
- Registro de auditoría consistente usando el nuevo servicio AuditLogger
- Formato estructurado para logs de auditoría y seguridad

## Fases de Migración

### Fase 1: Preparación (Antes del Despliegue)

**Duración**: 1-2 días

#### Tareas:

1. **Preparación de Código**:
   - Actualizar todo el registro de auditoría para usar el nuevo servicio AuditLogger
   - Modificar configuración de logging para incluir nuevos canales
   - Crear la clase de servicio AuditLogger
   - Actualizar middleware existente y casos de uso para usar nuevos canales

2. **Actualizaciones de Configuración**:
   - Actualizar `config/logging.php` con definiciones de nuevos canales
   - Actualizar `config/audit.php` para mantener compatibilidad
   - Agregar variables de entorno para nueva configuración de logging

3. **Pruebas**:
   - Probar el nuevo sistema de logging en ambiente de desarrollo
   - Verificar que los logs se escriban en los archivos correctos
   - Asegurar que no haya pérdida de datos durante la transición

#### Variables de Entorno a Agregar:

```
LOG_JSON_FORMATTER=false  # Habilitar formato JSON para logs de auditoría
AUDIT_LOG_CHANNEL=audit   # Canal para logs de auditoría
TECHNICAL_LOG_CHANNEL=technical  # Canal para logs técnicos
```

### Fase 2: Despliegue (Día de la Migración)

**Duración**: 1 día (con capacidad de rollback)

#### Checklist Previo al Despliegue:

- [ ] Respaldar archivos de log actuales
- [ ] Asegurar espacio suficiente en disco para nuevos archivos de log
- [ ] Verificar que la aplicación tenga permisos de escritura en directorios de logs
- [ ] Probar despliegue en ambiente de staging
- [ ] Preparar plan de rollback

#### Pasos del Despliegue:

1. **Desplegar Cambios de Configuración**:
   - Desplegar `config/logging.php` actualizado
   - Desplegar `config/audit.php` actualizado
   - Desplegar nuevo servicio `AuditLogger.php`
   - Desplegar middleware y casos de uso actualizados

2. **Configuración del Entorno**:
   - Actualizar variables de entorno según sea necesario
   - Verificar que la aplicación pueda escribir en nuevos archivos de log

3. **Monitoreo Inicial del Despliegue**:
   - Monitorear logs de la aplicación en busca de errores
   - Verificar que se estén creando nuevos logs
   - Verificar que los eventos de auditoría se registren correctamente

#### Plan de Rollback:

Si surgen problemas durante el despliegue:

1. Revertir archivos de configuración a versiones anteriores
2. Reiniciar la aplicación
3. Verificar que el sistema regrese al estado anterior
4. Investigar y corregir problemas antes de intentar la migración nuevamente

### Fase 3: Validación y Monitoreo (Post-Despliegue)

**Duración**: 1 semana

#### Pasos de Validación:

1. **Verificar Separación de Logs**:
   - Verificar que los logs de auditoría se escriban en `storage/logs/audit.log`
   - Verificar que los logs técnicos se escriban en `storage/logs/technical.log`
   - Verificar que los logs de seguridad se escriban en `storage/logs/security.log`
   - Confirmar que no aparezcan eventos de auditoría en logs técnicos

2. **Verificación de Integridad de Datos**:
   - Verificar que no se perdieron datos de auditoría durante la migración
   - Verificar que todos los eventos de auditoría esperados sigan siendo capturados
   - Confirmar que el logging técnico continúe funcionando normalmente

3. **Monitoreo de Rendimiento**:
   - Monitorear rendimiento de la aplicación después de los cambios
   - Verificar uso de espacio en disco para archivos de log
   - Confirmar que la rotación de logs funcione correctamente

#### Checklist de Monitoreo:

- [ ] Los logs de auditoría se escriben en archivos correctos
- [ ] Los logs técnicos se escriben en archivos correctos
- [ ] Los logs de seguridad se escriben en archivos correctos
- [ ] No hay errores en logs de la aplicación
- [ ] Los tamaños de archivos de log son razonables
- [ ] La rotación de logs funciona adecuadamente
- [ ] Todos los eventos de auditoría son capturados como se espera

### Fase 4: Optimización y Limpieza (Después de la Validación)

**Duración**: 1-2 semanas después de validación exitosa

#### Tareas:

1. **Ajuste de Rendimiento**:
   - Ajustar períodos de retención basados en uso real
   - Optimizar formatos de log si es necesario
   - Afinar niveles de log

2. **Limpieza**:
   - Eliminar referencias a patrones antiguos de logging
   - Actualizar documentación
   - Capacitar miembros del equipo en nuevas prácticas de logging

## Mitigación de Riesgos

### Riesgos y Estrategias de Mitigación:

1. **Riesgo de Pérdida de Datos**:
   - _Riesgo_: Los datos de auditoría podrían perderse durante la migración
   - _Mitigación_: Respaldar logs existentes, probar exhaustivamente en staging

2. **Impacto en el Rendimiento**:
   - _Riesgo_: El nuevo sistema de logging podría afectar el rendimiento de la aplicación
   - _Mitigación_: Monitorear métricas de rendimiento, optimizar según sea necesario

3. **Problemas de Espacio en Disco**:
   - _Riesgo_: Múltiples archivos de log podrían consumir más espacio en disco
   - _Mitigación_: Monitorear uso de disco, ajustar políticas de retención

4. **Errores en la Aplicación**:
   - _Riesgo_: Configuración incorrecta podría causar errores en la aplicación
   - _Mitigación_: Pruebas exhaustivas, plan de rollback listo

## Criterios de Éxito

La migración es exitosa cuando:

- [ ] Los logs de auditoría están completamente separados de los logs técnicos
- [ ] Todos los eventos de auditoría continúan siendo capturados
- [ ] No se rompe ninguna funcionalidad de la aplicación
- [ ] El rendimiento se mantiene o mejora
- [ ] Las políticas de retención de logs se implementan correctamente
- [ ] Los miembros del equipo comprenden el nuevo sistema de logging

## Cronograma

| Fase         | Duración  | Fecha de Inicio | Fecha de Fin |
| ------------ | --------- | --------------- | ------------ |
| Preparación  | 2 días    | Día -3          | Día -1       |
| Despliegue   | 1 día     | Día 0           | Día 0        |
| Validación   | 1 semana  | Día 1           | Día 7        |
| Optimización | 2 semanas | Día 8           | Día 21       |

## Responsabilidades del Equipo

- **Equipo de Desarrollo**: Implementar cambios de código, probar en desarrollo
- **Equipo de DevOps**: Desplegar cambios, monitorear sistemas, gestionar rollback si es necesario
- **Equipo de QA**: Validar funcionalidad después del despliegue
- **Equipo de Seguridad**: Verificar que se cumplan los requisitos de auditoría
