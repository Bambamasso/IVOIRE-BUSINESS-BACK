<?php

namespace App\Mail;

use App\Models\ServiceRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestAdmin extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    protected $serviceRequest;
    public function __construct(ServiceRequests $serviceRequest)
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
            subject: 'Nouvelle demande de service' . $this->serviceRequest->request_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request.request_admin',
            with: [
                'nomClient' => $this->serviceRequest->full_name,
                'serviceDemande' => $this->serviceRequest->service->name,
                'prixPropose' => $this->serviceRequest->propose_price,
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
