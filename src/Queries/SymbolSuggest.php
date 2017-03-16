<?php namespace YahooFinanceQuery\Queries;

/**
 * YahooFinanceQuery - a PHP package to query the Yahoo Finance API
 *
 * @author      Dirk Olbrich <mail@dirkolbrich.de>
 * @copyright   2013-2017 Dirk Olbrich
 * @link        https://github.com/dirkolbrich/YahooFinanceQuery
 * @license     MIT
 * @version     1.0.0
 * @package     YahooFinanceQuery
 */

use YahooFinanceQuery\Queries\Query;

/**
*
*/
class SymbolSuggest extends Query
{
    /**
     * constructor with $yql param
     * @param bool $yql - setting if YQL is used
     */
    public function __construct(bool $yql)
    {
        parent::__construct($yql);
    }

    /**
     * get a list of stocks with symbol and market for provided search string
     * from yahoo.finance.com's stock symbol autosuggest callback
     * @param string $string - the name/string to search for
     * @return self
     */
    public function query(string $queryString)
    {
        $this->queryString = $queryString;

        // set url for callback
        $this->baseUrl = 'http://d.yimg.com/aq/autoc?query=';
        $region = 'region=US';
        $lang = 'lang=en-US';
        $this->queryUrl = $this->baseUrl . urlencode($this->queryString) . '&' . $region . '&' . $lang . '&callback=YAHOO.util.ScriptNodeDataSource.callbacks';

        // curl request
        $this->curlRequest($this->queryUrl);

        // handle response
        if ($this->response['status'] == 200) {
            $this->handleResponse();
            return $this;
        }

        $this->handleError();
        return $this;
    }

    /**
     * handle the response if the query was successful
     * @return self
     */
    protected function handleResponse()
    {
        // define return array
        $this->result = [
                'ok' => true,
                'meta' => [
                    'status' => $this->response['status'],
                    'query' => $this->queryString,
                ],
                'data' => []
        ];

        // read json from response
        $json = preg_replace('/.+?({.+}).+/', '$1', $this->response['result']);

        // convert json to array
        $object = json_decode($json);
        $data = $object->ResultSet->Result;
        if ($data) {

            $i = 0;
            $list = [];

            //structure of a single data type array
            $item = [
                'type' => 'symbols',
                'id'   => null,
                'attributes' => [
                    'symbol' => null,
                    'name' => null,
                    'exch' => null,
                    'exchDisplay' => null,
                    'type' => null,
                    'typeDisplay' => null,
                ]
            ];

            foreach($data as $suggest) {
                $symbol = $item;
                $symbol['id'] = $i;

                $symbol['attributes']['symbol']     = (empty($suggest->symbol) ? null : $suggest->symbol);
                $symbol['attributes']['name']       = (empty($suggest->name) ? null : $suggest->name);
                $symbol['attributes']['exch']       = (empty($suggest->exch) ? null : $suggest->exch);
                $symbol['attributes']['type']       = (empty($suggest->type) ? null : $suggest->type);
                $symbol['attributes']['exchDisplay']   = (empty($suggest->exchDisp) ? null : $suggest->exchDisp);
                $symbol['attributes']['typeDisplay']   = (empty($suggest->typeDisp) ? null : $suggest->typeDisp);

                $list[] = $symbol;
                $i++;
            }
            // fill data variable of return array
            $this->result['data'] = $list;
        }
        return $this;
    }

    /**
     * handle the response if the query had errors
     * @return self
     */
    protected function handleError()
    {
        // define return array
        $this->result = [
                'ok' => false,
                'meta' => [
                    'status' => $this->response['status'],
                    'query' => $this->queryString,
                ],
                'errors' => [
                    'status' => $this->response['status'],
                    'title' => $this->response['error'],
                    'detail' => $this->response['errno'],
                ],
        ];

        return $this;
    }
}