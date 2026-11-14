<?php

namespace App\Support;

class MixedScript
{
    /**
     * 'Qiroah Kitab: أدب العالم' -> [['Qiroah Kitab: ', false], ['أدب العالم', true]]
     * Spasi menempel ke segmen yang sedang berjalan (sama seperti versi Flask).
     */
    public static function split(?string $text): array
    {
        if ($text === null || $text === '') {
            return [['', false]];
        }

        $parts = [];
        $buf = '';
        $bufArabic = null;

        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $isArabic = (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $ch);
            if ($bufArabic === null) {
                $bufArabic = $isArabic;
            }
            if ($isArabic === $bufArabic || preg_match('/^\s$/u', $ch)) {
                $buf .= $ch;
            } else {
                $parts[] = [$buf, $bufArabic];
                $buf = $ch;
                $bufArabic = $isArabic;
            }
        }
        if ($buf !== '') {
            $parts[] = [$buf, $bufArabic];
        }

        return $parts;
    }
}
