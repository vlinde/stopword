<?php

namespace Vlinde\StopWord\Commands\Elastic;

use Illuminate\Console\Command;
use Vlinde\StopWord\Jobs\KeywordsEsImportJob;
use Vlinde\StopWord\Models\Keyword;

class ImportKeywordsToES extends Command
{
    private const DEFAULT_OFFSET = 0;
    private const DEFAULT_LIMIT = 5000;

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
        $offset = (int)$this->argument('offset') ?: self::DEFAULT_OFFSET;
        $limit = (int)$this->argument('limit') ?: self::DEFAULT_LIMIT;

        $this->processKeywords($offset, $limit);

        $this->info('Operation finished');
    }

    /**
     * Add keywords in queue
     *
     * @param int $offset
     * @param int $limit
     *
     * @return void
     */
    protected function processKeywords(int $offset, int $limit): void
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
