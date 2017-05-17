<?php
namespace App\Composer;
class EventsHandler{
	static function setup($event){
		$GLOBALS['ioDialogRedCat'] = $event->getIO();
		$php = 'vendor/redcatphp/redcatphp/artist';
		$_SERVER['argv'] = $GLOBALS['argv'] = [$php,'--plugins=plugins/artist="App\\Artist"','setup'];
		ob_start();
		include $php;
	}
	static function postUpdateCmd($event){
		return self::setup($event);
	}
	static function postCreateProjectCmd($event){
		return self::setup($event);
	}
}
