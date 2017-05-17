<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use ForceUTF8\Encoding;
use PDOException;
use Redis;
use DateTime;
use Exception;
use RedCat\CSVTools\DelimiterFinder;

class MyImportRuntask extends ArtistPlugin{
	
	protected $separator = ';';
	protected $db;
	protected $progress = [];
	protected $file;
	protected $pid;
	protected $dir = '.data/importing/';
	
	protected $requiredKeysByType = [
		'debtor'=>['primary'],
		'paperwork'=>['primary','debtor_primary'],
		'contact'=>['primary','debtor_primary'],
	];
	
	protected $description = "Run a csv import task";
	protected $args = [
		'instance_id'=>'instance id',
		'table'=>'table name',
		'context'=>'context',
	];
	protected $user;
	protected $table;
	protected $context;
	protected $ns;
	protected $instance_id;
	function __construct($name = null, MainDb $db, Redis $redis){
		parent::__construct($name);
		$this->db = $db;
		$this->redis = $redis;
		$this->pid = getmypid();
	}
	
	protected function exec(){

        set_time_limit(0);

        $this->instance_id = $this->input->getArgument('instance_id');
        $this->table = $this->input->getArgument('table');
        $this->context = $this->input->getArgument('context');
        if(!$this->context){
            $this->context = 'user';
        }
        $this->ns = 'my:tasks:running:import:'.$this->context.':';
        $this->file = $this->dir.$this->instance_id.'/'.$this->table.'.csv';

        if(!($this->instance_id&&$this->table)) return;
        $this->onlyInsert = $this->table == 'contact';

        if(!$this->onlyInsert){
            $this->db->updateOnDuplicateKey(true); //this approach need mysql
        }
        else{
            $this->db->enableInsertIgnore(true);
        }
		$this->runTask();
	}
	
	protected function runTask(){

		if(!file_exists($this->file)) return;


		//auto detect csv separator
		$csv = new DelimiterFinder($this->file);
		$this->separator = $csv->find();
		if(!$this->separator){
			throw new Exception('Unable to find CSV delimiter');
		}

		$this->progress = [
			'pid'=>$this->pid,
			'file'=>$this->file,
			'user'=>$this->instance_id,
			'table'=>$this->table,
			'state'=>'',
			'start'=>time(),
			'current'=>0,
			'context'=>$this->context,
		];
		
		//$fp = fopen($this->file,'r');
		//if(!$fp){
			//$this->progress([
				//'state'=>'error',
				//'error'=>'unable to open file '.$this->file,
			//]);
			//return;
		//}
		
		//use temp file for avoid ftp/http collision with import process
		$temp = sys_get_temp_dir().'/'.uniqid('myimportcsv',true);
		if(!copy($this->file,$temp)){
			$this->progress([
				'state'=>'error',
				'error'=>'unable to open file '.$this->file,
			]);
			return;
		}
        $fp = fopen($temp,'r');

        $i = 0;
		$linecount = 0;
		while(!feof($fp)){
			$line = fgets($fp, 4096);
			if(trim($line)){
				$linecount = $linecount + substr_count($line, PHP_EOL);
			}
		}
		rewind($fp);
		
		$this->progress([
			'total'=>$linecount,
		]);
				
		$r = $this->import($fp);
		if(!$r){
			trigger_error(print_r($this->progress,true));
			$this->progress('state','error');
		}
	}
	protected function progress($k,$v=null){
		$progress = $this->redis->hGet($this->ns.'progress',$this->pid);
		
		if($progress){
			$progress = json_decode($progress,true);
		}
		if(is_array($progress)){
			$this->progress = array_merge($progress,$this->progress);
		}
		
		if(is_array($k)){
			foreach($k as $key=>$v){
				$this->progress[$key] = $v;
			}
		}
		else{
			$this->progress[$k] = $v;
		}
		if((isset($this->progress['error'])&&$this->progress['error'])||$this->progress['state']=='error'){
			$this->progress['expire'] = time()+10;
			file_put_contents('.tmp/import-error.log',$this->progress['error'].' '.print_r($this->progress,true),FILE_APPEND);
			trigger_error($this->progress['error'].' '.print_r($this->progress,true));
		}
		$this->redis->hSet($this->ns.'progress',$this->pid,json_encode($this->progress));
	}
	static function normalizeColumn($string){
		return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '_', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '_'));
	}
	protected function import($fp){
		
		$import_timestamp = time();
		
		$required = $this->requiredKeysByType[$this->table];
		
		$columnsMap = $this->db['map_import_'.$this->table]->where('user_id = ?',[$this->instance_id])->getRow()->getArray();
		
		if(empty($columnsMap)||!isset($columnsMap['primary'])){
			$this->progress('error','unmapped file');
			return false;
		}
		foreach($required as $reqc){			
			if(!isset($columnsMap[$reqc])){
				$this->progress('error','incomplete db map: "'.$reqc.'" is missing');
				return false;
			}
		}
		
		$table = $this->db[$this->table];
		
		$this->progress('state','import');
		$i = 0;
		
		$maxQueries = 10000;
		$iQuery = 0;
		$rows = [];
		
		while(($line = fgetcsv($fp, 0, $this->separator)) !== false){
			
			$line = array_map([Encoding::class,'toUTF8'],$line);
			if($i==0){
				if(end($line)=='')
					array_pop($line);
				$line = array_map('self::normalizeColumn',$line);
				$columns = [];
				foreach($line as $y=>$col){
					$dbCol = array_search($col,$columnsMap);
					if($dbCol!==false){
						$columns[$y] = $dbCol;
					}
				}
				foreach($required as $reqc){
					if(!in_array($reqc,$columns)){
						$this->progress('error','incomplete csv file: "'.$columnsMap[$reqc].'" as "'.$reqc.'" is missing');
						return false;
					}
				}
			}
			else{
				$valid = true;
				$row = $table->entity();
				foreach($columns as $y=>$field){
					
					if(in_array($field,$required)){
						if(!isset($line[$y])||trim($line[$y])==''){
							$valid = false;
							break;
						}
					}
					
					$row[$field] = isset($line[$y])&&$line[$y]!=''?$line[$y]:null;
					
				}
				if($valid){
					$row['instance_id'] = $this->instance_id;
					$row['import_timestamp'] = $import_timestamp;
					$rows[] = $row;
				}
				if($iQuery>=$maxQueries){
					$iQuery = 0;
					$this->pushMulti($rows);
					$rows = [];
					$this->progress('current',$i);
					echo "$i\n";
				}
				$iQuery++;
			}
			$i++;
		}
		
		$this->pushMulti($rows);
		$this->progress('current',$i);
		
		if(!$this->onlyInsert){
			$this->progress('state','delete');
			$this->db->exec('DELETE FROM '.$this->table.' WHERE import_timestamp != ? AND instance_id = ?',[$import_timestamp,$this->instance_id]);
		}
		
		echo "finish\n";
		$this->progress('finish',true);
		fclose($fp);
		
		return true;
	}
	function pushMulti($rows){
		
		foreach($rows as $row){
			$row->trigger('beforePut');
			$row->trigger('beforeCreate');
		}
		
		$rows = $this->db->createQueryMulti($this->table,$rows);
		
		foreach($rows as $row){
			$row->trigger('afterCreate');
			$row->trigger('afterPut');
		}
	}
}
