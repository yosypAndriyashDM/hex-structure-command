<?php

namespace YosypPro\HexagonalStructureCommand\Trait;

use Symfony\Component\Console\Question\Question;

trait IOTrait {

    // Common I/O methods
    private function getUserInputRequest($title = null, $example = null)
    {
        $title !== null ? $this->writeLine($title) : 0;
        $question = new Question(': ', $example);
        $response = $this->helper->ask($this->input, $this->output, $question);
        $this->writeLine('');

        return $response;
    }

    private function writeLine($text = ''): void
    {
        $this->output->writeln($text);
    }

    private function writeSuccess($text): void
    {
        $this->output->writeln('<info>' . $text . '</info>');
    }

    private function writeError($text): void
    {
        $this->output->writeln('<error>' . $text . '</error>');
    }

    private function writeComment($text): void
    {
        $this->output->writeln('<comment>' . $text . '</comment>');
    }

    private function writeTitle($title): void
    {
        $paddingChars = 5;

        if (!is_array($title)) {
            $title = [$title];
        }

        $maxLengthLine = '';
        foreach ($title as $line) {
            if (strlen(trim($line)) > strlen($maxLengthLine)) {
                $maxLengthLine = $line;
            }
        }

        $this->output->writeln('╔' . $this->repeatChar($paddingChars, '═') . $this->drawSubLine($maxLengthLine) . $this->repeatChar($paddingChars, '═') . '╗');

        foreach ($title as $line) {
            $pendingChars = $this->repeatChar(strlen($maxLengthLine) - strlen($line));
            $this->output->writeln([
                '║' .
                $this->repeatChar($paddingChars) .
                $line  .
                $pendingChars .
                $this->repeatChar($paddingChars) .
                '║']);
        }

        $this->output->writeln('╚' . $this->repeatChar($paddingChars, '═') . $this->drawSubLine($maxLengthLine) . $this->repeatChar($paddingChars, '═') . '╝');
        $this->output->writeln('');
    }

    private function drawSubLine($text): string
    {
        return $this->repeatChar(strlen($text), '═');
    }

    private function drawStepSeparation()
    {
        $this->writeSuccess($this->repeatChar(64, '-'));
    }

    private function repeatChar($length = 1, $char = ' '): string
    {
        $length = $length > 0 ? $length : 0;
        return str_repeat($char, $length);
    }
}