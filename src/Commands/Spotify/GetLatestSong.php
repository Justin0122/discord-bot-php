<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\EmbedBuilder;
use Bot\Helpers\ErrorHandler;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class GetLatestSong
{
    public function getName(): string
    {
        return 'getlatestsong';
    }

    public function getDescription(): string
    {
        return 'Get the latest song from your liked songs';
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
                MessageBuilder::new()->addEmbed(ErrorHandler::handle("You need to authorize the bot first by using the '/spotify' command."))
            );
        }

        //set the api using sessionHandler
        $api = (new SessionHandler())->setSession($user_id);

        //get the latest 10 songs
        $tracks = $api->getMySavedTracks([
            'limit' => 10
        ]);

        $me = $api->me();

        $embed = EmbedBuilder::create($discord)
            ->setTitle('Liked songs')
            ->setSuccess()
            ->setDescription('Here are your latest 10 liked songs');
        foreach ($tracks->items as $track) {
            $embed->addField($track->track->name, $track->track->artists[0]->name, true);
        }

        $interaction->respondWithMessage(
            MessageBuilder::new()->addEmbed($embed->build())
        );
    }
}