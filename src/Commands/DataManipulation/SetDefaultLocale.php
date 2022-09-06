<?php

namespace Vlinde\StopWord\Commands\DataManipulation;

use Illuminate\Console\Command;
use Vlinde\StopWord\Models\Keyword;

class SetDefaultLocale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stopword:set:keyword:default:locale {locale=de}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set locale to keywords with empty locale';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(1800);

        $locale = $this->argument('locale');

        Keyword::select('id', 'locale')
            ->where('locale', '=', '')
            ->chunkById(1000, function ($keywords) use ($locale) {
                foreach ($keywords as $key => $keyword) {
                    $keyword->locale = $locale;
                    $keyword->save();
                }
            });
    }
}
