<?php

namespace App\Contracts;

interface OtpProvider
{
    public function issue(string $mobile): string;
}
