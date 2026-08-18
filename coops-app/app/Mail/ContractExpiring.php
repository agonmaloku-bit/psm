<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractExpiring extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $daysLeft;
    public $expireDate;

    public function __construct($contract, $daysLeft, $expireDate)
    {
        $this->contract = $contract;
        $this->daysLeft = $daysLeft;
        $this->expireDate = $expireDate;
    }

    public function build()
    {
        return $this->subject('Contract Expiring - ' . ($this->contract->name ?? '#' . $this->contract->id) . ' (' . $this->daysLeft . ' days left)')
            ->view('email.contract_expiring');
    }
}
