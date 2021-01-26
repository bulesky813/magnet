<?php

declare (strict_types=1);

namespace App\Model;

/**
 */
class Number extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'number';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'process',
        'local'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public $timestamps = false;
}
