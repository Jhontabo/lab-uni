<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
  use HandlesAuthorization;

  public function viewAny(User $user): bool
  {
    return $user->hasRole('ADMIN') ||
      $user->hasPermissionTo('ver panel reservar espacios');
  }

  public function view(User $user, Booking $booking): bool
  {
    return $user->hasRole('ADMIN')
      || $user->hasPermissionTo('ver cualquier reservar espacio')
      || $booking->user_id === $user->id;
  }

  public function create(User $user): bool
  {
    return $user->hasRole('ADMIN') ||
      $user->hasPermissionTo('crear reservar espacio');
  }

  public function update(User $user, Booking $booking): bool
  {
    return $user->hasRole('ADMIN')
      || $user->hasPermissionTo('actualizar reservar espacio')
      || ($booking->user_id === $user->id && $booking->status === 'pending');
  }

  public function delete(User $user, Booking $booking): bool
  {
    return $user->hasRole('ADMIN')
      || $user->hasPermissionTo('eliminar reservar espacio')
      || ($booking->user_id === $user->id && $booking->status === 'pending');
  }
}
