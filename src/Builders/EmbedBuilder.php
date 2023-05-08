<?php

namespace Bot\Builders;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
class EmbedBuilder
{

    private Embed $embed;

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
        return $this->embed;
    }
}