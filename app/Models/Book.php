<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as Querybuilder;

class Book extends Model
{
    use HasFactory;

    //lazy loads the relation
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where("title", "like", "%$title%");
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query
            ->withCount([
                'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
            ])
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRating(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withAvg([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ], 'rating')->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReviews(Builder $query, $minReviews): Builder|Querybuilder {
        //use `having`for querying the return of aggregate functions. `Where wouldn't work in here. You could use subqueries too.
        return $query->having('reviews_count', '>=', $minReviews);
    }

    private function dateRangeFilter(Builder $q, $from = null, $to = null)
    {
        if ($from && !$to) {
            $q->where('created_at', '>=', $from);
        } elseif (!$from && $to) {
            $q->where('created_at', '<=', $to);
        } elseif ($from && $to) {
            $q->whereBetween('created_at', [$from, $to]);
        }
    }
}
