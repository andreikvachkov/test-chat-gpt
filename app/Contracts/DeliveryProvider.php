<?php

namespace App\Contracts;

interface DeliveryProvider
{
    public function quote(array $destination, array $items): array;
}
