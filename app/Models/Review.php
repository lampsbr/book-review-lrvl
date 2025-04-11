<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['review', 'rating'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    protected static function booted()
    {
        static::updated(fn(Review $r) => cache()->forget('book_' . $r->book_id));
        static::deleted(fn(Review $r) => cache()->forget('book_' . $r->book_id));
        static::created(fn(Review $r) => cache()->forget('book_' . $r->book_id));
    }
}
