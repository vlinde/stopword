<?php

namespace Vlinde\StopWord\Commands\Elastic;

use Illuminate\Console\Command;
use Vlinde\StopWord\Jobs\ReindexKeywordsInElasticJob;
use Vlinde\StopWord\Models\Keyword;

class ReindexKeywordsInElastic extends Command
{
    /**
     * @var string
     */
    private $connection;

    /**
     * @var string
     */
    private $queue;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reindex:es:keywords {offset=0} {limit=5000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex keywords in Elasticsearch';

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
    public function handle(): void
    {
        $offset = (int)$this->argument('offset');
        $limit = (int)$this->argument('limit');

        $this->processKeywords($offset, $limit);
    }

    /**
     * Add keywords in queue
     *
     * @param int $offset
     * @param int $limit
     *
     * @return void
     */
    private function processKeywords(int $offset, int $limit): void
    {
        $keywordsCount = Keyword::count();

        if ($offset > 0) {
            $keywordsCount -= $offset;
        }

        $chunks = (int)ceil($keywordsCount / $limit);

        for ($i = 1; $i <= $chunks; $i++) {
            ReindexKeywordsInElasticJob::dispatch($offset, $limit)
                ->onConnection($this->connection)
                ->onQueue($this->queue);

            $offset += $limit;
        }
    }
}
