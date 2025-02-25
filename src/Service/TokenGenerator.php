<?php

namespace App\Service;

class TokenGenerator implements TokenGeneratorInterface
{
    public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
