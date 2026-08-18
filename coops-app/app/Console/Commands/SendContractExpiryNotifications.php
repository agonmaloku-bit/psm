<?php

namespace App\Console\Commands;

use App\Mail\ContractExpired;
use App\Mail\ContractExpiring;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContractExpiryNotifications extends Command
{
    protected $signature = 'contracts:notify-expiry';
    protected $description = 'Send email notifications for expiring and newly expired contracts';

    public function handle()
    {
        $today = Carbon::today();
        $oneMonthFromNow = Carbon::today()->addMonth();

        // --- Expiring contracts: deadline_to between today and +30 days ---
        $expiringContracts = Contract::with(['responsiblePerson', 'createdBy'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 4) // not canceled
            ->where('deadline_to', '>=', $today)
            ->where('deadline_to', '<=', $oneMonthFromNow)
            ->get();

        $expiringCount = 0;
        foreach ($expiringContracts as $contract) {
            $daysLeft = $today->diffInDays(Carbon::parse($contract->deadline_to));
            $expireDate = Carbon::parse($contract->deadline_to)->format('d.m.Y');

            $recipients = $this->getRecipients($contract);
            foreach ($recipients as $email) {
                try {
                    Mail::to($email)->send(new ContractExpiring($contract, $daysLeft, $expireDate));
                    $expiringCount++;
                } catch (\Throwable $e) {
                    Log::warning("ContractExpiring mail to {$email} failed: " . $e->getMessage());
                }
            }
        }

        // --- Expired contracts: deadline_to was yesterday (just expired) ---
        $yesterday = Carbon::yesterday();
        $expiredContracts = Contract::with(['responsiblePerson', 'createdBy'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 4) // not canceled
            ->whereDate('deadline_to', $yesterday)
            ->get();

        $expiredCount = 0;
        foreach ($expiredContracts as $contract) {
            $expiredDate = Carbon::parse($contract->deadline_to)->format('d.m.Y');

            $recipients = $this->getRecipients($contract);
            foreach ($recipients as $email) {
                try {
                    Mail::to($email)->send(new ContractExpired($contract, $expiredDate));
                    $expiredCount++;
                } catch (\Throwable $e) {
                    Log::warning("ContractExpired mail to {$email} failed: " . $e->getMessage());
                }
            }
        }

        $this->info("Sent {$expiringCount} expiring notifications for " . $expiringContracts->count() . " contracts.");
        $this->info("Sent {$expiredCount} expired notifications for " . $expiredContracts->count() . " contracts.");

        return 0;
    }

    /**
     * Collect unique email recipients: responsible person + creator.
     */
    private function getRecipients(Contract $contract): array
    {
        $emails = [];

        if ($contract->responsiblePerson && $contract->responsiblePerson->email) {
            $emails[] = $contract->responsiblePerson->email;
        }

        if ($contract->createdBy && $contract->createdBy->email && !in_array($contract->createdBy->email, $emails)) {
            $emails[] = $contract->createdBy->email;
        }

        return $emails;
    }
}
