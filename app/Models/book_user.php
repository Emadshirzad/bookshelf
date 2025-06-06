<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class book_user extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'book_id',
        'count',
        'price',
        'sold_price',
        'release_date',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
