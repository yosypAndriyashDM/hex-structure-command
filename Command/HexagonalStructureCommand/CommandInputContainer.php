<?php

namespace App\Command\HexagonalStructureCommand;

class CommandInputContainer {

    private array $commandInputContainer = [];

    public function getInput($id)
    {
        // If key id not exists in array, return exception (helps in development)
        return $this->commandInputContainer[$id];
    }

    public function existsInput($id): bool
    {
        return isset($this->commandInputContainer[$id]);
    }

    public function addInput($id, $value): void
    {
        $this->commandInputContainer[$id] = $value;
    }
}