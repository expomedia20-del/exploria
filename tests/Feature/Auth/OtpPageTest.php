<?php

namespace Tests\Feature\Auth;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OtpPageTest extends TestCase
{
    public function test_otp_page_is_available_as_a_persian_rtl_inertia_page(): void
    {
        $this->get('/access')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('auth/otp'));
    }
}
