<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCrmNote extends Model
{
    protected $fillable = ['customer_id','author_user_id','body','is_pinned'];
    protected function casts(): array { return ['is_pinned'=>'boolean']; }
    public function author() { return $this->belongsTo(User::class, 'author_user_id'); }
}
