<?php

namespace App\Services;

use App\Contracts\DeliveryProvider;
use App\Contracts\PaymentGateway;
use App\Contracts\SmsSender;
use App\Models\Order;
use LogicException;

final class UnavailableIntegrations implements DeliveryProvider, PaymentGateway, SmsSender
{
    public function createPayment(Order $order): string
    {
        throw new LogicException('Платёжный провайдер ещё не подключён.');
    }

    public function sendCode(string $phone, string $code): void
    {
        throw new LogicException('SMS-провайдер ещё не подключён.');
    }

    public function quote(array $destination, array $items): array
    {
        throw new LogicException('Автоматический расчёт доставки ещё не подключён.');
    }
}
