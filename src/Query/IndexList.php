<?php namespace DirkOlbrich\YahooFinanceQuery\Query;

/**
 * YahooFinanceQuery - a PHP package to query the Yahoo Finance API
 *
 * @author      Dirk Olbrich <mail@dirkolbrich.de>
 * @copyright   2013-2015 Dirk Olbrich
 * @link        https://github.com/dirkolbrich/YahooFinanceQuery
 * @license     MIT
 * @version     1.0.0
 * @package     YahooFinanceQuery
 */

use DirkOlbrich\YahooFinanceQuery\Query\Query;

/**
 * 
 */
class IndexList extends Query
{
    
    function __construct($yql)
    {
        parent::__construct($yql);
    }

    /**
     * get list of component for indices symbol from yahoo.finance.com
     *
     * @param mixed $symbol
     */
    function query(array $symbol)
    {
        $this->queryString = $symbol;

        if ($this->yql) { // request via yql console
            $data = $this->queryYQL();
        } else { // direct request via .csv
            $data = $this->queryDirect();
            // $data = $this->queryCSV($symbols); // query via csv is broken on finance.yahoom.com
        }

        $this->result = $data;
        return $this;
    }

    /**
     * query finance.yahoo.com via screen scrapper
     */
    private function queryDirect()
    {
        foreach ($this->queryString as $indexSymbol) {

            //set request url
            $this->baseUrl = 'http://finance.yahoo.com/q/cp?s=';
            $config = '+Components';
            $this->queryUrl = $this->baseUrl . urlencode($indexSymbol) . $config;

            //curl request
            $this->curlRequest($this->queryUrl);
            
            // parse html
            $dom = new \DOMDocument();
            @$dom->loadHTML($this->response['result']);
            $dom->preserveWhiteSpace = false;
            $body = new \DOMXPath($dom);

            // query DOM for key
            $keyList = [];
            foreach ($body->query('//table[@id="yfncsumtab"]//table[@class="yfnc_tableout1"]//th[@class="yfnc_tablehead1"]') as $node) {
                $keyList[] = str_replace(' ', '', rtrim($node->nodeValue, ':'));
            }

            // query dom for entrys
            $index = [];
            $table = $body->query('//table[@id="yfncsumtab"]//table[@class="yfnc_tableout1"]//table//tr');

            for ( $i = 1; $i < $table->length; $i++ ) {
                $hit = $body->query('.//td[@class="yfnc_tabledata1"]', $table->item($i));
                // get values from nodes
                $symbol = $hit->item(0)->nodeValue;
                $name = $hit->item(1)->nodeValue;
                // make array
                $temp = [ $keyList[0] => $symbol, $keyList[1] => $name ];
                $index[] = $temp;
            }

            // set array
            $indexData['symbol'] = $indexSymbol;
            $indexData['components'] = $index;
            $data[] = $indexData;
        }

        return $data;
    }

    private function queryCSV()
    {
        foreach ($this->queryString as $indexSymbol) {
            $symbolList[] = urlencode('@' . $indexSymbol);
        }
        $symbolString = implode('+', $symbolList);

        //set request url
        $this->baseUrl = 'http://download.finance.yahoo.com/d/quotes.csv?s=';
        $config = '&f=snx&e=.csv';
        $this->queryUrl = $this->baseUrl . $symbolString . $config;

        //curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }
        
        //parse csv
        $result = str_getcsv($this->response['result'], "\n"); //parse rows
        foreach ($result as &$row) { //parse items in row
            $row = str_getcsv($row);
        }
        unset($row);
        //split up data if multiple symbols are requested
        $data = [];
        $resultHolder = [];
        reset($symbol);
        foreach ($result as $resultKey => $resultVal) {
            //check if array has empty element = delimiter
            if ((count($resultVal) == 1) and ($resultVal[0] === null)) {
                $data[current($symbol)] = $resultHolder; // dump $resultHolder to data array
                $resultHolder = []; // reset $resultHolder
                next($symbol);
            } else {
                $resultHolder[] = $resultVal;
            }
        }

        return $data;
        }

    private function queryYQL() {
        $data = [];
        return $data;
    }
}