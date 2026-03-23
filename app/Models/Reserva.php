<?php

namespace App\Models;

use App\Models\Mesa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model {

    use SoftDeletes;
    public $timestamps = true;
    protected $table = "reservas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre_solicitante',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'cantidad_personas'
    ];
    public const DURACION_DEFAULT = 2;

    public function mesas(): BelongsToMany 
    {
        return $this->belongsToMany(Mesa::class);
    }
}