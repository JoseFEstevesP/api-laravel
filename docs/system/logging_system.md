# Logging System Documentation

## Overview

This document describes the new logging system that separates audit logs from technical logs in the Laravel application. The system provides clear separation of concerns while maintaining both types of logs in rotating files for performance reasons.

## Architecture

### Channels

#### 1. Audit Channel (`audit`)

- **Purpose**: Business and compliance events
- **File**: `storage/logs/audit.log`
- **Retention**: 90 days (3 months)
- **Level**: `info` and above
- **Format**: JSON when `LOG_JSON_FORMATTER` is enabled
- **Use Cases**:
  - User authentication events (login, logout, failed attempts)
  - User actions and operations
  - Authorization and permission checks
  - Sensitive data access
  - Financial transactions
  - Configuration changes
  - Compliance-related events

#### 2. Technical Channel (`technical`)

- **Purpose**: System debugging, performance monitoring, and error tracking
- **File**: `storage/logs/technical.log`
- **Retention**: 30 days (1 month)
- **Level**: Configurable via `LOG_LEVEL` environment variable (default: debug)
- **Format**: Standard Laravel log format
- **Use Cases**:
  - System errors and exceptions
  - Performance metrics and slow queries
  - Application flow and debugging information
  - Third-party service interactions
  - Infrastructure events
  - Development and operational debugging

#### 3. Security Channel (`security`)

- **Purpose**: High-priority security events
- **File**: `storage/logs/security.log`
- **Retention**: 180 days (6 months)
- **Level**: `warning` and above
- **Format**: JSON structured format
- **Use Cases**:
  - Failed login attempts
  - Account lockouts
  - Security violations
  - High-risk events

#### 4. Stack Channel (`stack`)

- **Purpose**: Default logging channel
- **Configuration**: Routes to `technical` channel by default
- **Use Cases**: General application logging that is not audit or security related

## Usage

### Using the AuditLogger Service

The application provides a centralized `AuditLogger` service for consistent audit logging:

```php
use App\Modules\Audit\Services\AuditLogger;

// Log a general audit event
AuditLogger::log(
  'event_name',
  ['context' => 'data'],
  $userId,
  'resource_type',
  'resource_id',
  'description',
);

// Log a security event
AuditLogger::logSecurity(
  'security_event',
  ['context' => 'data'],
  $userId,
  'description',
);

// Log a user action
AuditLogger::logUserAction(
  'action_name',
  $userId,
  'resource_type',
  'resource_id',
  ['details' => 'data'],
);

// Log authentication events
AuditLogger::logAuthentication(
  'login', // or 'logout', 'failed_login', etc.
  $userId,
  ['details' => 'data'],
);
```

### Direct Channel Usage

You can also log directly to specific channels:

```php
use Illuminate\Support\Facades\Log;

// Log to audit channel
Log::channel('audit')->info('Audit message', ['data' => 'value']);

// Log to technical channel
Log::channel('technical')->error('Technical error', ['error' => 'details']);

// Log to security channel
Log::channel('security')->warning('Security event', ['event' => 'details']);
```

## Configuration

### Environment Variables

The logging system can be configured using the following environment variables:

- `LOG_CHANNEL`: Default log channel (default: `stack`)
- `LOG_LEVEL`: Log level for technical logs (default: `debug`)
- `LOG_JSON_FORMATTER`: Enable JSON formatting for audit logs (default: `false`)

### File Locations

- Audit logs: `storage/logs/audit.log`
- Technical logs: `storage/logs/technical.log`
- Security logs: `storage/logs/security.log`
- Legacy logs: `storage/logs/laravel.log`

## Migration

The system has been designed for gradual migration:

1. New audit events should use the `AuditLogger` service
2. Existing audit logging has been updated to use the new channels
3. The old `audits` channel still exists but will be deprecated
4. All new audit events go to the `audit` channel
5. Security-related audit events go to the `security` channel

## Best Practices

### For Audit Logs

- Use the `AuditLogger` service for consistency
- Include relevant context data
- Sanitize sensitive information before logging
- Use appropriate event names that are descriptive
- Always include user ID when available

### For Technical Logs

- Use appropriate log levels (debug, info, warning, error, critical)
- Include sufficient context for debugging
- Avoid logging sensitive information
- Use structured data when possible

### For Security Logs

- Reserve for high-priority security events
- Include IP addresses and user agents when relevant
- Use descriptive event names
- Log with appropriate severity levels

## Maintenance

### Log Rotation

- Audit logs: Rotated daily, retained for 90 days
- Technical logs: Rotated daily, retained for 30 days
- Security logs: Rotated daily, retained for 180 days

### Monitoring

- Monitor security log size and content regularly
- Set up alerts for critical security events
- Review audit logs for compliance requirements
- Monitor technical logs for system health

## Troubleshooting

If logs are not appearing in expected locations:

1. Check that the correct channel is being used
2. Verify that the logging configuration matches the environment
3. Ensure the application has write permissions to the storage/logs directory
4. Check that the log levels are appropriate for the events being logged
