<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando o saldo pré-pago de mensagens WhatsApp não chega para
 * cobrir o custo de um novo envio. O envio é recusado antes de sequer
 * contactar o Evolution API.
 */
class SaldoWhatsAppInsuficienteException extends RuntimeException
{
}
