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
    protected $signature = 'stopword:set:keyword:default:locale {locale?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(1800);

        $locale = $this->argument('locale') ?? 'de';

        Keyword::where('locale', '=', '')->chunk(1000, function ($keywords) use ($locale) {

            foreach ($keywords as $key => $keyword) {
                $keyword->locale = $locale;
                $keyword->save();
            }

        });

    }
}
