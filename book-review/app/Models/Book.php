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

    public function scopeMinReview(Builder $query, int $minReview) : Builder
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

    public function scopePopularLastMonth(Builder $query) : Builder
    {
        return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minreview(2);
    }

    public function scopePopularLast6months(Builder $query) : Builder
    {
        return $query->popular(now()->subMonths(6), now())
            ->highestRated(now()->subMonths(6), now())
            ->minReview(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query) : Builder
    {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(6), now())
            ->minReview(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query) : Builder
    {
        return $query->highestRated(now()->subMonths(6), now())
            ->popular(now()->subMonths(6), now())
            ->minReview(5);
    }

}