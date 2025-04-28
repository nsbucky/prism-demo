<?php

namespace App\Services;


require_once base_path('library/GPT-3-Encoder-PHP/gpt3-encoder.php');


class GPT3Encoder
{
    public static function encode(string $text): array
    {
        return \gpt_encode($text);
    }

    public static function decode(array $tokens): string
    {
        return \gpt_decode($tokens);
    }
}
