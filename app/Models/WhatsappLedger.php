<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WhatsappLedger extends Model
{
    protected $table = 'whatsapp_ledger';

    protected $fillable = [
        'tipo', 'valor', 'descricao', 'destinatario', 'mensagem_tipo', 'registado_por',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public const CUSTO_POR_MENSAGEM = 89.00;

    public function registadoPor()
    {
        return $this->belongsTo(User::class, 'registado_por');
    }

    /**
     * Saldo actual = soma de créditos menos soma de débitos.
     * Calculado sempre a partir do extrato (sem campo "saldo" guardado à parte),
     * para nunca poder desalinhar do histórico real.
     */
    public static function saldoAtual(): float
    {
        $creditos = static::where('tipo', 'credito')->sum('valor');
        $debitos  = static::where('tipo', 'debito')->sum('valor');

        return (float) $creditos - (float) $debitos;
    }

    /**
     * Debita o custo de uma mensagem, se houver saldo suficiente.
     * Retorna false sem debitar nada quando o saldo é insuficiente —
     * quem chama deve tratar isso como "não enviar".
     */
    public static function debitarMensagem(string $destinatario, string $mensagemTipo, ?string $descricao = null): bool
    {
        return DB::transaction(function () use ($destinatario, $mensagemTipo, $descricao) {
            // lockForUpdate evita corrida entre dois envios em simultâneo lerem
            // o mesmo saldo "quase a zero" e ambos passarem a validação.
            $creditos = static::where('tipo', 'credito')->lockForUpdate()->sum('valor');
            $debitos  = static::where('tipo', 'debito')->lockForUpdate()->sum('valor');
            $saldo    = (float) $creditos - (float) $debitos;

            if ($saldo < self::CUSTO_POR_MENSAGEM) {
                return false;
            }

            static::create([
                'tipo'          => 'debito',
                'valor'         => self::CUSTO_POR_MENSAGEM,
                'descricao'     => $descricao ?? 'Mensagem WhatsApp enviada',
                'destinatario'  => $destinatario,
                'mensagem_tipo' => $mensagemTipo,
            ]);

            return true;
        });
    }

    public static function carregar(float $valor, string $descricao, ?int $registadoPor = null): self
    {
        return static::create([
            'tipo'          => 'credito',
            'valor'         => $valor,
            'descricao'     => $descricao,
            'registado_por' => $registadoPor,
        ]);
    }
}
