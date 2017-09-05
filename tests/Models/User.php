<?php

namespace LukePOLO\LaravelApiMigrations\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    protected $fillable = [
        'api_version'
    ];

    public $id = 1;

    public $name = 'Dwight Schrute';

    public function save(array $options = [])
    {
        return $this;
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        return $this->fill($attributes)->save($options);
    }
}
