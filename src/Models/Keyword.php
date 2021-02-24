<?php

namespace Vlinde\StopWord\Models;

use Illuminate\Database\Eloquent\Model;
use Vlinde\StopWord\Models\Traits\Keyword\KeywordSearch;

class Keyword extends Model
{
    use KeywordSearch;

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @inheritDoc
     */
    public function getQueueableRelations()
    {
        // TODO: Implement getQueueableRelations() method.
    }

    /**
     * @inheritDoc
     */
    public function getQueueableConnection()
    {
        // TODO: Implement getQueueableConnection() method.
    }

    /**
     * @inheritDoc
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // TODO: Implement resolveRouteBinding() method.
    }

    /**
     * @inheritDoc
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
