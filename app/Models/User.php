<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use App\Models\Sections;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'dts_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'position',
        'division_code',
        'section_id' ?? 1,
        'office_nameid' ?? 1,
        'user_type' ?? 1,
        'system_admin' ?? 0,
        'approved' ?? 1,
        'active' ?? 1,
        'contactnumber',
        'google_id',
        'google_token',
        'google_refresh_token',
        'avatar',
        'role',
        'security_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'security_code'
    ];

    protected $appends = ['decrypted_name'];


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

    // Encrypt sensitive data before saving
    public function setAttribute($key, $value)
    {
        if (in_array($key, ['name', 'position'])) {
            $value = Crypt::encryptString($value);
        }
        return parent::setAttribute($key, $value);
    }

    // Decrypt sensitive data when accessing
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (in_array($key, ['name', 'position']) && !empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Sections::class, 'section_id', 'section_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Offices::class,'division_code',  'sch_id');
    }

    public function encryptData($plaintext, $key) {
        $method = 'aes-256-gcm';
        $key = hash('sha256', $key, true);
        $iv = substr(hash('sha256', 'fixed_iv_value'), 0, 12);
        $tag = '';

        $encrypted = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);

        return bin2hex($tag . $encrypted);
    }

    public function decryptData($encryptedData, $key ) {
        $method = 'aes-256-gcm';
        $key = hash('sha256', $key, true);
        $iv = substr(hash('sha256', 'fixed_iv_value'), 0, 12);

        $decoded = hex2bin($encryptedData);
        $tag = substr($decoded, 0, 16);
        $ciphertext = substr($decoded, 16);

        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }

    public function getDecryptedNameAttribute()
    {
        return $this->name;
    }

    public function get_section()
    {
        return $this->belongsTo(Offices::class, 'section_id', 'section_id');
    }
}
