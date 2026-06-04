<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\TicketMensagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['cliente', 'ultimaMensagem'])
            ->latest();

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }
        if ($categoria = $request->query('categoria')) {
            $query->where('categoria', $categoria);
        }
        if ($prioridade = $request->query('prioridade')) {
            $query->where('prioridade', $prioridade);
        }
        if ($search = trim($request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('assunto', 'like', "%{$search}%")
                  ->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->paginate(25)->withQueryString();

        $totais = [
            'aberto'       => Ticket::where('estado', 'Aberto')->count(),
            'em_andamento' => Ticket::where('estado', 'Em Andamento')->count(),
            'resolvido'    => Ticket::where('estado', 'Resolvido')->count(),
            'fechado'      => Ticket::where('estado', 'Fechado')->count(),
        ];

        return view('tickets.index', compact('tickets', 'totais') + [
            'estadoFiltro'    => $request->query('estado', ''),
            'categoriaFiltro' => $request->query('categoria', ''),
            'prioridadeFiltro'=> $request->query('prioridade', ''),
            'search'          => $request->query('search', ''),
        ]);
    }

    public function create(Request $request)
    {
        $clientes = Cliente::orderBy('nome')->get(['id', 'nome', 'contato']);
        $clienteId = $request->query('cliente_id');
        return view('tickets.create', compact('clientes', 'clienteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'  => 'nullable|exists:clientes,id',
            'assunto'     => 'required|string|max:255',
            'categoria'   => 'required|in:Técnico,Cobrança,Equipamento,Plano,Outro',
            'prioridade'  => 'required|in:Baixa,Normal,Alta,Urgente',
            'mensagem'    => 'required|string|max:5000',
        ]);

        $ticket = Ticket::create([
            'cliente_id' => $data['cliente_id'] ?? null,
            'user_id'    => Auth::id(),
            'assunto'    => $data['assunto'],
            'categoria'  => $data['categoria'],
            'prioridade' => $data['prioridade'],
            'estado'     => 'Aberto',
        ]);

        TicketMensagem::create([
            'ticket_id'  => $ticket->id,
            'autor_tipo' => 'admin',
            'user_id'    => Auth::id(),
            'mensagem'   => $data['mensagem'],
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket #' . $ticket->id . ' criado com sucesso.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['cliente', 'mensagens.user', 'user']);
        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate(['mensagem' => 'required|string|max:5000']);

        TicketMensagem::create([
            'ticket_id'  => $ticket->id,
            'autor_tipo' => 'admin',
            'user_id'    => Auth::id(),
            'mensagem'   => $request->mensagem,
        ]);

        if ($ticket->estado === 'Aberto') {
            $ticket->update(['estado' => 'Em Andamento']);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Resposta enviada.');
    }

    public function updateEstado(Request $request, Ticket $ticket)
    {
        $request->validate(['estado' => 'required|in:Aberto,Em Andamento,Resolvido,Fechado']);
        $ticket->update(['estado' => $request->estado]);
        return redirect()->route('tickets.show', $ticket)->with('success', 'Estado actualizado.');
    }

    public function updatePrioridade(Request $request, Ticket $ticket)
    {
        $request->validate(['prioridade' => 'required|in:Baixa,Normal,Alta,Urgente']);
        $ticket->update(['prioridade' => $request->prioridade]);
        return redirect()->route('tickets.show', $ticket)->with('success', 'Prioridade actualizada.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado.');
    }
}
