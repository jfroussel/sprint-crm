<?php
namespace App\Artist;


use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Redis;

class MyDebtorRoutine extends ArtistPlugin{
	
	protected $description = "Routine to add all night scenario tasks";
	protected $args = [];
	protected $opts = [];
	protected $boolOpts = [];
	function __construct($name = null, Redis $redis, MainDb $db){
		parent::__construct($name);
		$this->redis = $redis;
		$this->db = $db;

	}

	protected function exec(){
	    //$this->db->debug();
		foreach($this->db['debtor'] as $debtor){
			$id = $debtor->id;
			$artist = $this->cwd.'artist';
			$task = [
				'key'=>'scenario',
				'cmd'=>"php $artist my:scenario:rundebtor $id",
				'uniq'=>"scenario:debtor:$id",
			];
			$json = json_encode($task);
			$this->redis->rPush('my:tasks:waiting',$json);


		}
	}
}
