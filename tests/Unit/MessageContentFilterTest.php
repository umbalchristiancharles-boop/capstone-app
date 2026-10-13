<?php

namespace Tests\Unit;

use App\Services\MessageContentFilter;
use Tests\TestCase;

class MessageContentFilterTest extends TestCase
{
    public function test_it_blocks_english_inappropriate_language(): void
    {
        $filter = new MessageContentFilter();

        $this->assertTrue($filter->containsInappropriateLanguage('Please stop sending fucking messages.'));
    }

    public function test_it_blocks_tagalog_inappropriate_language(): void
    {
        $filter = new MessageContentFilter();

        $this->assertTrue($filter->containsInappropriateLanguage('Putang ina mo!'));
    }

    public function test_it_handles_common_obfuscation_without_blocking_clean_text(): void
    {
        $filter = new MessageContentFilter();

        $this->assertTrue($filter->containsInappropriateLanguage('That is b1tch behavior.'));
        $this->assertFalse($filter->containsInappropriateLanguage('Please confirm the delivery schedule.'));
    }
}
