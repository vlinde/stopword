<?php

namespace Vlinde\StopWord\Models\Traits\Keyword;

use ElasticScoutDriverPlus\QueryDsl;
use Laravel\Scout\Searchable;

trait KeywordSearch
{
    use Searchable, QueryDsl;

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'counter' => $this->counter,
            'locale' => $this->locale
        ];
    }
}
