<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratory extends Model
{
    use HasFactory;

    protected $table = 'laboratories';

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'user_id',
    ];

    public function setAttribute($key, $value)
    {
        if ($key === 'product_ids') {
            return;
        }

        return parent::setAttribute($key, $value);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $productIds = request()->input('product_ids', []);
            if (! empty($productIds)) {
                Product::where('laboratory_id', $model->id)->update(['laboratory_id' => null]);
                Product::whereIn('id', $productIds)->update(['laboratory_id' => $model->id]);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'laboratory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'laboratory_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
