<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $fillable = ['ticket_id', 'message', 'is_admin', 'author_name'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
