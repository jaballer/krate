<?php

namespace Tests\Unit;

use App\Http\Controllers\RecordController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit coverage for the BOOLEAN-mode fulltext term builder. The fulltext path
 * itself only runs on MariaDB/MySQL (CI uses SQLite), so these assertions guard
 * the term-building logic that path depends on.
 */
class RecordSearchTest extends TestCase
{
    private function build(string $search): string
    {
        return (new ReflectionMethod(RecordController::class, 'booleanFullTextTerms'))
            ->invoke(new RecordController, $search);
    }

    public function test_hyphenated_value_splits_on_the_separator(): void
    {
        // The hyphen is a token boundary, not deleted into one fused token.
        // Both parts here are long enough to index, so both survive.
        $this->assertSame('+Hip* +Hop*', $this->build('Hip-Hop'));
    }

    public function test_short_tokens_are_dropped_so_the_rest_still_matches(): void
    {
        // "Jay-Z": the 1-char "Z" can't be indexed, so a required "+Z*" would
        // block every match. Drop it and keep "+Jay*", which finds the record.
        $this->assertSame('+Jay*', $this->build('Jay-Z'));

        // When every token is too short, return '' so applySearch() uses LIKE.
        $this->assertSame('', $this->build('U2'));
    }

    public function test_each_word_is_required(): void
    {
        // "Miles Davis" needs both words, not either — matches the stricter
        // intent of the old LIKE phrase search.
        $this->assertSame('+Miles* +Davis*', $this->build('Miles Davis'));
    }

    public function test_punctuation_only_input_yields_no_terms(): void
    {
        // Returning '' makes applySearch() fall back to LIKE rather than send a
        // bare wildcard to the fulltext parser (which is a syntax error).
        $this->assertSame('', $this->build('.'));
        $this->assertSame('', $this->build('&'));
        $this->assertSame('', $this->build('   '));
    }

    public function test_unicode_letters_and_digits_are_preserved(): void
    {
        $this->assertSame('+Beyoncé*', $this->build('Beyoncé'));
        $this->assertSame('+1999*', $this->build('1999'));
    }
}
