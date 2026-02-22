<?php
// Simple Laravel bootstrap script to inspect mail config and optionally send a test email.
// Usage:
//   php tools/check_mail.php            # prints mail config
//   php tools/check_mail.php --send-to=you@example.com  # attempts to send test email

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function eprint($msg) { echo $msg . PHP_EOL; }

eprint('config.mail.default=' . (string) config('mail.default'));
eprint('env.MAIL_MAILER=' . (string) env('MAIL_MAILER'));
eprint('config.mail.from=' . (string) config('mail.from.address'));
eprint('config.mail.mailers.smtp=' . json_encode(config('mail.mailers.smtp')));

$sendTo = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--send-to=') === 0) {
        $sendTo = substr($arg, strlen('--send-to='));
    }
}

if ($sendTo) {
    eprint("Attempting to send test email to {$sendTo}...");
    try {
        \Illuminate\Support\Facades\Mail::raw('Teste SMTP', function ($m) use ($sendTo) {
            $m->to($sendTo)->subject('Teste SMTP from check_mail.php');
        });
        eprint('Mail::raw() called — check logs for transport activity.');
    } catch (\Throwable $e) {
        eprint('Error sending mail: ' . $e->getMessage());
        eprint($e->getTraceAsString());
    }
}

eprint('Done.');
