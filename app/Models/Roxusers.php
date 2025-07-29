<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roxusers extends Model
{
    protected $table="rox_users";
    protected $fillable = [
        'fullname',
        'email',
        'contact_number',
        'password',
        'secured_pass',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'designation',
        'dts_admin',
        'dts_section_id',
        'dts_image_url',
        'staff_id',
        'station_id',
        'school_head',
        'ict_coordinator',
        'property_custodian',
        'user_type',
        'system_admin',
        'approved',
        'active',

    ];
}
