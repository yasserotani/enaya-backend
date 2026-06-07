<?php

namespace App\Services;

class PhoneNormalizerService
{
    /**
     * Normalize a phone number to the standard international format (+963XXXXXXXXX).
     *
     * Handles Syrian phone numbers in various formats:
     * - 0912345678 → +963912345678
     * - 0096312345678 → +963912345678
     * - 96312345678 → +963912345678
     * - 912345678 → +963912345678 (assumes Syria when missing country code)
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Remove all non-numeric and non-+ characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Handle 00 prefix (international format without +)
        if (str_starts_with($phone, '00')) {
            $phone = '+'.substr($phone, 2);
        }

        // Handle 09 prefix (domestic Syria format)
        if (str_starts_with($phone, '09')) {
            $phone = '+963'.substr($phone, 1);
        }

        // Handle 963 prefix (without +)
        if (str_starts_with($phone, '963')) {
            $phone = '+'.$phone;
        }

        if (preg_match('/^9\d{8}$/', $phone) === 1) {
            $phone = '+963'.$phone;
        }

        return $phone;
    }
}
