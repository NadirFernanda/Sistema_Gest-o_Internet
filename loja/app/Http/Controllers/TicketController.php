<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'nullable|email|max:150',
            'phone'    => 'nullable|string|max:30',
            'subject'  => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(Ticket::CATEGORIES)),
            'message'  => 'required|string|max:5000',
        ], [
            'name.required'     => 'O nome é obrigatório.',
            'subject.required'  => 'O assunto é obrigatório.',
            'message.required'  => 'A mensagem é obrigatória.',
        ]);

        $ticket = Ticket::create([
            'ref'      => Ticket::generateRef(),
            'token'    => Str::random(48),
            'name'     => $validated['name'],
            'email'    => $validated['email'] ?? null,
            'phone'    => $validated['phone'] ?? null,
            'subject'  => $validated['subject'],
            'category' => $validated['category'],
            'message'  => $validated['message'],
            'status'   => Ticket::STATUS_OPEN,
            'priority' => Ticket::PRIORITY_NORMAL,
        ]);

        return redirect()->route('tickets.show', $ticket->token)
            ->with('success', 'Ticket ' . $ticket->ref . ' criado com sucesso. Guarde este link para acompanhar o estado.');
    }

    public function show(string $token)
    {
        $ticket = Ticket::where('token', $token)->firstOrFail();
        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, string $token)
    {
        $ticket = Ticket::where('token', $token)->firstOrFail();

        if (! $ticket->isOpen()) {
            return back()->withErrors(['message' => 'Este ticket está fechado e não aceita mais respostas.']);
        }

        $request->validate(['message' => 'required|string|max:5000']);

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'message'     => $request->message,
            'is_admin'    => false,
            'author_name' => $ticket->name,
        ]);

        if ($ticket->status === Ticket::STATUS_RESOLVED) {
            $ticket->update(['status' => Ticket::STATUS_IN_PROGRESS]);
        }

        return back()->with('success', 'Resposta enviada.');
    }
}
