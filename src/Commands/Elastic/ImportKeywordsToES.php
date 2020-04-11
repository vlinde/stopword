<?php

namespace Vlinde\StopWord\Commands\Elastic;

use Illuminate\Console\Command;
use Sleimanx2\Plastic\Facades\Plastic;
use Vlinde\StopWord\Models\Keyword;

class ImportKeywordsToES extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:es:keywords {offset?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import keywords to Elasticsearch';

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
        $offset = $this->argument('offset');

        if (is_null($offset)) {
            $offset = 0;
        }

        $limit = 5000;
        $this->processKeywords($offset, $limit);
        $offset += $limit;
        $this->info(memory_get_usage());
        $this->call('import:es:keywords', ['offset' => $offset]);
    }


    protected function processKeywords($offset, $limit)
    {
        $keywords = Keyword::offset($offset)->limit($limit)->get();

        if (empty($keywords)) {
            $this->info('Operation finished');
            exit();
        }

        Plastic::persist()->bulkSave($keywords);
        $max = $offset + $limit;
        $this->info("Indexed keywords to ES  from {$offset} to {$max}.");
        unset($keywords);
    }
}
