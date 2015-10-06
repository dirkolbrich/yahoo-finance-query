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
class SectorList extends Query
{
    
    function __construct($yql)
    {
        parent::__construct($yql);
    }

    /**
    *   get full list of sectors with corresponding industries from yahoo.finance.com
    *
    *   @return array $sectorsList - array with sectors
    */
    public function query() {
        // query via YQL console ist broken due to parsing error in yql statement
        // if ($this->yql) { // request via yql console
        //     $data = $this->queryYQL();
        // } else { // direct request via .csv
        //     $data = $this->queryDirect();
        // }
        $data = $this->queryDirect();

        $this->result = $data;
        return $this;
    }

    private function queryDirect() {
        //set request url
        $this->baseUrl = 'http://biz.yahoo.com/ic/ind_index.html';
        $this->queryUrl = $this->baseUrl;

        //curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        // parse html
        $dom = new \DOMDocument();
        @$dom->loadHTML($this->response['result']);
        $dom->preserveWhiteSpace = false;
        $body = new \DOMXPath($dom);

        // $body->query('//td[@width=\'50%\']//table//tr//td[@colspan=\'2\']//b')

        // query dom for sector names
        $sectorList = [];
        $i = 0;
        foreach ($body->query('//td[@width=\'50%\']//table//tr') as $node) {
            if ($node->firstChild->hasAttribute('colspan') && strlen($node->firstChild->nodeValue) > 0) {
                $sectorList[$i]['name'] = $node->firstChild->nodeValue;
                $i++;
            } elseif ($node->firstChild->hasAttribute('width')) {
                $name = $node->lastChild->nodeValue;

                $href = $node->lastChild->firstChild->firstChild->getAttribute('href');
                preg_match("/(?P<id>\d+)\.html$/", $href, $output_array);
                $id = $output_array['id'];

                $industry = [
                    'name'  => $name,
                    'id'    => $id,
                ];

                // attach to array
                $k = $i - 1;
                $sectorList[$k]['industries'][] = $industry;
            }
        }

        return $sectorList;
    }

    /**
     * query via YQL console
     */
    private function queryYQL()
    {
        //set yql query
        $yql_query = 'select * from yahoo.finance.sectors';
        //set request url
        $this->baseUrl = 'http://query.yahooapis.com/v1/public/yql?q=';
        $config = '&format=json&env=store://datatables.org/alltableswithkeys&callback=';
        $this->queryUrl = $this->baseUrl . rawurlencode($yql_query) . $config;

        //curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        //read json
        $object = json_decode($this->response['result'], true);
        //check if some data is returned
        if (is_null($object['query']['results'])) {
            return $data = [];
        }
        //select object node
        $data = $object['query']['results']['sector'];
        //check array
        if (empty($data)) {
            return $data = [];
        } else {
            //sanitize data for sector with single industry
            foreach ($data as $key => $sector) {
                if (!is_array($sector['industries'][0])) {
                    $data[$key]['industries'] = array($sector['industries']);
                }
            }
        }

        return $data;
    }
}