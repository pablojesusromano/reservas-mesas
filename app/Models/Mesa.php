<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model {

    use SoftDeletes;
    public $timestamps = true;
    protected $table = "mesas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'ubicacion',
        'numero',
        'cantidad_personas'
    ];
    public const UBICACIONES = ['A', 'B', 'C', 'D'];

    public function reservas(): BelongsToMany
    {
        return $this->belongsToMany(Reserva::class);
    }

    public static function getNextNumero($ubicacion)
    {
        $maxNumero = Mesa::where('ubicacion', $ubicacion)->max('numero');
        return ($maxNumero ?? 0) + 1;
    }
}