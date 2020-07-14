<?php

namespace Vlinde\StopWord;

use Vlinde\StopWord\Helpers\Functions;
use Vlinde\StopWord\Models\Keyword;

class StopWord
{
    const DEFAULT_LOCALE = 'en';

    public function createKeywords(
        string $string,
        int $wordsLongerThan = 2,
        int $numberOfWords = 4,
        string $locale = null
    ): int
    {
        $combinations = $this->generateCleanCombinations($string, $wordsLongerThan, $numberOfWords, $locale);

        $count = 0;

        if (empty($combinations)) {
            return $count;
        }

        $keywords = Keyword::where(function ($query) use ($combinations) {
            foreach ($combinations as $combination) {
                $query->orWhere('key', '=', $combination);
            }
        })->get();

        foreach ($combinations as $combination) {
            $keyword = $keywords->where('key', '=', $combination);

            if ($keyword->isEmpty()) {
                $this->generateNewKeyword($combination, $locale);
            } else {
                $this->increaseKeywordCounter($keyword);
            }
        }

        return $count;
    }

    protected function generateCleanCombinations(string $string, $wordsLongerThan = 2, $numberOfWords = 4, string $locale = null)
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

        $clean = mb_ereg_replace("/\b[a-z|A-Z]{1," . $wordsLongerThan . "}\b(?<!" . $implodedExceptions . ")/", "  ", $clean);

        $clean = explode(' ', $clean);

        $clean = array_filter($clean);

        $clean = array_unique($clean);
        $clean = array_map('mb_strtolower', $clean);

        return array_slice($clean, 0, $numberOfWords);
    }

    protected function generateNewKeyword(string $combination, string $locale, bool $sync = false): Keyword
    {
        $keyword = new Keyword();
        $keyword->key = $combination;
        $keyword->locale = $locale;

        if ($sync) {
            return tap($keyword)->save();
        }

        return $keyword->withoutSyncingToSearch(function () use ($keyword) {
            return tap($keyword)->save();
        });
    }

    protected function increaseKeywordCounter(Keyword $keyword, bool $sync = false): Keyword
    {
        $keyword->counter += 1;

        if ($sync) {
            return tap($keyword)->save();
        }

        return $keyword->withoutSyncingToSearch(function () use ($keyword) {
            return tap($keyword)->save();
        });
    }

    public function createKeywordsWithCombinations(
        string $string,
        int $wordsLongerThan = 2,
        int $numberOfWords = 4,
        string $locale = null
    ): int
    {
        $this->cleanWithStopwords($string, $wordsLongerThan, $numberOfWords, $locale);

        $count = 0;

        if (empty($clean)) {
            return $count;
        }

        $combinations = Functions::powerSet($clean);

        $count = count($combinations);

        $existing_keywords_all = Keyword::where(function ($query) use ($combinations) {
            foreach ($combinations as $combination) {
                $query->orWhere('key', '=', $combination);
            }
        })->get();

        foreach ($combinations as $combination) {

            $existing_keywords = $existing_keywords_all->where('key', '=', $combination);

            if ($existing_keywords->isNotEmpty()) {
                $empty_locale_keyword = $existing_keywords->where('locale', '=', '');

                if ($empty_locale_keyword->isNotEmpty()) {
                    $existing_keyword = $empty_locale_keyword->first();

                    $this->increaseKeywordCounter($existing_keyword);

                    continue;
                }

                $exists_locale_keyword = $existing_keywords->where('locale', '=', $locale);

                if ($exists_locale_keyword->isEmpty()) {

                    $this->generateNewKeyword($combination, $locale);
                } else {
                    $existing_keyword = $exists_locale_keyword->first();

                    $this->increaseKeywordCounter($existing_keyword);
                }
            } else {
                $this->generateNewKeyword($combination, $locale);
            }
        }

        return $count;
    }
}
