<?php

namespace Vlinde\StopWord\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Vlinde\StopWord\Models\Keyword;

/**
 * @method int createKeywords(string $string, int $wordsLongerThan, int $numberOfWords, $locale, bool $withCombinations)
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
