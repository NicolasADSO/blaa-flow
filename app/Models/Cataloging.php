<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cataloging extends Model
{
    protected $fillable = [
        'control_process_id',
        'user_id',
        'status',
        'notes',

        // nuevos
        'title','subtitle','authors','publisher','place_of_publication','edition','publication_year','isbn','language',
        'classification_system','classification_code','call_number','shelf_location',
        'subjects','descriptors',
        'pages','dimensions','material_type',
        'barcode','cover_image_path','quality_status','record_completed_at',
    ];

    protected $casts = [
        'authors'             => 'array',
        'subjects'            => 'array',
        'publication_year'    => 'integer',
        'pages'               => 'integer',
        'record_completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Si marcamos Finalizado y no tiene record_completed_at, lo sellamos
        static::saving(function (self $model) {
            if ($model->status === 'Finalizado' && is_null($model->record_completed_at)) {
                $model->record_completed_at = now();
            }
        });
    }

    public function process()
    {
        return $this->belongsTo(ControlProcess::class, 'control_process_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
