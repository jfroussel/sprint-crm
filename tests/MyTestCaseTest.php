<?php

namespace MyApp\tests;


use PDO;
require 'FixturesTestCase.php';


class MyTestCaseTest extends FixturesTestCase {



    public $fixtures = array(
        'scenario',
        'scenario_step',
        'running_scenario',
        'debtor'
    );



        function testReadDatabase() {


            $conn = $this->getConnection()->getConnection();

            // fixtures auto loaded, let's read some data

            $query = $conn->query('SELECT * FROM scenario');
            $results = $query->fetchAll(PDO::FETCH_COLUMN);
            $this->assertEquals(9, count($results));
            var_dump('fixtures auto loaded, let\'s read some data'.$this->assertEquals(9, count($results)));

            // now delete them

            $conn->query('DELETE FROM scenario');
            $query = $conn->query('SELECT * FROM scenario');
            $results = $query->fetchAll(PDO::FETCH_COLUMN);
            $this->assertEquals(0, count($results));
            var_dump('now delete them'.$this->assertEquals(0, count($results)));

            // now reload them

            $ds = $this->getDataSet(array('scenario'));
            $this->loadDataSet($ds);
            $query = $conn->query('SELECT * FROM scenario');
            $results = $query->fetchAll(PDO::FETCH_COLUMN);
            $this->assertEquals(9, count($results));
            var_dump('now reload them'.$this->assertEquals(9, count($results)));
        }

        function testNumberOfDebtor(){
            $conn = $this->getConnection()->getConnection();
            $query = $conn->query('SELECT * FROM debtor');
            $results = $query->rowCount();
            var_dump('Numbers of debtor in debtor_table :'.$results);
        }

        function testNumberOfDebtorAfterTruncateDebtorTable(){
            $conn = $this->getConnection()->getConnection();
            $conn->query('DELETE FROM debtor');
            $query = $conn->query('SELECT * FROM debtor');
            $results = $query->rowCount();
            var_dump('Numbers of debtor in debtor_table after truncate debtor table :'.$results);
        }

        function testNumberOfDebtorWithNewDb(){
            $conn = $this->getConnection()->getConnection();
            $query = $conn->query('SELECT * FROM debtor');
            $results = $query->rowCount();
            var_dump('Numbers of debtor in debtor_table with new db:'.$results);
        }

}