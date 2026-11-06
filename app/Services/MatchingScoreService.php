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
        // v2 applies only to newly inserted candidates; existing scores are not recalculated.
        $reasons[] = 'Metode v2: nama 40, ciri 25, lokasi 25, tanggal 10; kategori tidak dinilai';
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
        if (count($union)) {
            $points = (int) round((25 * count(array_intersect($left, $right))) / count($union));
            $score += $points;
            $reasons[] = "Kesamaan ciri/deskripsi (+{$points})";
        }

        $titleLost = $this->normalize((string) $lost->title);
        $titleFound = $this->normalize((string) $found->title);
        if ($titleLost !== '' && $titleFound !== '') {
            similar_text($titleLost, $titleFound, $similarity);
            $titleScore = (int) round((40 * $similarity) / 100);
            $score += $titleScore;
            $reasons[] = "Nama barang mirip (+{$titleScore})";
        }

        $locationLost = $this->normalize((string) $lost->location);
        $locationFound = $this->normalize((string) $found->location);
        if ($locationLost !== '' && $locationFound !== '') {
            if ($locationLost === $locationFound) {
                $score += 25;
                $reasons[] = 'Lokasi sama (+25)';
            } elseif (
                Str::contains($locationLost, $locationFound) ||
                Str::contains($locationFound, $locationLost)
            ) {
                $score += 12;
                $reasons[] = 'Lokasi berkaitan (+12)';
            }
        }

        if ($lost->event_date && $found->event_date) {
            $days = abs($lost->event_date->diffInDays($found->event_date));
            if ($days <= 1) {
                $score += 10;
                $reasons[] = 'Tanggal berdekatan (+10)';
            } elseif ($days <= 7) {
                $score += 5;
                $reasons[] = 'Tanggal dalam 7 hari (+5)';
            }
        }
        return [min(100, $score), $reasons];
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->lower()->squish()->toString();
    }
}
