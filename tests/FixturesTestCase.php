<?php
namespace MyApp\tests;

use PDO;
use RedCat\Framework\App;

class FixturesTestCase extends \PHPUnit_Extensions_Database_TestCase {

    public $fixtures = array(
        'scenario',
        'scenario_step',
        'running_scenario',
        'debtor'
    );

    private $conn = null;
    public $global;

    public function getGlobalRedcat(){
        if(!isset($this->global)){
            $redcat = $GLOBALS['redcat'];
            $this->global = $redcat;
        }
        return $this->global;
    }

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=dbphpunit', 'jeff', '');
                $this->conn = $this->createDefaultDBConnection($pdo, 'test');
            } catch (\PDOException $e) {
                echo $e->getMessage();
            }
        }
        return $this->conn;
    }

    public function getDataSet($fixtures = array()) {
        if (empty($fixtures)) {
            $fixtures = $this->fixtures;
        }
        $compositeDs = new
        \PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());
        $fixturePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixtures';

        foreach ($fixtures as $fixture) {
            $path =  $fixturePath . DIRECTORY_SEPARATOR . "$fixture.xml";
            $ds = $this->createMySQLXMLDataSet($path);
            $compositeDs->addDataSet($ds);
        }
        return $compositeDs;
    }

    public function loadDataSet($dataSet) {
        // set News data
        $this->getDatabaseTester()->setDataSet($dataSet);
        // call setUp() and add data
        $this->getDatabaseTester()->onSetUp();
    }
}
