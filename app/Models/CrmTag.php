<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmTag extends Model
{
    protected $fillable = ['name','slug','color'];
    public function customers() { return $this->belongsToMany(Customer::class, 'customer_crm_tag'); }
}
