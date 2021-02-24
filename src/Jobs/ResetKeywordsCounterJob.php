<?php

namespace Vlinde\StopWord\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vlinde\StopWord\Models\Keyword;

class ResetKeywordsCounterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $skip;
    public $take;

    /**
     * Create a new job instance.
     *
     * @param $skip
     * @param $take
     */
    public function __construct($skip, $take)
    {
        $this->skip = $skip;
        $this->take = $take;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $keywords = Keyword::skip($this->skip)
            ->take($this->take)
            ->get();

        if ($keywords->isEmpty()) {
            return;
        }

        Keyword::withoutSyncingToSearch(function () use ($keywords) {
            $keywords->each(function ($keyword) {
                $keyword->counter = 0;

                $keyword->save();
            });
        });
    }
}
