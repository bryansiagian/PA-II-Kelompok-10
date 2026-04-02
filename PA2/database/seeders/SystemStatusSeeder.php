<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductOrderStatus;
use App\Models\DeliveryStatus;
use App\Models\ProductOrderType;
use App\Models\ProductOrderDelivery;

class SystemStatusSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Status Pesanan (Product Order Status)
        $orderStatuses = ['Pending', 'Processed', 'Shipping', 'Completed', 'Rejected', 'Cancelled'];
        foreach ($orderStatuses as $status) {
            ProductOrderStatus::updateOrCreate(['name' => $status]);
        }

        // 2. Status Pengiriman (Delivery Status)
        $deliveryStatuses = ['Ready', 'Claimed', 'In Transit', 'Delivered'];
        foreach ($deliveryStatuses as $status) {
            DeliveryStatus::updateOrCreate(['name' => $status]);
        }

        // 3. Tipe Pesanan
        $types = [
            'Motorcycle (Kapasitas Kecil)',
            'Car/Van (Kapasitas Besar)'
        ];
        foreach ($types as $t) {
            ProductOrderType::updateOrCreate(['name' => $t]);
        }

        // 4. Metode Pengambilan
        $deliveries = ['Delivery', 'Self Pickup'];
        foreach ($deliveries as $d) {
            ProductOrderDelivery::updateOrCreate(['name' => $d]);
        }
    }
}