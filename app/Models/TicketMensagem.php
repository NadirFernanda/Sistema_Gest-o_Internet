<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMensagem extends Model
{
    protected $fillable = [
        'ticket_id',
        'autor_tipo',
        'user_id',
        'mensagem',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function isAdmin(): bool
    {
        return $this->autor_tipo === 'admin';
    }
}
