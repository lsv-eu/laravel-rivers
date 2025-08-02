<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LsvEu\Rivers\Concerns\RiverRunnable;
use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Observers\RiversObserver;
use Workbench\App\Rivers\Rafts\UserRaft;
use Workbench\Database\Factories\UserFactory;

#[ObservedBy(RiversObserver::class)]
class User extends Authenticatable implements CreatesRaft
{
    use HasFactory, Notifiable, RiverRunnable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->using(Taggable::class)->whereType('user');
    }

    public function createRaft(): Raft
    {
        return new UserRaft(['modelId' => $this->getKey()]);
    }

    protected static function newFactory(): UserFactory
    {
        return new UserFactory;
    }
}
