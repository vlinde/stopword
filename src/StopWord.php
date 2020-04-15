<?php

namespace Vlinde\StopWord;

use Vlinde\StopWord\Helpers\Functions;
use Vlinde\StopWord\Models\Keyword;

class StopWord
{
    protected $defaultLocale = 'en';

    public function filter($string, $words_longer_than = 2, $number_of_words = 4, $locale = '')
    {
        $stopWordLocale = !empty($locale) ? $locale : $this->defaultLocale;

        $path = __DIR__ . '/../stopwords/' . $stopWordLocale . '.php';

        if (!file_exists($path)) {
            return 0;
        }

        $char_map = require $path;

        $replace_exceptions = ['php', 'js', 'bäckerei'];

        $imploded_exceptions = implode('|', $replace_exceptions);

        $clean = str_replace(array_keys($char_map), $char_map, " " . mb_strtolower($string) . " ");

        $clean = mb_ereg_replace("/\b[a-z|A-Z]{1," . $words_longer_than . "}\b(?<!" . $imploded_exceptions . ")/", "  ", $clean);

        $clean = explode(' ', $clean); // separate by space

        $clean = array_filter($clean);  // remove empty values from array

        $clean = array_unique($clean); // remove duplicates

        $clean = array_map('mb_strtolower', $clean);

        $clean = array_slice($clean, 0, $number_of_words);   // limit number of results

        $count = 0;

        if (empty($clean)) {
            return $count;
        }

        $combinations = Functions::powerSet($clean);

        $count = count($combinations);

        $existing_keywords_all = Keyword::where(function($query) use($combinations) {
            foreach($combinations as $combination) {
                $query->orWhere('key', '=', $combination);
            }
        })->get();

        foreach ($combinations as $combination) {

            $existing_keywords = $existing_keywords_all->where('key', '=', $combination);

            if($existing_keywords->isNotEmpty()) {
                $empty_locale_keyword = $existing_keywords->where('locale', '=', '');

                if($empty_locale_keyword->isNotEmpty()) {
                    $existing_keyword = $empty_locale_keyword->first();

                    $existing_keyword->counter += 1;
                    $existing_keyword->withoutSyncingToSearch(function () use ($existing_keyword) {
                        $existing_keyword->save();
                    });

                    continue;
                }

                $exists_locale_keyword = $existing_keywords->where('locale', '=', $locale);

                if($exists_locale_keyword->isEmpty()) {

                    $new_keyword = new Keyword();
                    $new_keyword->key = $combination;
                    $new_keyword->locale = $locale;

                    $new_keyword->withoutSyncingToSearch(function () use ($new_keyword) {
                        $new_keyword->save();
                    });
                } else {
                    $existing_keyword = $exists_locale_keyword->first();

                    $existing_keyword->counter += 1;

                    $existing_keyword->withoutSyncingToSearch(function () use ($existing_keyword) {
                        $existing_keyword->save();
                    });
                }
            } else {
                $new_keyword = new Keyword();
                $new_keyword->key = $combination;
                $new_keyword->locale = $locale;

                $new_keyword->withoutSyncingToSearch(function () use ($new_keyword) {
                    $new_keyword->save();
                });
            }
        }

        return $count;

    }
}
