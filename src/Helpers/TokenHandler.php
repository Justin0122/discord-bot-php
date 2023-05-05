<?php

namespace Bot\Helpers;

class TokenHandler
{
    public static function storeTokens($tokens) {

        //read the users.json file
        $users = file_get_contents(__DIR__ . "/../../users.json");
        //decode the json into an array

        $decoded_tokens = json_decode($tokens, true);
        foreach ($decoded_tokens as $user_id => $user_tokens) {
            $user_tokens = str_replace("\\", "", $user_tokens["access_token"]);
            $data = array(
                "access_token" => $user_tokens["access_token"],
                "refresh_token" => $user_tokens["refresh_token"],
                "playlist_gen" => false
            );
            $json_data = json_encode($data);
            $users = json_decode($users, true);
            $users[$user_id] = $json_data;
            $users = json_encode($users);
            file_put_contents(__DIR__ . "/../../users.json", $users);
        }
    }
}