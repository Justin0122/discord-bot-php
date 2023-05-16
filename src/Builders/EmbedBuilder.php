<?php

namespace Bot\Builders;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;

class EmbedBuilder
{

    private Embed $embed;
    private array $fields = [];
    private int $maxFieldsPerPage = 10;

    public static function create(Discord $discord, string $title = '', string $footer = '', string $description = ''): EmbedBuilder
    {
        return (new self($discord, $title, $footer, $description));
    }

    public function __construct(Discord $discord, string $title = '', string $footer = '', string $description = '')
    {
        $this->embed = new Embed($discord);
        $this->embed->setType('rich');
        $this->embed->setColor(2067276);
        $this->embed->setDescription($description);
        $this->embed->setTitle($title);
        $this->embed->setFooter($footer);
    }

    public function setFailed(): self
    {
        $this->embed->setTitle('Failed');
        $this->embed->setColor(15548997);
        return $this;
    }

    public function setSuccess(): self
    {
        $this->embed->setColor(3066993);
        return $this;
    }

    public function setWarning(): self
    {
        $this->embed->setColor(15844367);
        return $this;
    }

    public function setInfo(): self
    {
        $this->embed->setColor(3447003);
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->embed->setTitle($title);
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->embed->setDescription($description);
        return $this;
    }

    public function setFooter(string $footer): self
    {
        $this->embed->setFooter($footer);
        return $this;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->embed->setThumbnail($thumbnail);
        return $this;
    }

    public function setAuthor(string $author): self
    {
        $this->embed->setAuthor($author);
        return $this;
    }

    public function addField(string $name, string $value, bool $inline = false): self
    {
        $this->embed->addFieldValues($name, $value, $inline);
        return $this;
    }

    public function addFields(array $fields): self
    {
        foreach ($fields as $field) {
            $this->addField($field['name'], $field['value'], $field['inline'] ?? false);
        }
        return $this;
    }

    public function setImage(string $image): self
    {
        $this->embed->setImage($image);
        return $this;
    }

    public function build(): Embed
    {
        if (count($this->fields) <= $this->maxFieldsPerPage) {
            return $this->embed;
        }

        $pages = array_chunk($this->fields, $this->maxFieldsPerPage);
        $page = 1;
        $totalPages = count($pages);

        $this->embed->setDescription("Showing page $page of $totalPages");

        foreach ($pages[$page - 1] as $field) {
            $this->embed->addFieldValues($field['name'], $field['value'], $field['inline'] ?? false);
        }

        $row = ActionRow::new();

        if ($page > 1) {
            $row->addComponent(Button::new(Button::STYLE_SUCCESS)->setLabel('Previous')->setCustomId('previous'));

        }

        if ($page < $totalPages) {
            $row->addComponent(Button::new(Button::STYLE_SUCCESS)->setLabel('Next')->setCustomId('next'));
        }

        // Add the action row to the embed
        $this->embed->addComponent($row);

        return $this->embed;
    }
}