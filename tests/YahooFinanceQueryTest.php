<?php

use DirkOlbrich\YahooFinanceQuery\YahooFinanceQuery;

class YahooFinanceQueryTest extends PHPUnit_Framework_TestCase {

  public function testConfigIsArray()
  {
    $query = YahooFinanceQuery::make();
    $config = $query->getConfig();
    $this->assertTrue($config['returnType'] == 'array');
  }

  public function testConfigIsJson()
  {
    $query = YahooFinanceQuery::make();
    $array = array('returnType' => 'json');
    $query->config($array);
    $config = $query->getConfig();
    $this->assertTrue($config['returnType'] == 'json');
  }

}
