<?php

namespace Bot\Components;

use Discord\Builders\Components\Button;

class PreviousButton extends Button
{
    public function __construct()
    {
        parent::__construct(Button::STYLE_PRIMARY, 'previous', 'Previous');
        $this->setCustomId('Previous');
        $this->setDisabled(false);
        $this->setLabel('Previous');
    }

    public function getButton(): Button
    {
        return $this;
    }
}