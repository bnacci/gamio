<?php
namespace App\Models;

use Bnacci\Gamio\Traits\Leveable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticable
{
    use Notifiable, HasFactory, Leveable;

    protected $guarded = [];
    protected $table   = "users";
}
