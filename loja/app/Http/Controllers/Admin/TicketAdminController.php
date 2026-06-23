<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketAdminController extends Controller
{
    public function index(Request $request)
    {
        try {
            // ORDER BY explícito: prioridade por estado (open→in_progress→resolved→closed), depois data
            $statusOrder = DB::raw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'resolved' THEN 2 ELSE 3 END");

            $query = Ticket::orderBy($statusOrder)->orderByDesc('created_at');

            if ($status = $request->get('status')) {
                $query->where('status', $status);
            }
            if ($category = $request->get('category')) {
                $query->where('category', $category);
            }
            if ($priority = $request->get('priority')) {
                $query->where('priority', $priority);
            }
            if ($q = trim((string) $request->get('q', ''))) {
                $query->where(function ($qb) use ($q) {
                    $qb->where('ref', 'like', "%{$q}%")
                       ->orWhere('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%")
                       ->orWhere('subject', 'like', "%{$q}%");
                });
            }

            $tickets = $query->withCount('replies')->paginate(25)->withQueryString();

            $counts = [
                'open'        => Ticket::where('status', Ticket::STATUS_OPEN)->count(),
                'in_progress' => Ticket::where('status', Ticket::STATUS_IN_PROGRESS)->count(),
                'resolved'    => Ticket::where('status', Ticket::STATUS_RESOLVED)->count(),
                'closed'      => Ticket::where('status', Ticket::STATUS_CLOSED)->count(),
            ];
        } catch (\Throwable $e) {
            Log::error('TicketAdminController@index falhou: ' . $e->getMessage(), [
                'sql'   => method_exists($e, 'getSql') ? $e->getSql() : null,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        return view('admin.tickets.index', compact('tickets', 'counts'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load('replies');
        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string|max:5000']);

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'message'     => $request->message,
            'is_admin'    => true,
            'author_name' => 'AngolaWiFi Suporte',
        ]);

        if ($ticket->status === Ticket::STATUS_OPEN) {
            $ticket->update(['status' => Ticket::STATUS_IN_PROGRESS]);
        }

        return back()->with('success', 'Resposta enviada ao cliente.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status'      => 'required|in:open,in_progress,resolved,closed',
            'priority'    => 'nullable|in:low,normal,high,urgent',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $update = ['status' => $request->status];
        if ($request->filled('priority'))    $update['priority']    = $request->priority;
        if ($request->filled('admin_notes')) $update['admin_notes'] = $request->admin_notes;
        if ($request->status === Ticket::STATUS_RESOLVED) {
            $update['resolved_at'] = now();
        }

        $ticket->update($update);
        return back()->with('success', 'Ticket actualizado.');
    }
}
