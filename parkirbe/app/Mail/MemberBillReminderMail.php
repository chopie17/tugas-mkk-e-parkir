<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Member;
use App\Models\MemberBill;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberBillReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Member $member,
        public readonly MemberBill $bill,
    ) {}

    public function envelope(): Envelope
    {
        $period = $this->bill->period ? $this->bill->period->translatedFormat('F Y') : date('F Y');
        return new Envelope(
            subject: "[E-Parkir Plaza Andalas] Pengingat Iuran Member Parkir Periode {$period}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.member-bill-reminder',
        );
    }
}

