<?php

namespace Tests\Unit\Similarity;

use App\Services\Similarity\TextAnalyzer;
use PHPUnit\Framework\TestCase;

class TextAnalyzerTest extends TestCase
{
    private TextAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new TextAnalyzer('english');
    }

    public function test_normalize_strips_accents_punctuation_and_thousands_separators(): void
    {
        $this->assertSame('cafe in 1000000 cafes', $this->analyzer->normalize('Café in 1.000.000 "cafés"?!'));
    }

    public function test_content_words_drop_english_stopwords_and_quiz_phrasing(): void
    {
        $this->assertSame(['capital', 'france'], $this->analyzer->contentWords('What is the capital of France?'));
        $this->assertSame(['capital', 'france'], $this->analyzer->contentWords('Which of the following is the capital of France?'));
        $this->assertSame(['capital', 'france'], $this->analyzer->contentWords('Name the capital of France'));
    }

    public function test_stemming_unifies_english_inflections(): void
    {
        $this->assertSame(
            array_keys($this->analyzer->stems('footballers')),
            array_keys($this->analyzer->stems('footballer')),
        );
        $this->assertSame(
            array_keys($this->analyzer->stems('painted')),
            array_keys($this->analyzer->stems('painting')),
        );
    }

    public function test_numbers_are_kept_unstemmed(): void
    {
        $this->assertArrayHasKey('1995', $this->analyzer->stems('In 1995 Ajax won'));
        $this->assertSame(['1995'], $this->analyzer->numbers('In 1995 Ajax won'));
    }

    public function test_trigrams_match_compounds_written_apart(): void
    {
        $this->assertSame($this->analyzer->trigrams('football club'), $this->analyzer->trigrams('footballclub'));
    }

    public function test_detects_negation(): void
    {
        $this->assertTrue($this->analyzer->hasNegation('Which country does not border the sea?'));
        $this->assertTrue($this->analyzer->hasNegation("Which of these isn't a fruit?"));
        $this->assertFalse($this->analyzer->hasNegation('Which country borders the sea?'));
    }
}
