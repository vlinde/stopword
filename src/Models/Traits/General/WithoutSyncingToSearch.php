<?php

namespace Vlinde\StopWord\Models\Traits\General;

trait WithoutSyncingToSearch
{
	public function withoutSyncingToSearch($callback)
	{
		$this->syncDocument = false;

		try {
			return $callback();
		} finally {
			$this->syncDocument = true;
		}
	}

}
