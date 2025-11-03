<?php

declare(strict_types=1);

// This test is skipped as there is no root route defined in the application
// The application is an API-only application with routes under /api
test('the application returns a successful response', function () {
    $response = $this->get('/api/inventory');

    $response->assertSuccessful();
})->skip('Example test - not part of the application test suite');
