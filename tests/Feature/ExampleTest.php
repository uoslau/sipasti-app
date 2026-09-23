<?php

test('guest diarahkan ke halaman login', function () {
    $this->get('/')->assertRedirect(route('login'));
});
