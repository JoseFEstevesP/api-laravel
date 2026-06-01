<?php

namespace App\Modules\Rol\Enums;

enum Permission: string
{
  // Sistema
  case SUPER = 'super';
  // Usuarios
  case USER = 'user';
  case USER_READ = 'user.read';
  case USER_CREATE = 'user.create';
  case USER_UPDATE = 'user.update';
  case USER_DELETE = 'user.delete';
  // Rol
  case ROL = 'rol';
  case ROL_READ = 'rol.read';
  case ROL_CREATE = 'rol.create';
  case ROL_UPDATE = 'rol.update';
  case ROL_DELETE = 'rol.delete';
  // Audit
  case AUDIT_READ = 'audit.read';
  // Session
  case SESSION_READ = 'session.read';
  case SESSION_DELETE = 'session.delete';
}
