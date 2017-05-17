<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use RedCat\Framework\App;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\Helper;

use Redis;

class MyImportRoutine extends ArtistPlugin{
	
	protected $description = "Routine to add all night import tasks";
	protected $args = [];
	protected $boolOpts = [];
	protected $opts = [];
	protected $ns;
	protected $dir = '.data/importing/';
	protected $tables = ['debtor','paperwork', 'contact'];
	
	function __construct($name = null, Redis $redis){
		parent::__construct($name);
		//$this->redis = $redis;
	}
	
	protected function exec(){
		$artist = $this->cwd.'artist';
		foreach(glob($this->dir.'*',GLOB_ONLYDIR) as $d){
			$user = basename($d);
			if((string)(int)$user!=$user) continue;
			foreach($this->tables as $table){
				//$this->runCmd('my:import:addtask', [$user,$table,'routine']);
				exec("php $artist my:import:addtask $user $table routine");
			}
		}
	}
}
