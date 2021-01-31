<?php

declare (strict_types=1);

namespace App\Model;

/**
 */
class Subject extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subject';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'content',
        'source',
        'favorites'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'content' => 'object'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $primaryKey = 'number';
}
