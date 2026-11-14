<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Web routes still answer in JSON when the client asks for it; only /api/* is JSON by default. */
class JsonErrorsTest extends TestCase
{
    public function test_web_routes_return_json_when_the_client_asks_for_json(): void
    {
        $this->postJson('/requests', [])->assertUnauthorized();
    }
}
