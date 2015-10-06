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
class StockInfo extends Query
{
    
    function __construct($yql)
    {
        parent::__construct($yql);
    }

    /**
    *   query url for stock info via direct query
    */
    public function query($symbol)
    {
        $this->queryString = $symbol;
        // set request url
        $this->baseUrl = 'http://finance.yahoo.com/q/pr?s=';
        $this->queryUrl = $this->baseUrl . urlencode($this->queryString);

        // curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        // parse html
        $dom = new \DOMDocument();
        @$dom->loadHTML($this->response['result']);
        $dom->preserveWhiteSpace = false;
        $body = new \DOMXPath($dom);
        $data = array();
        $i = 0;
        // query DOM for key
        foreach ($body->query('//td[@class="yfnc_modtitlew1"]//table[@class="yfnc_datamodoutline1"]//td[@class="yfnc_tablehead1"]') as $node) {
            $data[$i]['key'] = str_replace(' ', '', rtrim($node->nodeValue, ':'));
            $i++;
        }
        $i = 0;
        // query DOM for values
        foreach ($body->query('//td[@class="yfnc_modtitlew1"]//table[@class="yfnc_datamodoutline1"]//td[@class="yfnc_tabledata1"]') as $node) {
            $data[$i]['value'] = $node->nodeValue;
            $i++;
        }

        // rearrange as simple array
        $list = array();
        foreach ($data as $dataEntry) {
            $list[$dataEntry['key']] = $dataEntry['value'];
        }

        $this->result = $list;
        return $this;
    }    
}