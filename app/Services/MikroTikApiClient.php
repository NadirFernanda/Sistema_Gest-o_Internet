<?php

namespace App\Services;

/**
 * RouterOS API binary protocol client.
 *
 * Supports RouterOS 6.43+ (plain password) and older (MD5 challenge).
 * Port 8728 = plain; 8729 = TLS (not implemented here).
 */
class MikroTikApiClient
{
    private mixed $socket = null;
    private bool $connected = false;

    public function __construct(
        private readonly string $host,
        private readonly int    $port    = 8728,
        private readonly int    $timeout = 10,
    ) {}

    public function connect(string $username, string $password): void
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            throw new \RuntimeException(
                "MikroTik: não foi possível ligar a {$this->host}:{$this->port} — $errstr (errno $errno)"
            );
        }

        stream_set_timeout($this->socket, $this->timeout);
        $this->connected = true;
        $this->login($username, $password);
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            try { $this->writeSentence(['/quit']); } catch (\Throwable) {}
            fclose($this->socket);
            $this->socket    = null;
            $this->connected = false;
        }
    }

    public function isConnected(): bool
    {
        return $this->connected && is_resource($this->socket);
    }

    /**
     * Send a RouterOS command and return all response sentences.
     *
     * @param  string  $command  e.g. '/ip/hotspot/user/print'
     * @param  array   $params   attribute pairs  ['name' => 'foo', 'disabled' => 'no']
     * @param  array   $queries  query filters    ['name' => 'foo']
     */
    public function command(string $command, array $params = [], array $queries = []): array
    {
        $words = [$command];

        foreach ($params as $key => $value) {
            $words[] = "=$key=$value";
        }
        foreach ($queries as $key => $value) {
            $words[] = "?$key=$value";
        }

        $this->writeSentence($words);
        return $this->readResponse();
    }

    // ─── Auth ────────────────────────────────────────────────────────────────

    private function login(string $username, string $password): void
    {
        // RouterOS 6.43+: plain password
        $resp = $this->command('/login', ['name' => $username, 'password' => $password]);

        if ($this->isTrap($resp)) {
            // Older RouterOS: challenge/response (MD5)
            if (isset($resp[0]['=ret'])) {
                $challenge = $resp[0]['=ret'];
                $md5       = md5("\x00" . $password . pack('H*', $challenge));
                $resp2     = $this->command('/login', ['name' => $username, 'response' => '00' . $md5]);

                if ($this->isTrap($resp2)) {
                    throw new \RuntimeException(
                        'MikroTik: autenticação falhada — ' . ($resp2[0]['=message'] ?? 'erro desconhecido')
                    );
                }
                return;
            }
            throw new \RuntimeException(
                'MikroTik: autenticação falhada — ' . ($resp[0]['=message'] ?? 'erro desconhecido')
            );
        }
    }

    private function isTrap(array $response): bool
    {
        return !empty($response) && ($response[0]['type'] ?? '') === '!trap';
    }

    // ─── Write ───────────────────────────────────────────────────────────────

    private function writeSentence(array $words): void
    {
        foreach ($words as $word) {
            $this->writeWord($word);
        }
        $this->writeWord(''); // end-of-sentence marker
    }

    private function writeWord(string $word): void
    {
        $len = strlen($word);
        $this->writeLength($len);
        if ($len > 0) {
            fwrite($this->socket, $word);
        }
    }

    private function writeLength(int $len): void
    {
        if ($len < 0x80) {
            fwrite($this->socket, chr($len));
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            fwrite($this->socket, chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            fwrite($this->socket,
                chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        } elseif ($len < 0x10000000) {
            $len |= 0xE0000000;
            fwrite($this->socket,
                chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) .
                chr(($len >>  8) & 0xFF) . chr($len & 0xFF));
        } else {
            fwrite($this->socket,
                chr(0xF0) .
                chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) .
                chr(($len >>  8) & 0xFF) . chr($len & 0xFF));
        }
    }

    // ─── Read ────────────────────────────────────────────────────────────────

    private function readResponse(): array
    {
        $sentences = [];
        $current   = [];

        while (true) {
            $word = $this->readWord();

            if ($word === '') {
                // End of sentence
                if (!empty($current)) {
                    $sentences[] = $current;
                    $current     = [];
                }
                continue;
            }

            if ($word === '!done') {
                if (!empty($current)) {
                    $sentences[] = $current;
                }
                $sentences[] = ['type' => '!done'];
                break;
            }

            if (in_array($word, ['!re', '!trap', '!fatal'], true)) {
                if (!empty($current)) {
                    $sentences[] = $current;
                }
                $current = ['type' => $word];

                if ($word === '!fatal') {
                    // Read remaining words of the fatal sentence
                    while (($w = $this->readWord()) !== '') {
                        $this->parseWordInto($w, $current);
                    }
                    $sentences[] = $current;
                    break;
                }
                continue;
            }

            $this->parseWordInto($word, $current);
        }

        return $sentences;
    }

    private function parseWordInto(string $word, array &$target): void
    {
        if (str_starts_with($word, '=')) {
            $eq = strpos($word, '=', 1);
            if ($eq !== false) {
                $key          = substr($word, 1, $eq - 1);
                $val          = substr($word, $eq + 1);
                $target[$key] = $val;           // without leading '='
                $target["=$key"] = $val;        // with leading '=' (RouterOS convention)
            }
        }
    }

    private function readWord(): string
    {
        $len = $this->readLength();
        if ($len === 0) {
            return '';
        }

        $buf = '';
        $rem = $len;
        while ($rem > 0) {
            $chunk = fread($this->socket, $rem);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $buf .= $chunk;
            $rem -= strlen($chunk);
        }
        return $buf;
    }

    private function readLength(): int
    {
        $raw = fread($this->socket, 1);
        if ($raw === false || $raw === '') {
            throw new \RuntimeException('MikroTik: ligação perdida durante leitura (EOF/timeout)');
        }
        $b = ord($raw);

        if (($b & 0x80) === 0) {
            return $b;
        }
        if (($b & 0xC0) === 0x80) {
            $b2 = ord(fread($this->socket, 1));
            return (($b & ~0x80) << 8) | $b2;
        }
        if (($b & 0xE0) === 0xC0) {
            $d = fread($this->socket, 2);
            return (($b & ~0xC0) << 16) | (ord($d[0]) << 8) | ord($d[1]);
        }
        if (($b & 0xF0) === 0xE0) {
            $d = fread($this->socket, 3);
            return (($b & ~0xE0) << 24) | (ord($d[0]) << 16) | (ord($d[1]) << 8) | ord($d[2]);
        }
        $d = fread($this->socket, 4);
        return (ord($d[0]) << 24) | (ord($d[1]) << 16) | (ord($d[2]) << 8) | ord($d[3]);
    }
}
