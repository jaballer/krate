<?php

namespace Tests\Unit;

use App\Http\Controllers\RecordController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit coverage for the BOOLEAN-mode fulltext query builder. The fulltext path
 * only runs on MariaDB/MySQL (CI uses SQLite), so these assertions guard the
 * builder that path depends on — including when it must defer to LIKE (null).
 */
class RecordSearchTest extends TestCase
{
    private function build(string $search): ?string
    {
        return (new ReflectionMethod(RecordController::class, 'fullTextQuery'))
            ->invoke(new RecordController, $search);
    }

    public function test_indexable_tokens_become_required_prefix_terms(): void
    {
        // Every token is required (+) so "Miles Davis" needs both words.
        $this->assertSame('+Miles* +Davis*', $this->build('Miles Davis'));
        $this->assertSame('+Krautrock*', $this->build('Krautrock'));
        // Prefix matching is retained for the indexed path.
        $this->assertSame('+Krau*', $this->build('Krau'));
    }

    public function test_hyphen_is_a_token_boundary(): void
    {
        // Both parts indexable → split into two required tokens, not fused.
        $this->assertSame('+Hip* +Hop*', $this->build('Hip-Hop'));
    }

    public function test_unicode_letters_and_digits_are_preserved(): void
    {
        $this->assertSame('+Beyoncé*', $this->build('Beyoncé'));
        $this->assertSame('+1999*', $this->build('1999'));
    }

    public function test_short_tokens_defer_to_like(): void
    {
        // A token too short to index would be mandatory-but-unmatchable, or
        // silently dropped (weakening the search) — so the whole search uses LIKE.
        $this->assertNull($this->build('Jay-Z'));   // 1-char "Z"
        $this->assertNull($this->build('U2'));       // 2-char only
        $this->assertNull($this->build('U2 War'));   // mixed: short + indexable
    }

    public function test_stopwords_defer_to_like(): void
    {
        // "the" is an InnoDB stopword: never indexed, so "+the*" matches nothing.
        $this->assertNull($this->build('The Chronic'));
        $this->assertNull($this->build('the'));
    }

    public function test_punctuation_only_input_defers_to_like(): void
    {
        $this->assertNull($this->build('.'));
        $this->assertNull($this->build('&'));
        $this->assertNull($this->build('   '));
    }
}
