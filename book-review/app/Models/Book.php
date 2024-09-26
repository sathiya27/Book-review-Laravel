<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;


    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title) : Builder
    {
        return $query->where('title', 'Like', '%' . $title . '%');
    }

    public function scopePopular(Builder $query, $from = null, $to = null) : Builder
    {
        return $query->withCount([
            'reviews' => fn(Builder $q)=> $this->dateRangeFilter($from, $to, $q)
        ])
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $query, $from = null, $to = null) : Builder
    {
        return $query->withAvg([
            'reviews'=> fn(Builder $q) => $this->dateRangeFilter($from, $to, $q)
        ], 'rating')
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReview(Builder $query, int $minReview)
    {
        return $query->having('reviews_count', '>=', $minReview);
    }

    private function dateRangeFilter($from, $to, Builder $q)
    {
        if($from && !$to){
            $q->where('created_at', '>=', $from);
        } elseif(!$from && $to){
            $q->where('created_at', '<=', $to);
        } elseif($from && $to){
            $q->whereBetween('created_at', [$from, $to]);
        }
    }
}