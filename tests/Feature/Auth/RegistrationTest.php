<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // A freshly registered account has no admin/manager role yet, so it's
    // sent to self-service billing rather than the admin-only users.show.
    $response->assertSessionHasNoErrors()
        ->assertRedirect('/billing');

    $this->assertAuthenticated();
});
