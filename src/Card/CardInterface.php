<?php

namespace App\Card;

interface CardInterface
{
    public function applyEffect($target): void;
}

?>