<?php

namespace Vlinde\StopWord\Models;

use Illuminate\Database\Eloquent\Model;
use Vlinde\StopWord\Models\Traits\Keyword\KeywordSearch;

class Keyword extends Model
{
    use KeywordSearch;

    public $timestamps = false;

    protected $guarded = [];
}
