<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Filament\Panel;

class User extends Authenticatable implements HasAvatar, FilamentUser
{
    use Notifiable, HasRoles;

    // Nombre de la tabla
    protected $table = 'users';

    // ✅ NO redefinimos primaryKey: Laravel asume 'id' automáticamente

    // Atributos asignables en masa
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'phone',
        'address',
        'status',
        'custom_fields',
        'avatar_url',
        'document_number',

    ];

    // Atributos ocultos
    protected $hidden = [
        'remember_token',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
        ];
    }

    public $timestamps = true;

    // Método para Filament: control de acceso al panel
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active'; // ✅ Usar 'status', no 'estado'
    }

    // Método para obtener avatar en Filament
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function scopeProfessors($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'docente'); // Ajusta al nombre de tu rol
        });
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

