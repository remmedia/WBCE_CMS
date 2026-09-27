<?php
/** Contract for a primary (pre-2FA) authentication provider. */
interface WbceAuthenticationProviderInterface
{
    public function getId();
    public function getName();
    /** Return true only after the external credential was verified. */
    public function authenticate(array $user, array $credentials);
}
