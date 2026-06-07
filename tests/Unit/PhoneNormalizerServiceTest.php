<?php

namespace Tests\Unit;

use App\Services\PhoneNormalizerService;
use PHPUnit\Framework\TestCase;

class PhoneNormalizerServiceTest extends TestCase
{
    public function test_normalize_null_phone()
    {
        $result = PhoneNormalizerService::normalize(null);
        $this->assertNull($result);
    }

    public function test_normalize_domestic_format()
    {
        $result = PhoneNormalizerService::normalize('0912345678');
        $this->assertEquals('+963912345678', $result);
    }

    public function test_normalize_international_format_with_00()
    {
        $result = PhoneNormalizerService::normalize('00963912345678');
        $this->assertEquals('+963912345678', $result);
    }

    public function test_normalize_international_format_963()
    {
        $result = PhoneNormalizerService::normalize('96312345678');
        $this->assertEquals('+96312345678', $result);
    }

    public function test_normalize_with_dashes_and_spaces()
    {
        $result = PhoneNormalizerService::normalize('09-1234-5678');
        $this->assertEquals('+963912345678', $result);
    }

    public function test_normalize_with_plus_sign()
    {
        $result = PhoneNormalizerService::normalize('+963912345678');
        $this->assertEquals('+963912345678', $result);
    }
}
