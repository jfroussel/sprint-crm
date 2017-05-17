<?php
namespace App\Artist;
use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\ProgressBar;
use ForceUTF8\Encoding;
use PDOException;
use DateTime;
use Exception;

class MyImportSynchronous extends ArtistPlugin{
	protected $description = "Import routine from csv lines format to a database";

	protected $args = [
		'dir'=>'The storage directory',
		'files'=>'The csv files list separated by commas',
	];
	protected $opts = [
	];
	
	protected $defaultDir = '.data/importing/';
	protected $defaultFiles = 'debtor.csv,paperwork.csv';
	protected $separator = ';';
	protected $db;
	protected $requiredKeysByType = [
		'debtor'=>['primary'],
		'paperwork'=>['primary','debtor_primary'],
	];
	function __construct($name = null, MainDb $db){
		parent::__construct($name);
		$this->db = $db;
	}
	
	protected function exec(){
		$this->db->updateOnDuplicateKey(true); //this approach need mysql
		$dir = $this->input->getArgument('dir');
		if(!$dir)
			$dir = $this->defaultDir;
		$dir = rtrim($dir,'/').'/';
		
		$files = $this->input->getArgument('files');
		if(!$files)
			$files = $this->defaultFiles;
		$files = explode(',',$files);
		
		foreach(glob($this->cwd.$dir.'*',GLOB_ONLYDIR) as $d){
			$userId = basename($d);
			foreach($files as $file){
				$f = $d.'/'.$file;
				$type = pathinfo($file,PATHINFO_FILENAME);
				if(!is_file($f)) continue;
				$r = $this->import($f,$type,$userId);
				if(!$r){
					$this->output->writeln('skipping importation');
				}
				$this->output->writeln('');
			}
		}
	}
	static function normalizeColumn($string){
		return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '_', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '_'));
	}
	protected function import($file,$type,$userId){
		$this->output->writeln('importing '.$type.' for user '.$userId);
		
		$required = $this->requiredKeysByType[$type];
		
		$columnsMap = $this->db['map_import_'.$type]->where('user_id = ?',[$userId])->getRow()->getArray();
		
		if(empty($columnsMap)||!isset($columnsMap['primary'])){
			$this->output->writeln('unmapped file');
			return false;
		}
		foreach($required as $reqc){			
			if(!isset($columnsMap[$reqc])){
				$this->output->writeln('incomplete db map: "'.$reqc.'" is missing');
				return false;
			}
		}
		
		$table = $this->db[$type];
		
		$temporaryImportTable = $this->db['temporary_import'];
		if($temporaryImportTable->exists()){
			$this->output->writeln('clean temporary table');
			$this->db->exec('DELETE FROM temporary_import WHERE user_id = ? AND type = ?',[$userId,$type]);
		}
		
		$fp = fopen($file,'r');
		$i = 0;
		$linecount = 0;
		while(!feof($fp)){
			$line = fgets($fp, 4096);
			if(trim($line)){
				$linecount = $linecount + substr_count($line, PHP_EOL);
			}
		}
		$progress = new ProgressBar($this->output, $linecount);
		rewind($fp);
		
		$this->output->writeln('insert or update');
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
						$this->output->writeln('incomplete csv file: "'.$reqc.'" is missing');
						return false;
					}
				}
			}
			else{
				$row = $table->entity();
				foreach($columns as $i=>$field){
					$row[$field] = isset($line[$i])&&$line[$i]!=''?$line[$i]:null;
					if(substr($field,0,5)=='date_'){
						$date = trim($row[$field]);
						if($date){
							try{
								$date = str_replace(['-',' '],'/',$date);
								$format = 'd/m/Y';
								if(strpos($date,'/')===false){
									$format = 'Ymd';
								}
								$date = DateTime::createFromFormat($format, $date);
								if($date){
									$date = $date->format('Y-m-d');
								}
							}
							catch(Exception $e){
								$date = null;
							}
						}
						else{
							$date = null;
						}
						$row[$field] = $date;
					}
				}
				$row['_one_user_x_'] = $userId;
				try{
					$table[] = $row;
					$temporaryImportTable[] = ['type'=>$type,'_one_user_x_'=>$userId,'_one_'.$type.'_x_'=>$row];
				}
				catch(PDOException $e){
					$this->output->writeln($e->getMessage());
					return false;
				}
			}
			$i++;
			$progress->advance();
		}
		
		$this->output->writeln('remove deleted');
		$this->db->exec('DELETE FROM '.$type.' WHERE NOT EXISTS (SELECT * FROM temporary_import WHERE '.$type.'.id = temporary_import.'.$type.'_id)');
		
		$progress->finish();
		$this->output->writeln('');
		$this->output->writeln('done');
		$this->output->writeln('');
		fclose($fp);
		return true;
	}
}
