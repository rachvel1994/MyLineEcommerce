<?php

test('guests are redirected to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('filament.backend.auth.login'));
});
