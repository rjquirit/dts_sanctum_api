<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Offices;

class Sections extends Model
{
    protected $table="dts_sections";
    protected $fillable = [
        'section_id', 'section_description', 'office_id', 'initial_receipt','public_view','hidden','active'
    ];

    public function get_division()
    {
        return $this->belongsTo(Offices::class, 'sch_id', 'office_id');
    }
}
