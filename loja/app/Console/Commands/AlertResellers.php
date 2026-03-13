<?php

namespace App\Console\Commands;

use App\Mail\ResellerAlertMail;
use App\Models\ResellerApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AlertResellers extends Command
{
    protected $signature   = 'resellers:alert';
    protected $description = 'Envia alertas de e-mail aos revendedores com manutenção em atraso ou meta mensal não atingida.';

    public function handle(): int
    {
        $approved = ResellerApplication::where('status', ResellerApplication::STATUS_APPROVED)->get();

        $maintenanceCount = 0;
        $targetCount      = 0;

        foreach ($approved as $application) {
            if ($application->maintenanceDueThisMonth()) {
                Mail::to($application->email)->send(new ResellerAlertMail($application, 'maintenance'));
                $maintenanceCount++;
            }

            if ($application->monthly_target_aoa > 0 && !$application->metMonthlyTarget()) {
                Mail::to($application->email)->send(new ResellerAlertMail($application, 'target'));
                $targetCount++;
            }
        }

        $this->info("Alertas enviados: {$maintenanceCount} manutenção, {$targetCount} meta.");

        return Command::SUCCESS;
    }
}
