<?php

namespace App\Services;

class PhoneNormalizerService
{

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Remove all non-numeric and non-+ characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Handle 00 prefix (international format without +)
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        // Handle 09 prefix (domestic Syria format)
        if (str_starts_with($phone, '09')) {
            $phone = '+963' . substr($phone, 1);
        }

        // Handle 963 prefix (without +)
        if (str_starts_with($phone, '963')) {
            $phone = '+' . $phone;
        }

        if (preg_match('/^9\d{8}$/', $phone) === 1) {
            $phone = '+963' . $phone;
        }

        return $phone;
    }
}
