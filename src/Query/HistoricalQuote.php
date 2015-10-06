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
class HistoricalQuote extends Query
{
    protected $startDate;
    protected $endDate;
    protected $historicalQuoteParams = [
        'd' => 'daily',
        'w' => 'weekly',
        'm' => 'monthly',
        'v' => 'dividends',
    ];

    function __construct($yql)
    {
        parent::__construct($yql);
    }


    /**
     * get historical quotes for provided symbol from yahoo.finance.com, direct query to csv
     *
     * @param string $symbol
     * @param string $startDate - yyyy-mm-dd
     * @param string $endDate - yyyy-mm-dd
     * @param string $param - type of data
     */
    function query($symbol, $startDate = '', $endDate = '', $param = 'd')
    {
        $this->queryString = $symbol;
        $this->queryParams = $param;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        // validate $param and set to default if not found
        if (!array_key_exists($this->queryParams, $this->historicalQuoteParams)) {
            $this->queryParams = 'd';
        }

        // validate symbol
        if (empty($symbol)) {
            $this->result = [];
            return $this;
        }

        if ($this->yql) { // request via yql console
            $data = $this->queryYQL();
        } else { // direct request via .csv
            $data = $this->queryCSV();
        }

        $this->result = $data;
        return $this;
    }

    /**
    *   query url for historical quotes via direct .csv query
    */
    private function queryCSV()
    {
        // validate dates
        $this->validateDate();

        // set startDate and endDate as option
        // query returns complete available historical data if no date is passed
        if (!empty($this->startDate)) {
            $this->startDate = explode('-', $this->startDate);
            $this->startDate[1] = $this->startDate[1]-1; // yahoo index starts with 0 for january

            $configStartDate = '&a=' . $this->startDate[1] . '&b=' . $this->startDate[2] . '&c=' . $this->startDate[0];

        } else {
            $configStartDate = '&a=&b=&c=';
        }
        if (!empty($this->endDate)) {
            $this->endDate = explode('-', $this->endDate);
            $this->endDate[1] = $this->endDate[1]-1; // yahoo index starts with 0 for january

            $configEndDate = '&d=' . $this->endDate[1] . '&e=' . $this->endDate[2] . '&f=' . $this->endDate[0];

        } else {
            $configEndDate = '&d=&e=&f=';
        }

        // add start and end date to query url if set
        $configDate = $configStartDate . $configEndDate;

        // set request url
        $this->baseUrl = 'http://ichart.finance.yahoo.com/table.csv?s=';
        $configValue = '&g=' . $this->queryParams . '&ignore=.csv';
        $this->queryUrl = $this->baseUrl . urlencode($this->queryString) . $configDate . $configValue;

        // curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        // parse csv
        $result = str_getcsv($this->response['result'], "\n"); //parse rows
        foreach ($result as &$row) { //parse items in row
            $row = str_getcsv($row);
        }
        unset($row);

        // assign headers of first row as key to values of following rows
        $dataKeys = $result[0];
        foreach ($dataKeys as $key => $value) {
            $dataKeys[$key] = str_replace(' ', '', $value); // strip white space
        }
        unset($result[0]);
        $result = array_values($result);

        // build array
        $data = array();
        foreach ($result as $key => $row) {
            foreach ($row as $rowKey => $rowValue) {
                $data[$key][$dataKeys[$rowKey]] = $rowValue;
            }
        }

        return $data;
    }

    /**
    *   prepare query url for historical quotes via YQL console
    */
    private function queryYQL()
    {
        // validate dates
        $this->validateDate();

        if (empty($this->startDate)) {
            $this->startDate = '';
        }
        if (empty($this->endDate)) {
            $this->endDate = '';
        }

        // set yql query
        $yql_query = 'select * from yahoo.finance.historicaldata where symbol = "';
        $yql_query .= $this->queryString . '" and startDate="' . $this->startDate . '" and endDate="' . $this->endDate . '"';

        // set request url
        $this->baseUrl = 'http://query.yahooapis.com/v1/public/yql?q=';
        $config = '&format=json&env=http://datatables.org/alltables.env&callback=';
        $this->queryUrl = $this->baseUrl . urlencode($yql_query) . $config;

        // curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        // read json
        $object = json_decode($this->response['result']);
        // check if some data is returned
        if (is_null($object->query->results)){
            $data = null;
        } else {
            // select object node
            $data = $object->query->results->quote;

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

        return $data;
    }

    /**
     * validate dates
     */
    protected function validateDate() {
        // validate startDate is earlier in time than endDate
        if ($this->startDate > $this->endDate and !empty($this->endDate)) {
            $temp               = $this->startDate;
            $this->startDate    = $this->endDate;
            $this->endDate      = $temp;
        }
    }

}