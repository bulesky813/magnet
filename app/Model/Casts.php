<?php

declare (strict_types=1);

namespace App\Model;

/**
 */
class Casts extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'casts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'works'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'works' => 'object'
    ];
}
