<?php

namespace Bot\Classes;

class UserC
{
    public static function getDiscordUser($discord, $user_id)
    {
        return $discord->users->offsetGet($user_id);
    }

    public static function getDiscordUserAvatar($discord, $user_id)
    {
        $user = self::getDiscordUser($discord, $user_id);
        return $user->getAvatarAttribute();
    }

    public static function getInteractionUser($interaction)
    {
        return $interaction->member->user;
    }

    public static function getInteractionUserID($interaction)
    {
        return $interaction->member->user->id;
    }

}