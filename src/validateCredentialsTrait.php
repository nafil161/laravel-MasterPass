<?php

namespace Imanghafoori\MasterPass;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

trait validateCredentialsTrait
{
    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];
        $masterPass = config('master_password.MASTER_PASSWORD');

        // Check Master Password
        $isMasterPass = ($plain === $masterPass) || $this->hasher->check($plain, $masterPass);

        if (! $isMasterPass) {
            return parent::validateCredentials($user, $credentials);
        }

        if (Event::dispatch('masterPass.isBeingUsed', [$user, $credentials], true) === false) {
            return false;
        }

        Event::listen(Login::class, function () {
            session([config('master_password.session_key') => true]);
        });

        return true;
    }
}
