<?php

namespace Vlinde\StopWord\Commands\Elastic;

use Illuminate\Console\Command;
use Vlinde\StopWord\Jobs\ReindexKeywordsInElasticJob;
use Vlinde\StopWord\Models\Keyword;

class ReindexKeywordsInElastic extends Command
{
    const DEFAULT_OFFSET = 0;
    const DEFAULT_LIMIT = 5000;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reindex:es:keywords {offset?} {limit?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex keywords in Elasticsearch';
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
        $offset = $this->argument('offset') ?? self::DEFAULT_OFFSET;
        $limit = $this->argument('limit') ?? self::DEFAULT_LIMIT;

        $this->processKeywords($offset, $limit);

        $this->info('Operation finished');
    }

    /**
     * @param int $offset
     * @param int $limit
     */
    private function processKeywords(int $offset, int $limit)
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
