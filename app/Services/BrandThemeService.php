<?php

namespace App\Services;

use App\Models\SiteSettings;

class BrandThemeService
{
    public function colors(): array
    {
        $stored = SiteSettings::where('key', 'primary_accent_color')->value('payload');
        $color = is_string($stored) ? json_decode($stored, true) : null;
        $accent = is_string($color) && preg_match('/^#[0-9a-f]{6}$/i', $color) ? strtoupper($color) : '#3874FF';
        [$r, $g, $b] = $this->rgb($accent);

        return [
            'accent' => $accent,
            'rgb' => "$r, $g, $b",
            'dark' => $this->mix($accent, '#000000', .22),
            'light' => $this->mix($accent, '#FFFFFF', .35),
            'subtle' => $this->mix($accent, '#FFFFFF', .88),
            'contrast' => $this->contrast($r, $g, $b),
        ];
    }

    private function rgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    private function mix(string $from, string $to, float $weight): string
    {
        $a = $this->rgb($from); $b = $this->rgb($to);
        return sprintf('#%02X%02X%02X', ...array_map(fn ($i) => (int) round($a[$i] * (1 - $weight) + $b[$i] * $weight), [0, 1, 2]));
    }

    private function contrast(int $r, int $g, int $b): string
    {
        $channels = array_map(function ($value) {
            $value /= 255;
            return $value <= .03928 ? $value / 12.92 : (($value + .055) / 1.055) ** 2.4;
        }, [$r, $g, $b]);
        return (.2126 * $channels[0] + .7152 * $channels[1] + .0722 * $channels[2]) > .42 ? '#111111' : '#FFFFFF';
    }
}
