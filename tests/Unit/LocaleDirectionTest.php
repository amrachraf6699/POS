<?php

namespace Tests\Unit;

use App\Support\LocaleDirection;
use PHPUnit\Framework\TestCase;

class LocaleDirectionTest extends TestCase
{
    public function test_arabic_locales_are_rtl(): void
    {
        $this->assertTrue(LocaleDirection::isRtl('ar'));
        $this->assertTrue(LocaleDirection::isRtl('ar_EG'));
        $this->assertSame('rtl', LocaleDirection::for('ar'));
    }

    public function test_english_and_unknown_locales_are_ltr(): void
    {
        $this->assertFalse(LocaleDirection::isRtl('en'));
        $this->assertSame('ltr', LocaleDirection::for('en_US'));
        $this->assertSame('ltr', LocaleDirection::for('fr'));
    }
}
