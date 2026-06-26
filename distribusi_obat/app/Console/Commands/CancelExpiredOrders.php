<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductOrder;
use App\Models\ProductOrderStatus;
use App\Services\SyncReportService;

class CancelExpiredOrders extends Command
{
    protected $signature   = 'orders:cancel-expired';
    protected $description = 'Auto-cancel pesanan yang payment_expired_at sudah lewat';

    public function handle()
    {
        $awaitingStatus  = ProductOrderStatus::where('name', 'Awaiting Payment')->first();
        $cancelledStatus = ProductOrderStatus::where('name', 'Cancelled')->first();

        $expired = ProductOrder::where('product_order_status_id', $awaitingStatus->id)
            ->where('payment_status', 'unpaid')
            ->where('payment_expired_at', '<', now())
            ->get();

        foreach ($expired as $order) {
            $order->update([
                'product_order_status_id' => $cancelledStatus->id,
                'payment_token'           => null,
                'payment_ref'             => null,
            ]);

            app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));

            $this->info("Order #{$order->id} auto-cancelled (expired)");
        }

        $this->info("Total: {$expired->count()} pesanan di-cancel.");
    }
}
