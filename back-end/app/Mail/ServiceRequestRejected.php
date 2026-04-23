<?php

namespace App\Mail;

use App\Models\ServiceRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestRejected extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    protected $serviceRequest;
    public function __construct( ServiceRequests $serviceRequest)
    {
        //
        $this->serviceRequest = $serviceRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Demande de service rejetée',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request.request_rejected',
            with:[
                'nomClient'=>$this->serviceRequest->full_name,
                'serviceDemande'=>$this->serviceRequest->service->name,
                'raisonRejet'=>$this->serviceRequest->rejection_reason,
                'numeroDemande' => $this->serviceRequest->request_number
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
