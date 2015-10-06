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
class SymbolSuggest extends Query
{

    public function __construct($yql)
    {
        parent::__construct($yql);
    }


    /**
     * get a list of stocks with symbol and market for provided search string
     * from yahoo.finance.com's stock symbol autosuggest callback
     * @param string $string - the name/string to search for
     * @return self
     */
    public function query($queryString)
    {
        $this->queryString = $queryString;

        // set url for callback
        $this->baseUrl = 'http://d.yimg.com/aq/autoc?query=';
        $region = 'region=US';
        $lang = 'lang=en-US';
        $this->queryUrl = $this->baseUrl . urlencode($this->queryString) . '&' . $region . '&' . $lang . '&callback=YAHOO.util.ScriptNodeDataSource.callbacks';

        // deprecated
        // $this->queryUrl = 'http://d.yimg.com/autoc.finance.yahoo.com/autoc?query=' . urlencode($this->queryString) . '&callback=YAHOO.Finance.SymbolSuggest.ssCallback';

        // curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        // read json
        $json = preg_replace('/.+?({.+}).+/', '$1', $this->response['result']);

        // convert json to array
        $object = json_decode($json);
        $data = $object->ResultSet->Result;
        if ($data) {
            $i = 0;
            $list = array();
            foreach($data as $suggest) {
                $list[$i]['symbol']     = (empty($suggest->symbol) ? null : $suggest->symbol);
                $list[$i]['name']       = (empty($suggest->name) ? null : $suggest->name);
                $list[$i]['exch']       = (empty($suggest->exch) ? null : $suggest->exch);
                $list[$i]['type']       = (empty($suggest->type) ? null : $suggest->type);
                $list[$i]['exchDisp']   = (empty($suggest->exchDisp) ? null : $suggest->exchDisp);
                $list[$i]['typeDisp']   = (empty($suggest->typeDisp) ? null : $suggest->typeDisp);
                $i++;
            }
            $this->result = $list;
        } else {
            //no data found
            $this->result = null;
        }
        return $this;
    }
}