<?php

namespace Bot\Components;

use Discord\Builders\Components\Button;

class NextButton extends Button
{
    public function __construct()
    {
        parent::__construct(Button::STYLE_PRIMARY, 'next', 'Next');
        $this->setCustomId('Next');
        $this->setDisabled(false);
        $this->setLabel('Next');
    }

    public function getButton(): Button
    {
        return $this;
    }
}