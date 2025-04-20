<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Observers\RiversObserver;
use Workbench\Database\Factories\UserFactory;

#[ObservedBy(RiversObserver::class)]
class User extends Authenticatable implements CreatesRaft
{
    use HasFactory, Notifiable;

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

    public function createRaft(): array
    {
        return [
            'modelClass' => get_class($this),
            'modelId' => $this->getKey(),
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return new UserFactory;
    }
}
