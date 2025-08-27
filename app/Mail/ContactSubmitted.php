<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactSubmitted extends Mailable // implements ShouldQueue (optional)
{
    use Queueable, SerializesModels;

    public ContactMessage $contact;

    public function __construct(ContactMessage $contact)
    {
        $this->contact = $contact;
    }

    public function build()
    {
        return $this->subject('New Contact Message: ' . $this->contact->subject ?: 'No subject')
            ->markdown('mails.contact.submitted');
    }
}
