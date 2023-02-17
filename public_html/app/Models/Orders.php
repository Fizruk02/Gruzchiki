<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

    public $timestamps = false;

    use HasFactory;

    public $filter = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'cabinet_id',
    ];

    /**
     * Get the user that owns the cabinet.
     */
    public function cabinet()
    {
        return $this->hasOne(Cabinet::class, 'id', 'cabinet_id');
    }

    public function balance()
    {
        return $this->hasOne(OrdersBalance::class, 'orders_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(OrdersUsers::class, 'order_id', 'id');
    }

    public function orders_values() {
        return $this->hasMany(OrdersValues::class, 'orders_id', 'id');
    }

    public function getTitle() {
        return $this->getField('title');
    }

    public function getInfo() {
        return $this->getField('client_name');
    }

    public function getField($name) {
        $field_id = null;

        foreach ($this->cabinet->orders_fields as $of) {
            if ($of->type == $name) {
                $field_id = $of->id;
            }
        }

        if (!$field_id) return null;

        foreach ($this->orders_values as $vf) {
            if ($vf->orders_fields_id == $field_id) {
                return $vf->value;
            }
        }

        return null;
    }
}
