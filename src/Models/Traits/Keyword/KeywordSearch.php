<?php

namespace Vlinde\StopWord\Models\Traits\Keyword;

trait KeywordSearch
{

	public function buildDocument()
	{
		$data = [
			'id'          => $this->id ,
			'key'         => $this->key ,
			'key_suggest' => $this->key ,
			'counter'     => $this->counter ,
		];

		return $data;
	}
}
