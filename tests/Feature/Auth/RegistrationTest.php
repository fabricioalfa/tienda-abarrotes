<?php

test('registration screen is not publicly available', function () {
    $response = $this->get('/register');

    // Self-registration is disabled; only admins create users via the admin panel.
    $response->assertStatus(404);
});

test('login page is accessible and registration link is absent', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertDontSee('Register');
});
