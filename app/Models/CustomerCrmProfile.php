<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCrmProfile extends Model
{
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $fillable = ['customer_id','contraindications','important_warnings','preferred_user_id','updated_by_user_id'];

    public function preferredMaster() { return $this->belongsTo(User::class, 'preferred_user_id'); }
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by_user_id'); }
}
