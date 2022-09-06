<?php

namespace Vlinde\StopWord\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Vlinde\StopWord\Models\Keyword;

/**
 * @method int createKeywords(string $string, int $wordsLongerThan, int $numberOfWords, $locale, bool $withCombinations)
 * @method int|array generateCleanCombinations(string $string, int $wordsLongerThan, int $numberOfWords, $locale)
 * @method Collection findKeywordsByCombinations(array $combinations)
 * @method Keyword generateNewKeyword(string $combination, $locale, bool $sync)
 * @method Keyword increaseKeywordCounter(Keyword $keyword, bool $sync)
 *
 * @see \Vlinde\StopWord\StopWord
 */
class StopWord extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stopword';
    }
}
