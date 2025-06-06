<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="BookModel",
 *     title="Book Model",
 *     description="Represents a book",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int32",
 *         description="Book ID"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int32",
 *         description="User ID"
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         type="integer",
 *         format="int32",
 *         description="Category ID"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Book title"
 *     ),
 *     @OA\Property(
 *         property="author",
 *         type="string",
 *         description="Book author"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="integer",
 *         format="int32",
 *         description="Book price"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation date"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Update date"
 *     )
 * )
 */
class Book extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'category_id', 'title', 'author', 'price', ''];
    protected $hidden = ['verified'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ebook()
    {
        return $this->hasMany(Ebook::class);
    }

    public function bookUser()
    {
        return $this->hasOne(book_user::class);
    }
}
