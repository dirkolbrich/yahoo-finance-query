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
class IntraDayQuote extends Query
{
    protected $intraDayPeriods = [
        '1d',
        '5d',
        '10d',
        '15d',
    ];
    protected $intraDayParams = [
        'quote',
        'sma',
        'close',
        'volume',
    ];
    
    function __construct($yql)
    {
        parent::__construct($yql);
    }

    /**
     * get intraday quotes for provided symbol from yahoo.finance.com, direct query to csv
     *
     * @param string $symbol
     * @param string $param - type of data
     * @return array $quoteList - array with quotes
     * @return self
     */
    function query($symbol, $period = '1d', $param = 'quote')
    {
        $this->queryString = $symbol;
        $this->queryParams = [
            'period' => $period,
            'param' => $param,
        ];
        // validate period
        if (!in_array($period, $this->intraDayPeriods)) {
            $period = '1d';
        }
        // validate params
        if (!in_array($param, $this->intraDayParams)) {
            $param = 'quote';
        }

        // examples:
        // http://chartapi.finance.yahoo.com/instrument/1.0/bas.de/chartdata;type=quote;range=1d/csv/
        // http://chartapi.finance.yahoo.com/instrument/1.0/aapl/chartdata;type=close;range=5d/json/

        // set request url
        $this->baseUrl = 'http://chartapi.finance.yahoo.com/instrument/1.0/';
        $this->queryUrl = $this->baseUrl . $this->queryString . '/chartdata;type=' . $param . ';range=' . $period . '/json/';

        // curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        // trim response
        $result = ltrim(rtrim($this->response['result'], ' )'), 'finance_charts_json_callback( ');
        //read json
        $object = json_decode($result);

        // check if some data is returned
        if (is_null($object->series)){
            $data = null;
        } else {
            //select object node
            $data = $object->series;
            // put single object into array to unify $data
            if (is_object($data)) {
                $data = array($data);
            }
            // cast single datasets to array
            foreach ($data as &$dataSet) {
                if (is_object($dataSet)) {
                    $dataSet = (array)$dataSet;
                }
            }
            unset($dataSet);
        }

        $this->result = $data;
        return $this;
    }

}