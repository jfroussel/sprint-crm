<?php
namespace App\Model\Entity;
use App\Model\TableModel;

class User_Table extends TableModel{
	//protected $loadColumns = ['*'];
	//protected $dontLoadColumns = ['password'];
	protected $uniqTextKey = 'email';
	protected $uniqColumns = ['email'];
}
