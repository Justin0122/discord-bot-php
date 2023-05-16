<?php

namespace Bot\Commands;

use Bot\Builders\EmbedBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Embed\Field;
use http\Message;
use Bot\Components\NextButton;
use Bot\Components\PreviousButton;


class Pagination
{
    public function getName(): string
    {
        return 'pagination';
    }

    public function getDescription(): string
    {
        return 'test pagination';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(Interaction $interaction, $discord): void
    {

        $builder = MessageBuilder::new();
        $embed = new Embed($discord);
        $embed->setTitle('Title');
        $embed->setDescription('Description');
        $embed->setFooter('test');
        $embed->setColor(0x00FF00);
        $field = new Field($discord);
        $field->name = 'Test field';
        $field->value = 'test';
        $embed->addField($field);

        $buttonPrevious = new PreviousButton();
        $buttonNext = new NextButton();

        $actionRow = ActionRow::new();
        $actionRow->addComponent($buttonPrevious);
        $actionRow->addComponent($buttonNext);
        $builder->addEmbed($embed);
        $builder->addComponent($actionRow);

         $buttonPrevious->setListener(function (Interaction $interaction) use ($discord) {
            $this->updateresponse($interaction, $discord);
        }, $discord);

        $interaction->respondWithMessage($builder, true);
    }

    public function updateresponse(Interaction $interaction, Discord $discord): void
    {
        $builder = MessageBuilder::new();
        $embed = new Embed($discord);
        $embed->setTitle('testing update');
        $embed->setDescription('testing update');
        $embed->setFooter('testing update');
        $builder->addEmbed($embed);
        $interaction->updateMessage($builder);
    }

}
