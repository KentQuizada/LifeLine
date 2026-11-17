<?php

namespace Tests\Feature;

use Tests\TestCase;

/** The app is installable (DEC-001): every page links a valid manifest with PNG icons that exist. */
class PwaManifestTest extends TestCase
{
    public function test_pages_link_the_manifest(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('rel="manifest"', false);
    }

    public function test_the_manifest_is_valid_and_its_icons_exist(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/home', $manifest['start_url']);
        $this->assertEqualsCanonicalizing(['192x192', '512x512'], array_column($manifest['icons'], 'sizes'));

        foreach ($manifest['icons'] as $icon) {
            [$width] = getimagesize(public_path(ltrim($icon['src'], '/')));
            $this->assertSame((int) explode('x', $icon['sizes'])[0], $width);
        }
    }
}
