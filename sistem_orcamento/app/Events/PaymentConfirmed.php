<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $paymentId;
    public $status;
    public $companyId;
    public $planType;
    public $amount;

    /**
     * Create a new event instance.
     */
    public function __construct($paymentId, $status, $companyId = null, $planType = null, $amount = null)
    {
        $this->paymentId = $paymentId;
        $this->status = $status;
        $this->companyId = $companyId;
        $this->planType = $planType;
        $this->amount = $amount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('payments'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'status' => $this->status,
            'company_id' => $this->companyId,
            'plan_type' => $this->planType,
            'amount' => $this->amount,
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'payment.confirmed';
    }
}
