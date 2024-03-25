<?php

namespace App\Listeners\Contact;

use App\Events\Contact\ContactSubmissionApproved;
use App\Mail\ContactedConfirmation;
use Illuminate\Support\Facades\Mail;

class SendContactedConfirmationMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContactSubmissionApproved $event): void
    {
        Mail::send(ContactedConfirmation::create($event->message->name, $event->message->email, $event->message->message));
    }
}
