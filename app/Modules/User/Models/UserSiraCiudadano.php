<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla de CIUDADANO en el esquema SIRA
 *
 * Representa la información de ciudadanos en el sistema SIRA
 */
class UserSiraCiudadano extends Model
{
  /**
   * Nombre de la tabla
   * @var string
   */
  protected $table = 'sira_ciudadanos';

  /**
   * Indica si la tabla usa timestamps
   * @var bool
   */
  public $timestamps = false;

  /**
   * Clave primaria de la tabla
   * @var string
   */
  protected $primaryKey = 'id_ciudadano';

  /**
   * Tipo de clave primaria
   * @var string
   */
  protected $keyType = 'string';

  /**
   * Atributos que se pueden asignar masivamente
   * @var array
   */
  protected $fillable = [
    'id_ciudadano',
    'primer_nombre',
    'segundo_nombre',
    'primer_apellido',
    'segundo_apellido',
    'sexo',
    'rif',
    'condicion_zurdo',
    'id_ocupacion',
    'fecha_nacimiento',
    'id_estado_civil',
    'id_catastro',
    'telefono_hab',
    'telefono_movil',
    'email_principal',
    'email_alterno',
    'fecha_fallecimiento',
    'estatus_afiliado',
    'cedula_extranjero',
    'id_nacionalidad',
  ];

  /**
   * Atributos que se ocultan en las representaciones JSON
   * @var array
   */
  protected $hidden = [
    // Agregar campos sensibles si es necesario
  ];

  /**
   * Atributos que deben ser convertidos a tipos nativos
   * @var array
   */
  protected $casts = [
    'fecha_nacimiento' => 'datetime',
    'fecha_fallecimiento' => 'datetime',
  ];

  public function findIdCiudadano($idCiudadano)
  {
    return $this->where('id_ciudadano', $idCiudadano)->first();
  }
}
