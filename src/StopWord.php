<?php

namespace Vlinde\StopWord;

use Illuminate\Support\Collection;
use Vlinde\StopWord\Helpers\Functions;
use Vlinde\StopWord\Models\Keyword;

class StopWord
{
    const DEFAULT_LOCALE = 'en';

    public function createKeywords(
        string $string,
        int $wordsLongerThan = 2,
        int $numberOfWords = 4,
        string $locale = null,
        bool $withCombinations = true
    ): int
    {
        $combinations = $this->generateCleanCombinations(
            $string,
            $wordsLongerThan,
            $numberOfWords,
            $locale
        );

        if (empty($combinations)) {
            return 0;
        }

        $count = 0;

        if ($withCombinations) {
            $combinations = Functions::powerSet($combinations, 1);
        }

        $combinations = array_filter($combinations, function ($combination) use ($wordsLongerThan) {
            return strlen($combination) > $wordsLongerThan;
        });

        $keywords = $this->findKeywordsByCombinations($combinations);

        foreach ($combinations as $combination) {
            $keyword = $keywords->where('key', '=', $combination)
                ->when(isset($locale), function ($collection) use ($locale) {
                    return $collection->where('locale', $locale);
                })
                ->first();

            if (!$keyword) {
                $this->generateNewKeyword($combination, $locale);

                $count++;
            } else {
                $this->increaseKeywordCounter($keyword);
            }
        }

        return $count;
    }

    protected function generateCleanCombinations(
        string $string,
               $wordsLongerThan = 2,
               $numberOfWords = 4,
        string $locale = null
    )
    {
        $stopWordLocale = $locale ?? self::DEFAULT_LOCALE;

        $path = __DIR__ . '/../stopwords/' . $stopWordLocale . '.php';

        if (!file_exists($path)) {
            return 0;
        }

        $charMap = require $path;

        $replaceExceptions = ['php', 'js', 'bäckerei'];

        $implodedExceptions = implode('|', $replaceExceptions);

        $clean = str_replace(array_keys($charMap), $charMap, " " . mb_strtolower($string) . " ");

        $clean = mb_ereg_replace(
            "/\b[a-z|A-Z]{1," . $wordsLongerThan . "}\b(?<!" . $implodedExceptions . ")/",
            "  ",
            $clean
        );

        $clean = explode(' ', $clean);

        $clean = array_filter($clean);

        $clean = array_unique($clean);
        $clean = array_map('mb_strtolower', $clean);

        return array_slice($clean, 0, $numberOfWords);
    }

    protected function findKeywordsByCombinations(array $combinations): Collection
    {
        return Keyword::where(function ($query) use ($combinations) {
            foreach ($combinations as $combination) {
                $query->orWhere('key', '=', $combination);
            }
        })->get();
    }

    protected function generateNewKeyword(
        string $combination,
        string $locale = null,
        bool $sync = false
    ): Keyword
    {
        $keyword = new Keyword();
        $keyword->key = $combination;
        $keyword->locale = $locale ?? '';

        if ($sync) {
            return tap($keyword)->save();
        }

        return Keyword::withoutSyncingToSearch(function () use ($keyword) {
            return tap($keyword)->save();
        });
    }

    protected function increaseKeywordCounter(Keyword $keyword, bool $sync = false): Keyword
    {
        $keyword->counter += 1;

        if ($sync) {
            return tap($keyword)->save();
        }

        return Keyword::withoutSyncingToSearch(function () use ($keyword) {
            return tap($keyword)->save();
        });
    }
}
