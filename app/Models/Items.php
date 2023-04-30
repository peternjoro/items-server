<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $fillable = ['item_name','desc','filename','user_id'];

    // Relationships
    // An Item must belong to a user - via user_id
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
