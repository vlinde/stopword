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

    private $connection;
    private $queue;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = config('stopword.redis_connection');
        $this->queue = config('stopword.redis_queue');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
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

        if ($offset > 0) {
            $count -= $offset;
        }

        $chunks = (int)ceil($count / $limit);

        for ($i = 1; $i <= $chunks; $i++) {
            KeywordsEsImportJob::dispatch($offset, $limit)
                ->onConnection($this->connection)
                ->onQueue($this->queue);

            $offset += $limit;
        }
    }

}
