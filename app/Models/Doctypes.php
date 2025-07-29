<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Docmain;

class Doctypes extends Model
{
    protected $table="dts_docstype";
    protected $fillable = [
        'doctype_description','display_sequence','display_section','with_form','active'
    ];
    public function docmains()
    {
        return $this->hasMany(Docmain::class, 'doctype_id', 'doc_type_id'); 
    }
}
