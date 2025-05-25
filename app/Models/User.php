<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Uitgecommentarieerd, dus verwijderd
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Representeert een gebruiker van de applicatie.
 * Dit model wordt gebruikt voor authenticatie en autorisatie.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */ // Specifieke factory hint voor IDE's
    use HasFactory, Notifiable;

    /**
     * De attributen die via mass assignment toegewezen mogen worden.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * De attributen die verborgen moeten worden bij serialisatie (bijv. voor JSON responses).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Definieert de attribute casting voor het model.
     * Deze methode retourneert een array met de cast-definities.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Cast naar Carbon instance; null indien niet geverifieerd
            'password' => 'hashed',          // Automatisch hashen van wachtwoorden bij toewijzing
        ];
    }
}