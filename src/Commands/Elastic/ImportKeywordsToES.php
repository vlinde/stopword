<?php

namespace Vlinde\StopWord\Commands\Elastic;

use Illuminate\Console\Command;
use Vlinde\StopWord\Jobs\KeywordsEsImportJob;
use Vlinde\StopWord\Models\Keyword;

class ImportKeywordsToES extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stopword:import:es:keywords {offset?} {limit?}';

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
        $offset = $this->argument('offset') ?? 0;
        $limit = $this->argument('limit') ?? 5000;

        $this->processKeywords($offset, $limit);

        $this->info('Operation finished');
    }

    protected function processKeywords($offset, $limit)
    {
        $count = Keyword::count();

        if($offset > 0) {
            $count -= $offset;
        }

        $chunks = ceil($count / $limit);

        for ($i = 1; $i <= (int)$chunks; $i++) {
            $job = (new KeywordsEsImportJob($offset, $limit))->onConnection('redis_queue')
                ->onQueue('high');

            dispatch($job);

            $offset += $limit;
        }
    }

}
