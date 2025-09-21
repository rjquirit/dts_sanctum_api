<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocWithLatestRoute extends Model
{
    // The DB VIEW name
    protected $table = 'vw_docs_latest_route';

    // Primary key from dts_docs
    protected $primaryKey = 'doc_id';

    // Views are read-only; avoid assuming incrementing behaviour
    public $incrementing = false;
    protected $keyType = 'int';

    // The view does not contain Laravel's created_at/updated_at by default
    public $timestamps = false;

    // Allow mass-reading but this model is effectively read-only (no writes to view).
    // If you want to prevent accidental writes, change to guarded = ['*'].
    protected $guarded = [];

    /**
     * Casts
     */
    protected $casts = [
        'doc_id' => 'integer',
        'track_issuedby_userid' => 'integer',
        'doc_type_id' => 'integer',
        'tempdocs_id' => 'integer',
        'origin_userid' => 'integer',
        'origin_school_id' => 'integer',
        'origin_section' => 'integer',
        'receiving_section' => 'integer',
        'acceptedby_userid' => 'integer',
        'acct_amount' => 'decimal:2',
        'done' => 'boolean',
        'datetime_posted' => 'datetime:Y-m-d H:i:s',
        'datetime_accepted' => 'datetime:Y-m-d H:i:s',
        'datetime_updated' => 'datetime:Y-m-d H:i:s',
        'tags' => 'array',
        'additional_receivers' => 'array',

        // route side
        'action_id' => 'integer',
        'document_id' => 'integer',
        'previous_route_id' => 'integer',
        'route_fromuser_id' => 'integer',
        'route_fromsection_id' => 'integer',
        'route_tosection_id' => 'integer',
        'route_touser_id' => 'integer',
        'receivedby_id' => 'integer',
        'actedby_id' => 'integer',
        'updatedby_id' => 'integer',
        'active' => 'integer',
        'datetime_forwarded' => 'datetime',
        'datetime_route_accepted' => 'datetime',
        'actions_datetime' => 'datetime',
        'def_datetime' => 'datetime',
    ];

    /**
     * Relationships
     */

    // doc type
    public function doctype(): BelongsTo
    {
        return $this->belongsTo(Doctypes::class, 'doc_type_id', 'doctype_id');
    }

    // origin section
    public function origin_section(): BelongsTo
    {
        return $this->belongsTo(Sections::class, 'origin_section', 'section_id');
    }

    // origin office / school
    public function origin_office(): BelongsTo
    {
        return $this->belongsTo(Offices::class, 'origin_school_id', 'sch_id');
    }

    /**
     * All routes for the document (full history)
     * 
     * Correctly uses document_id (foreign key on dts_docroutes)
     * and local key doc_id from dts_docs.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(DocRoutes::class, 'document_id', 'doc_id');
    }

    /**
     * Convenience relation to get the latest route record.
     * Uses latestOfMany ordering by datetime_forwarded (matches the view logic).
     */
    public function latestRoute(): HasOne
    {
        return $this->hasOne(DocRoutes::class, 'document_id', 'doc_id')
                    ->latestOfMany('datetime_forwarded');
    }

    /**
     * Optional: scope that eager-loads the latestRoute & some relations
     */
    public function scopeWithCoreRelations($query)
    {
        return $query->with(['doctype', 'origin_section', 'origin_office', 'latestRoute']);
    }
}
