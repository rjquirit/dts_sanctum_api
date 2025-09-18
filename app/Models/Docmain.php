<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctypes;
use App\Models\Sections;
use App\Models\Offices;
use App\Models\DocRoutes;

class Docmain extends Model
{
    protected $table="dts_docs";
    protected $primaryKey = 'doc_id';
    public $timestamps = false;
    protected static $totals;

    protected $casts = [
        'doc_id' => 'integer',
        'track_issuedby_userid' => 'integer',
        'doc_type_id' => 'integer',
        'origin_userid' => 'integer',
        'origin_section' => 'integer',
        'receiving_section' => 'integer',
        'receiving_userid' => 'integer',
        'active' => 'integer',
        'datetime_posted' => 'datetime',
        'datetime_accepted' => 'datetime',
        'datetime_updated' => 'datetime',
    ];

    protected $fillable=[
        'doc_tracking',
        'track_issuedby_userid',
        'doc_type_id',
        'tempdocs_id',
        'docs_description',
        'origin_fname',
        'origin_userid',
        'origin_school_id',
        'origin_school',
        'origin_section',
        'receiving_section',
        'receiving_userid',
        'actions_needed',
        'datetime_posted',
        'datetime_accepted',
        'acceptedby_userid',
        'acct_dvnum',
        'acct_payee',
        'acct_particulars',
        'acct_amount',
        'final_actions_made',
        'done',
        'datetime_updated',
        'updatedby_id',
        'archive_id',
        'active',
        'deactivate_reason',
        'tags',
        'additional_receivers',
    ];

    // Default values
    protected $attributes = [
    'tempdocs_id' => 0,
    'origin_fname' => '',
    'origin_school_id' => 1,
    'datetime_accepted' => null,
    'acceptedby_userid' => 0,
    'acct_dvnum' => '',
    'acct_payee' => '',
    'acct_particulars' => '',
    'acct_amount' => 0,
    'final_actions_made' => '',
    'updatedby_id' => 0,
    'archive_id' => 0,
    'active' => 1,
    'deactivate_reason' => '',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $year = date('y');

            // Generate doc_tracking (year-doc_id)
            $lastId = self::max('doc_id') ?? 0;
            $nextId = $lastId + 1;
            $model->doc_tracking = sprintf('%s-%d', $year, $nextId);

            // Generate doc_tracking (year-division_code-increment)
            if (auth()->check()) {
                $divisionCode = auth()->user()->division_code ?? '01';

                // Get the last increment number from existing tracking codes
                $lastDoc = self::where('doc_tracking', 'like', "{$year}-{$divisionCode}-%")
                    ->orderBy('doc_id', 'desc')
                    ->first();

                $lastIncrement = 0;
                if ($lastDoc) {
                    $parts = explode('-', $lastDoc->doc_tracking);
                    $lastIncrement = (int) end($parts);
                }

                $nextIncrement = $lastIncrement + 1;
                $model->doc_tracking = sprintf('%s-%s-%03d', $year, $divisionCode, $nextIncrement);
            }
        });
    }

    public function doctype()
    {
        return $this->belongsTo(Doctypes::class, 'doc_type_id', 'doctype_id');
    }

    public function origin_section()
    {
        return $this->belongsTo(Sections::class, 'origin_section', 'section_id');
    }

    public function origin_office()
    {
        return $this->belongsTo(Offices::class, 'origin_school_id' , 'sch_id');
    }

    public function routes()
    {
        return $this->hasMany(DocRoutes::class, 'action_id', 'doc_id');
    }
    public function latestRoute()
    {
        return $this->hasOne(DocRoutes::class, 'document_id', 'doc_id')->latestOfMany('action_id'); 
    }
}
