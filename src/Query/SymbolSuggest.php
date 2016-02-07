<?php namespace YahooFinanceQuery\Query;

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

use YahooFinanceQuery\Query\Query;

/**
* 
*/
class SymbolSuggest extends Query
{

    /**
     * @param string $yql
     */
    public function __construct($yql)
    {
        parent::__construct($yql);
    }

    /**
     * get a list of stocks with symbol and market for provided search string
     * from yahoo.finance.com's stock symbol autosuggest callback
     * @param string $queryString - the name/string to search for
     * @return self
     */
    public function query($queryString)
    {
        $this->queryString = $queryString;

        // set url for callback
        $this->baseUrl = 'http://d.yimg.com/aq/autoc?query=';
        $region = 'region=US';
        $lang = 'lang=en-US';
        $this->queryUrl = $this->baseUrl
            . urlencode($this->queryString) . '&' . $region . '&' . $lang
            . '&callback=YAHOO.util.ScriptNodeDataSource.callbacks';

        // deprecated
        // $this->queryUrl = 'http://d.yimg.com/autoc.finance.yahoo.com/autoc?query=' 
        //  .urlencode($this->queryString)
        //  .'&callback=YAHOO.Finance.SymbolSuggest.ssCallback';

        // curl request
        $this->curlRequest($this->queryUrl);

        // handel curl errors
        if ($this->response['errno']) {
            $result = array();
            $result["ok"] = false;
            $result["status"] = 500;
            $result["query"] = $this->queryString;
            $result["errno"] = $this->response['errno'];
            $result["error"] = $this->response['error'];
            $this->result = $result;
            return $this;
        }

        // read json
        $json = preg_replace('/.+?({.+}).+/', '$1', $this->response['result']);
        // convert json to array
        $response = json_decode($json);
        $data = $response->ResultSet->Result;
        if ($data) {
            $result = array();
            $result["ok"] = true;
            $result["status"] = 200;
            $result["query"] = $this->queryString;
            $result["symbols"] = $data;

            $this->result = $result;
        } else {
            //no data found
            $result = array();
            $result["ok"] = false;
            $result["status"] = 404;
            $result["query"] = $this->queryString;
            $result["error"] = "Could not find symbol for '" . $this->queryString . "'";
            $this->result = $result;
        }
        return $this;
    }
}