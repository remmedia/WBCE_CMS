<?php

interface WbceAuthFactorProviderInterface
{
    // Intentionally no return type declarations: this is a public module
    // boundary shared with the removable WBCE 1.7/1.6.8 hook bridge.
    public function getId();
    public function isRequired(array $user);
    public function renderChallenge(array $user, $error = '');
    public function verify(array $user, array $input);
}
