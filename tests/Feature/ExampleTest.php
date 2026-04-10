<?php

it('redirects root to login', function () {
    $response = $this->get('/');

    // Unauthenticated requests to / are redirected to /login
    $response->assertRedirect('/login');
});
