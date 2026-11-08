<?php

namespace App\Contracts;

interface SmsSender
{
    public function sendCode(string $phone, string $code): void;
}
