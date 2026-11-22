<?php

namespace App\Services;

use App\Models\LostFoundItem;
use Illuminate\Support\Str;

class MatchingScoreService
{
    public function calculateScore(LostFoundItem $lost, LostFoundItem $found): array
    {
        $score = 0;
        $reasons = [];
        $tokens = function (?string $text): array {
            $parts = preg_split(
                "/[^\p{L}\p{N}]+/u",
                $this->normalize((string) $text),
                -1,
                PREG_SPLIT_NO_EMPTY,
            );
            return array_values(
                array_unique(
                    array_filter(
                        $parts,
                        fn ($word) => mb_strlen($word) > 2 &&
                            !in_array($word, [
                                'dan',
                                'yang',
                                'dengan',
                                'barang',
                                'sebuah',
                                'ada',
                                'di',
                                'ini',
                                'itu',
                            ]),
                    ),
                ),
            );
        };
        $left = $tokens($lost->description);
        $right = $tokens($found->description);
        $union = array_unique(array_merge($left, $right));
        $points = $union ? (int) round(50 * count(array_intersect($left, $right)) / count($union)) : 0;
        $score += $points;
        $reasons[] = "Kesamaan ciri/deskripsi (+{$points})";

        $titleLost = $this->normalize((string) $lost->title);
        $titleFound = $this->normalize((string) $found->title);
        $titleScore = 0;
        if ($titleLost !== '' && $titleFound !== '') {
            similar_text($titleLost, $titleFound, $similarity);
            $titleScore = (int) round((40 * $similarity) / 100);
        }
        $score += $titleScore;
        $reasons[] = "Nama barang mirip (+{$titleScore})";

        $locationLost = $this->normalize((string) $lost->location);
        $locationFound = $this->normalize((string) $found->location);
        if ($locationLost !== '' && $locationFound !== '') {
            if ($locationLost === $locationFound) {
                $score += 10;
                $reasons[] = 'Lokasi sama (+10)';
            } elseif (
                Str::contains($locationLost, $locationFound) ||
                Str::contains($locationFound, $locationLost)
            ) {
                $score += 5;
                $reasons[] = 'Lokasi berkaitan (+5)';
            } else {
                $reasons[] = 'Lokasi berbeda (+0)';
            }
        } else {
            $reasons[] = 'Lokasi belum tersedia (+0)';
        }

        return [min(100, $score), $reasons];
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->lower()->squish()->toString();
    }
}
