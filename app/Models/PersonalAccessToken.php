<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Refresh the current token.
     *
     * @return string
     */
    public function refresh()
    {
        $this->token = hash('sha256', Str::random(50));

        $this->save();

        return $this->token;
    }
}
