<?php

namespace App\Services\Similarity;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Wamania\Snowball\Stemmer\Stemmer;
use Wamania\Snowball\StemmerFactory;

/**
 * Turns question and answer text into comparable features. English by default; Dutch via
 * config dashboard.similarity.language.
 */
class TextAnalyzer
{
    /** English function words and quiz phrasing ("which of the following", "what is", "name the"). */
    private const STOPWORDS_ENGLISH = [
        'a', 'about', 'after', 'all', 'also', 'am', 'an', 'and', 'any', 'are', 'as', 'at', 'be', 'been', 'but', 'by',
        'can', 'could', 'did', 'do', 'does', 'for', 'from', 'had', 'has', 'have', 'he', 'her', 'his', 'how', 'i', 'if',
        'in', 'into', 'is', 'it', 'its', 'me', 'my', 'of', 'on', 'one', 'or', 'our', 'out', 'she', 'so', 'some', 'than',
        'that', 'the', 'their', 'them', 'then', 'there', 'these', 'they', 'this', 'those', 'to', 'up', 'us', 'was',
        'we', 'were', 'what', 'whats', 'when', 'where', 'which', 'while', 'who', 'whom', 'whose', 'why', 'will',
        'with', 'would', 'you', 'your', 'ever',
        // Quiz phrasing
        'following', 'name', 'named', 'called', 'known', 'correct', 'true', 'answer', 'question', 'guess', 'tell',
        'type', 'kind', 'many', 'much', 's', 't',
    ];

    /** Dutch function words and quiz phrasing (used when dashboard.similarity.language is "dutch"). */
    private const STOPWORDS_DUTCH = [
        'aan', 'al', 'alles', 'als', 'altijd', 'andere', 'ben', 'bij', 'daar', 'dan', 'dat', 'de', 'der', 'deze', 'die',
        'dit', 'doch', 'doen', 'door', 'dus', 'een', 'eens', 'en', 'er', 'ge', 'geweest', 'haar', 'had', 'heb',
        'hebben', 'heeft', 'hem', 'het', 'hier', 'hij', 'hoe', 'hun', 'iemand', 'iets', 'ik', 'in', 'is', 'ja', 'je',
        'kan', 'kon', 'kunnen', 'maar', 'me', 'meer', 'men', 'met', 'mij', 'mijn', 'moet', 'na', 'naar', 'nog', 'nu',
        'of', 'om', 'omdat', 'onder', 'ons', 'ook', 'op', 'over', 'reeds', 'te', 'tegen', 'toch', 'toen', 'tot', 'u',
        'uit', 'uw', 'van', 'veel', 'voor', 'want', 'waren', 'was', 'wat', 'we', 'wel', 'werd', 'wezen', 'wie', 'wil',
        'worden', 'wordt', 'zal', 'ze', 'zelf', 'zich', 'zij', 'zijn', 'zo', 'zonder', 'zou',
        'welke', 'welk', 'waar', 'wanneer', 'hoeveel', 'waarom', 'waarvan', 'waarmee', 'waardoor', 'hoelang',
        'noem', 'raad', 'vraag', 'antwoord', 'volgende', 'volgens', 'jaar', 'genaamd', 'heet', 'heten', 'eigenlijk',
    ];

    /** Negations are detected separately and must not count as content ("isn't" normalizes to "isn t"). */
    private const NEGATIONS = [
        'not', 'never', 'none', 'no', 'nor', 'neither', 'cannot', 'isn', 'aren', 'wasn', 'weren', 'doesn', 'don',
        'didn', 'wouldn', 'shouldn', 'couldn', 'hasn', 'haven',
        'niet', 'geen', 'nooit', 'niets',
    ];

    /** @var array<string, true> */
    private array $stopwords;

    private Stemmer $stemmer;

    /** @var array<string, string> */
    private array $stemCache = [];

    public function __construct(?string $language = null)
    {
        $language ??= Container::getInstance()->bound('config')
            ? (string) config('dashboard.similarity.language', 'english')
            : 'english';

        $this->stopwords = array_fill_keys(
            [...($language === 'dutch' ? self::STOPWORDS_DUTCH : self::STOPWORDS_ENGLISH), ...self::NEGATIONS],
            true,
        );
        $this->stemmer = StemmerFactory::create($language === 'dutch' ? 'dutch' : 'english');
    }

    /** Lowercase ASCII words separated by single spaces; thousands separators in numbers are dropped. */
    public function normalize(string $text): string
    {
        $text = Str::lower(Str::ascii($text));
        $text = preg_replace('/(?<=\d)[.,](?=\d{3}\b)/', '', $text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);

        return trim($text);
    }

    /** @return list<string> */
    public function words(string $text): array
    {
        $normalized = $this->normalize($text);

        return $normalized === '' ? [] : explode(' ', $normalized);
    }

    /** @return list<string> Content words: stopwords and single letters removed. */
    public function contentWords(string $text): array
    {
        return array_values(array_filter(
            $this->words($text),
            fn (string $word) => ! isset($this->stopwords[$word]) && (strlen($word) > 1 || ctype_digit($word)),
        ));
    }

    /** @return array<string, int> Stem => term frequency. Numbers are kept unstemmed. */
    public function stems(string $text): array
    {
        $stems = [];

        foreach ($this->contentWords($text) as $word) {
            $stem = $this->stem($word);
            $stems[$stem] = ($stems[$stem] ?? 0) + 1;
        }

        return $stems;
    }

    /**
     * Character trigrams of the content words joined without spaces, so compounds
     * written apart ("football club") still match the joined form ("footballclub").
     *
     * @return array<string, true>
     */
    public function trigrams(string $text, bool $contentOnly = true): array
    {
        $joined = implode('', $contentOnly ? $this->contentWords($text) : $this->words($text));

        if ($joined === '') {
            return [];
        }

        if (strlen($joined) < 3) {
            return [$joined => true];
        }

        $trigrams = [];
        for ($i = 0, $last = strlen($joined) - 3; $i <= $last; $i++) {
            $trigrams[substr($joined, $i, 3)] = true;
        }

        return $trigrams;
    }

    /** @return list<string> */
    public function numbers(string $text): array
    {
        return array_values(array_unique(array_filter($this->words($text), 'ctype_digit')));
    }

    public function hasNegation(string $text): bool
    {
        return array_intersect($this->words($text), self::NEGATIONS) !== [];
    }

    private function stem(string $word): string
    {
        if (ctype_digit($word)) {
            return $word;
        }

        return $this->stemCache[$word] ??= $this->stemmer->stem($word);
    }
}
