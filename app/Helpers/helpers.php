<?php

use App\Models\User;

if (! function_exists('profile_url')) {
    /**
     * Generate profile URL using nickname
     *
     * @param  User|int  $user
     * @return string
     */
    function profile_url($user)
    {
        if (is_numeric($user)) {
            $user = User::find($user);
        }

        if (! $user) {
            return url('/@unknown');
        }

        $nickname = $user->profile?->nickname ?? $user->id;

        return url('/@'.$nickname);
    }
}
