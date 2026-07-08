<?php

it('redirects the welcome page to login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
