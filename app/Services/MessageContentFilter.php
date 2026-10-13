<?php

namespace App\Services;

class MessageContentFilter
{
    /**
     * Keep the list centralized so every message entry point uses the same rules.
     */
    private const PROHIBITED_TERMS = [
        'putang ina',
        'putangina',
        'tang ina',
        'tangina',
        'walang hiya',
        'pakshit',
        'pakshet',
        'bwisit',
        'leche',
        'lintik',
        'kupal',
        'tarantado',
        'hayop',
        'gago',
        'tanga',
        'bobo',
        'ulol',
        'fuck',
        'fucking',
        'shit',
        'bitch',
        'asshole',
        'bastard',
        'dick',
        'pussy',
        'slut',
        'whore',
        'cunt',
        'nigger',
        'nigga',
        'porn',
        'rape',
        'kill yourself',
    ];

    public function containsInappropriateLanguage(?string $message): bool
    {
        if ($message === null || trim($message) === '') {
            return false;
        }

        $normalized = $this->normalize($message);
        $terms = array_map(fn (string $term): string => preg_quote($term, '/'), self::PROHIBITED_TERMS);
        usort($terms, fn (string $left, string $right): int => \strlen($right) <=> \strlen($left));

        return preg_match('/(?:^|\s)(?:' . implode('|', $terms) . ')(?:$|\s)/u', $normalized) === 1;
    }

    private function normalize(string $message): string
    {
        $message = mb_strtolower($message, 'UTF-8');
        $message = strtr($message, [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '@' => 'a',
            '$' => 's',
            '5' => 's',
            '7' => 't',
        ]);

        return trim((string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', $message));
    }
}
