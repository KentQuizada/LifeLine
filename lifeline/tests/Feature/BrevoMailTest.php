<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Tests\TestCase;

/** DEC-025: online email goes through Brevo's web API because Railway's Trial plan blocks SMTP. */
class BrevoMailTest extends TestCase
{
    public function test_the_brevo_mailer_uses_the_web_api_with_the_configured_key(): void
    {
        config(['services.brevo.key' => 'test-key']);

        $transport = Mail::mailer('brevo')->getSymfonyTransport();

        $this->assertInstanceOf(BrevoApiTransport::class, $transport);
        $this->assertSame('brevo+api://api.brevo.com', (string) $transport);
    }
}
