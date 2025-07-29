<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offices extends Model
{
    //
    protected $table="schools";
    protected $fillable = [
        'sch_id',
        'school_code',
        'school_name',
        //'subject','quiz_description','score',
    ];
}
