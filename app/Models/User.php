<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, Notifiable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_2fa_enable',
        'provider',
        'provider_id',
        'status',
        'email_verified_at',
        'remember_token',
        'slug',
        'image',
        'role',
        'terms_accepted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    public function blogComments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }

    public function webinars()
    {
        return $this->belongsToMany(Webinar::class, 'webinar_registrations');
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        // Génération de l'URL signée avec une validité de 10 minutes
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Carbon\Carbon::now()->addMinutes(10),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        $this->notify(new \App\Notifications\QueuedVerifyEmail($url)); // On passe l'URL au constructeur
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\QueuedResetPassword($token));
    }
}
