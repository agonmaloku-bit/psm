<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractExpired extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $expiredDate;

    public function __construct($contract, $expiredDate)
    {
        $this->contract = $contract;
        $this->expiredDate = $expiredDate;
    }

    public function build()
    {
        return $this->subject('Contract Expired - ' . ($this->contract->name ?? '#' . $this->contract->id))
            ->view('email.contract_expired');
    }
}
