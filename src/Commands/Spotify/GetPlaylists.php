<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\EmbedBuilder;
use Bot\Helpers\ErrorHandler;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class GetPlaylists
{

    public function getName(): string
    {
        return 'getplaylists';
    }

    public function getDescription(): string
    {
        return 'Get a list of your playlists.';
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getOptions(): array
    {
        return [];
    }


    public function handle(Interaction $interaction, $discord): void
    {

        $tokenHandler = new TokenHandler($_ENV['API_URL'], $_ENV['SECURE_TOKEN']);
        $user_id = $interaction->member->user->id;
        $tokens = $tokenHandler->getTokens($user_id);


        if (!$tokens) {
            $interaction->respondWithMessage(
                MessageBuilder::new()->addEmbed(ErrorHandler::handle("You need to authorize the bot first by using the '/spotify' command.")), true
            );
        }

        // Set the API using SessionHandler
        $api = (new SessionHandler())->setSession($user_id);

        $me = $api->me();


        $playlists = $api->getUserPlaylists($me->id, [
            'limit' => 50
        ]);

        if (!$playlists) {
            $interaction->respondWithMessage(
                MessageBuilder::new()->addEmbed(ErrorHandler::handle("No playlists found.")), true
            );
        }

        //show only 10 playlists per embed page. Add buttons for previous and next
        $pages = array_chunk($playlists->items, 9);

        $page = 1;
        $totalPages = count($pages);

        //use the embed builder to create the embed
        $embed = EmbedBuilder::create($discord)
            ->setTitle('Playlists')
            ->setDescription("Showing page $page of $totalPages")
            ->setSuccess();

        foreach ($pages[$page - 1] as $playlist) {
            $embed->addField('Playlists', $playlist->name, true);
        }

        $interaction->respondWithMessage(
            MessageBuilder::new()->addEmbed($embed->build())
        );
    }
}
