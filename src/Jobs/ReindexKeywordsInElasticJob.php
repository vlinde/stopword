<?php

namespace Vlinde\StopWord\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vlinde\StopWord\Models\Keyword;

class ReindexKeywordsInElasticJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $skip;

    /**
     * @var int
     */
    public $take;

    /**
     * Create a new job instance.
     *
     * @param int $skip
     * @param int $take
     */
    public function __construct(int $skip, int $take)
    {
        $this->skip = $skip;
        $this->take = $take;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $keywords = Keyword::skip($this->skip)
            ->take($this->take)
            ->get();

        if ($keywords->isEmpty()) {
            return;
        }

        foreach ($keywords as $keyword) {
            $keyword->searchable();
        }
    }
}
