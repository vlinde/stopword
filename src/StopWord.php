<?php

namespace Vlinde\StopWord;

use Vlinde\StopWord\Helpers\Functions;
use Vlinde\StopWord\Models\Keyword;

class StopWord
{
    public function filter($string, $words_longer_than = 2, $number_of_words = 4, $locale = 'en')
    {
        $path = __DIR__ . '/../stopwords/' . $locale . '.php';

        if (!file_exists($path)) {
            return 0;
        }

        $char_map = require_once $path;

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

        foreach ($combinations as $combination) {
            if ($existing_keyword = Keyword::where([
                'key' => $combination,
                'locale' => $locale
            ])->first()) {
                $existing_keyword->counter += 1;

                $existing_keyword->withoutSyncingToSearch(function () use ($existing_keyword) {
                    $existing_keyword->save();
                });
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
