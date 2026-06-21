<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Auth/profile views render `@vite`, whose manifest (public/build) is
        // gitignored and not produced by the test command. Stub Vite suite-wide
        // so tests don't depend on a built front end.
        $this->withoutVite();
    }
}
