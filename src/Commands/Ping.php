<?php

namespace Bot\Commands;

use Bot\Builders\EmbedBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class Ping
{
    public function getName(): string
    {
        return 'ping';
    }

    public function getDescription(): string
    {
        return 'Ping the bot to check if it is online';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'test',
                'description' => 'test',
                'type' => 3,
                'required' => false
            ]
        ];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(Interaction $interaction, $discord): void
    {
        $optionRepository = $interaction->data->options;
        $firstOption = $optionRepository['test'];
        $value = $firstOption->value;

        $embedBuilder = EmbedBuilder::create($discord)
            ->setTitle('Pong!')
            ->setDescription('The bot is online.')
            ->setSuccess();

        if (!empty($value)) {
            $embedBuilder->addField('Test', $value);
        }

        $interaction->respondWithMessage(
            MessageBuilder::new()->addEmbed($embedBuilder->build()),
            true
        );
    }
}
