<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docroutes extends Model
{
    protected $table="dts_docroutes";
    protected $primaryKey = 'action_id';
    public $timestamps = false;

    protected $casts = [
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

    protected $fillable=[
        'action_id',
        'document_id',
        'previous_route_id',
        'route_fromuser_id',
        'route_from',
        'route_fromsection_id',
        'route_fromsection',
        'route_tosection_id',
        'route_tosection',
        'route_touser_id',
        'route_purpose',
        'fwd_remarks',
        'datetime_forwarded',
        'datetime_route_accepted',
        'receivedby_id',
        'received_by',
        'accepting_remarks',
        'actions_datetime',
        'actions_taken',
        'actedby_id',
        'acted_by',
        'doc_copy',
        'out_released_to',
        'logbook_page',
        'route_accomplished',
        'end_remarks',
        'def_reason',
        'def_datetime',
        'duplicate',
        'del_reason',
        'updatedby_id',
        'active',
    ];

        protected $attributes = [
        'action_id' => 0,
        'document_id' => 0,
        'previous_route_id' => 0,
        'route_fromuser_id' => 0,
        'route_from' => '',
        'route_fromsection_id' => 0,
        'route_fromsection' => '',
        'route_tosection_id' => 0,
        'route_tosection' => '',
        'route_touser_id' => 0,
        'route_purpose' => '',
        'fwd_remarks' => '',
        'datetime_forwarded' => '1970-01-01 00:00:00',
        'datetime_route_accepted' => '1970-01-01 00:00:00',
        'receivedby_id' => 0,
        'received_by' => '',
        'accepting_remarks' => '',
        'actions_datetime' => '1970-01-01 00:00:00',
        'actions_taken' => '',
        'actedby_id' => 0,
        'acted_by' => '',
        'doc_copy' => 0,
        'out_released_to' => '',
        'logbook_page' => '',
        'route_accomplished' => 0,
        'end_remarks' => '',
        'def_reason' => '',
        'def_datetime' => '1970-01-01 00:00:00',
        'duplicate' => 0,
        'del_reason' => '',
        'updatedby_id' => 0,
        'active' => 1,
    ];

    public function document()
    {
        return $this->belongsTo(Docmain::class, 'document_id', 'doc_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'route_fromuser_id', 'id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'route_touser_id', 'id');
    }

    public function fromSection()
    {
        return $this->belongsTo(Sections::class, 'route_fromsection_id', 'section_id');
    }

    public function toSection()
    {
        return $this->belongsTo(Sections::class, 'route_tosection_id', 'section_id');
    }
}
