<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    public function createPayment(Order $order): string;
}
