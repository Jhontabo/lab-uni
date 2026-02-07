<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'rejection_reason',
        'schedule_id',
        'user_id',
        'laboratory_id',
        'project_type',
        'academic_program',
        'semester',
        'applicants',
        'research_name',
        'advisor',
        'products',
        'start_at',
        'end_at',
        'color',
        'status',
    ];

    // Constantes de estado
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_REJECTED = 'rejected';

    protected $casts = [
        'products' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    // Scopes de estado
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Helpers de estado
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // Relaciones
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['start_at', 'end_at', 'status'])
            ->using(BookingProduct::class) // Opcional: modelo pivote personalizado
            ->withTimestamps();
    }
}
