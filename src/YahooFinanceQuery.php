<?php
/**
 * YahooFinanceQuery - a PHP class to query the Yahoo Finance API
 *
 * @author      Dirk Olbrich <mail@dirkolbrich.de>
 * @copyright   2013-2014 Dirk Olbrich
 * @link        https://github.com/dirkolbrich/YahooFinanceQuery
 * @license     MIT
 * @version     0.4.0
 * @package     YahooFinanceQuery
 */

namespace DirkOlbrich\YahooFinanceQuery;

class YahooFinanceQuery
{
    protected $config = array(
        'returnType' => 'array', // 'array' or 'json'
        );

    protected $query;
    protected $result;
    protected $raw = false;
    protected $yql = false;
    protected $toJson = false;

    private $quoteParams = array(
        'a' => 'Ask',
        'a2' => 'AverageDailyVolume',
        'b' => 'Bid',
        'b2' => 'AskRealtime',
        'b3' => 'BidRealtime',
        'b4' => 'BookValue',
        //'b6' => 'BidSize', // missing in YQL opendatatables
        'c' => 'Change_PercentChange',
        'c1' => 'Change',
        'c3' => 'Commission',
        'c6' => 'ChangeRealtime',
        'c8' => 'AfterHoursChangeRealtime',
        'd' => 'DividendShare',
        'd1' => 'LastTradeDate',
        'd2' => 'TradeDate',
        'e' => 'EarningsShare',
        'e1' => 'ErrorIndicationreturnedforsymbolchangedinvalid',
        'e7' => 'EPSEstimateCurrentYear',
        'e8' => 'EPSEstimateNextYear',
        'e9' => 'EPSEstimateNextQuarter',
        //'f6' => 'FloatShares', // missing in YQL opendatatables
        'g' => 'DaysLow',
        'h' => 'DaysHigh',
        'j' => 'YearLow',
        'k' => 'YearHigh',
        'g1' => 'HoldingsGainPercent',
        'g3' => 'AnnualizedGain',
        'g4' => 'HoldingsGain',
        'g5' => 'HoldingsGainPercentRealtime',
        'g6' => 'HoldingsGainRealtime',
        'i' => 'MoreInfo',
        'i5' => 'OrderBookRealtime',
        'j1' => 'MarketCapitalization',
        'j3' => 'MarketCapRealtime',
        'j4' => 'EBITDA',
        'j5' => 'ChangeFromYearLow',
        'j6' => 'PercentChangeFromYearLow',
        'k1' => 'LastTradeRealtimeWithTime',
        'k2' => 'ChangePercentRealtime',
        //'k3' => 'LastTradeSize', // missing in YQL opendatatables
        'k4' => 'ChangeFromYearHigh',
        'k5' => 'PercentChangeFromYearHigh',
        'l' => 'LastTradeWithTime',
        'l1' => 'LastTradePriceOnly',
        'l2' => 'HighLimit',
        'l3' => 'LowLimit',
        'm' => 'DaysRange',
        'm2' => 'DaysRangeRealtime',
        'm3' => 'FiftydayMovingAverage',
        'm4' => 'TwoHundreddayMovingAverage',
        'm5' => 'ChangeFromTwoHundreddayMovingAverage',
        'm6' => 'PercentChangeFromTwoHundreddayMovingAverage',
        'm7' => 'ChangeFromFiftydayMovingAverage',
        'm8' => 'PercentChangeFromFiftydayMovingAverage',
        'n' => 'Name',
        'n4' => 'Notes',
        'o' => 'Open',
        'p' => 'PreviousClose',
        'p1' => 'PricePaid',
        'p2' => 'ChangeinPercent',
        'p5' => 'PriceSales',
        'p6' => 'PriceBook',
        'q' => 'ExDividendDate',
        'r' => 'PERatio',
        'r1' => 'DividendPayDate',
        'r2' => 'PERatioRealtime',
        'r5' => 'PEGRatio',
        'r6' => 'PriceEPSEstimateCurrentYear',
        'r7' => 'PriceEPSEstimateNextYear',
        's' => 'Symbol',
        's1' => 'SharesOwned',
        's7' => 'ShortRatio',
        't1' => 'LastTradeTime',
        //'t6' => 'TradeLinks', // missing in YQL opendatatables
        't7' => 'TickerTrend',
        't8' => 'OneyrTargetPrice',
        'v' => 'Volume',
        'v1' => 'HoldingsValue',
        'v7' => 'HoldingsValueRealtime',
        'w' => 'YearRange',
        'w1' => 'DaysValueChange',
        'w4' => 'DaysValueChangeRealtime',
        'x' => 'StockExchange',
        'y' => 'DividendYield',
    );

    private $historicalQuoteParams = array(
        'd' => 'daily',
        'w' => 'weekly',
        'm' => 'monthly',
        'v' => 'dividends',
    );

    private $intraDayPeriods = array(
        '1d','5d','10d','15d',
    );

    private $intraDayParams = array(
        'quote','sma','close','volume',
    );

    private $indexSymbolsDefault = array(
        'DAX' => '^GDAXI',
        'MDAX' => '^MDAXI',
        'SDAX' => '^SDAXI',
        'TecDAX'=> '^TECDAX',
        'EuroSTOXX' => '^STOXX50E',
        'FTSE100' => '^FTSE',
        'DJI' => '^DJI',
        'NASDAQ100' => '^NDX',
    );

    /**
    *   constructor with optional config param
    */
    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->config($config);
        }
    }

    /**
    *   creator with optional config param
    */
    public static function make(array $config = array())
    {
        $query = new YahooFinanceQuery($config);
        return $query;
    }

    /**
    *   configurator
    */
    public function config(array $config = array())
    {
        foreach ($config as $key => $setting) {
            if (array_key_exists($key, $this->config)) {
                $this->config[$key] = $setting;
            }
        }
        return $this;
    }

    /**
    *   get configurator setting
    */
    public function getConfig()
    {
        return $this->config;
    }

    /*
    *
    */
    public function raw()
    {
        $this->raw = true;
        return $this;
    }

    /*
    *
    */
    public function yql()
    {
        $this->yql = true;
        return $this;
    }

    /*
    *
    */
    public function get()
    {
        $data = $this->result;
        // check returnType
        if ($this->config['returnType'] == 'json' or ($this->toJson)) {
            $this->toJson = false;
            $data = json_encode($data);
        }
        return $data;
    }

    /*
    *
    */
    public function toJson()
    {
        $this->toJson = true;
        return $this;
    }

    /**
    *   get list of stocks with symbol and market for provided search string from yahoo.finance.com's stock symbol autosuggest callback
    *   @param string $string - name to search for
    */
    public function symbolSuggest($string)
    {
        $this->query = $string;
        //set url for callback
        $query_url = 'http://d.yimg.com/autoc.finance.yahoo.com/autoc?query=' . urlencode($string) . '&callback=YAHOO.Finance.SymbolSuggest.ssCallback';
        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false;
            return $response;
        }

        //read json
        $json = preg_replace('/.+?({.+}).+/', '$1', $response['result']);

        //convert json to array
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

    /**
    *   get stock quotes for provided symbols from yahoo.finance.com
    *   @param array $symbol - array with symbol/s
    *   @param array $params - array with query params
    */
    public function quote(array $symbol, array $params = null)
    {
        $this->query['symbols'] = $symbol;
        $this->query['params'] = $params;
        $defaultParams = array(
            's'  => 'Symbol',
            't1' => 'LastTradeTime',
            'd1' => 'LastTradeDate',
        );
        //check if array contains duplicates, remove and rearrange numbers
        $symbols = array_values(array_unique($symbol));

        if ($this->yql) { // request via yql console
            $this->yql = false; // reset $yql
            $data = $this->quoteYQL($symbols, $params, $defaultParams);
        } else { // direct request via .csv
            $data = $this->quoteCSV($symbols, $params, $defaultParams);
        }

        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $data;
        }

        // add unix timestamp and UTC time
        foreach ($data as &$dataSet) {
            $time = $dataSet['LastTradeTime'];
            $date = $dataSet['LastTradeDate'];
            $timeString = $time.' '.$date;

            $yahooTimezone = new \DateTimeZone('America/New_York');
            $yahooDateTime = new \DateTime($timeString, $yahooTimezone);

            $yahooDateTime->setTimezone(new \DateTimeZone('UTC'));
            //$this->v($yahooDateTime->format('d.m.Y H:i:s'));

            $dataSet['LastTradeTimestamp'] = $yahooDateTime->format('U');
            $dataSet['LastTradeUTCTime'] = $yahooDateTime->format('c');
        }

        $this->result = $data;
        return $this;
    }

    /**
    *   get historical quotes for provided symbol from yahoo.finance.com, direct query to csv
    *   @param string $symbol
    *   @param string $startDate yyyy-mm-dd
    *   @param string $endDate yyyy-mm-dd
    *   @param string $param - type of data
    */
    function historicalQuote($symbol, $startDate = '', $endDate = '', $param = 'd')
    {
        $this->query['symbol'] = array(
            'symbol'    => $symbol,
            'param'     => $param,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            );

        // validate $param and set to default
        if (!array_key_exists($param, $this->historicalQuoteParams)) {
            $param = 'd';
        }

        if ($this->yql) { // request via yql console
            $this->yql = false; // reset $yql
            $data = $this->historicalQuoteYQL($symbol, $startDate, $endDate, $param);
        } else { // direct request via .csv
            $data = $this->historicalQuoteCSV($symbol, $startDate, $endDate, $param);
        }

        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $data;
        }

        $this->result = $data;
        return $this;
    }

    /**
    *   get intraday quotes for provided symbol from yahoo.finance.com, direct query to csv
    *   @param string $symbol
    *   @param string $param - type of data
    *   @return array $quoteList - array with quotes
    */
    function intraDay($symbol, $period = '1d', $param = 'quote')
    {
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

        //set request url
        $base_url = 'http://chartapi.finance.yahoo.com/instrument/1.0/';
        $query_url = $base_url . $symbol. '/chartdata;type=' . $param . ';range=' . $period . '/json/';

        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $response;
        }
        // trim response
        $result = ltrim(rtrim($response['result'], ' )'), 'finance_charts_json_callback( ');
        //read json
        $object = json_decode($result);

        //check if some data is returned
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

    /**
    *   get list of info for provided symbol from yahoo.finance.com
    *   @param string $symbol - stock symbol
    */
    function stockInfo($symbol)
    {
        $this->query = $symbol;

        if ($this->yql) { // request via yql console
            $this->yql = false; // reset $yql
            $data = $this->stockInfoYQL($symbol);
        } else { // direct request
            $data = $this->stockInfoDirect($symbol);
        }

        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $data;
        }

        //return data
        $this->result = $data;
        return $this;
    }

    /**
    *   get list of component for indices symbol from yahoo.finance.com
    *   @param mixed $symbol
    */
    function indexList(array $symbol)
    {
        // default indices
        if (empty($symbol[0])) {
           $symbol = $this->indexSymbolsDefault;
        }
        $symbolList = array();
        foreach ($symbol as $value) {
            $symbolList[] = urlencode('@' . $value);
        }
        $symbolString = implode('+', $symbolList);
        //set request url
        $base_url = 'http://download.finance.yahoo.com/d/quotes.csv?s=';
        $config = '&f=snx&e=.csv';
        $query_url = $base_url . $symbolString . $config;
        //curl request
        $response = $this->curlRequest($query_url);
        //parse csv
        $result = str_getcsv($response['result'], "\n"); //parse rows
        foreach ($result as &$row) { //parse items in row
            $row = str_getcsv($row);
        }
        unset($row);
        //split up data if multiple symbols are requested
        $data = array();
        $resultHolder = array();
        reset($symbol);
        foreach ($result as $resultKey => $resultVal) {
            //check if array has empty element = delimiter
            if ((count($resultVal) == 1) and ($resultVal[0] === null)) {
                $data[current($symbol)] = $resultHolder; // dump $resultHolder to data array
                $resultHolder = array(); // reset $resultHolder
                next($symbol);
            } else {
                $resultHolder[] = $resultVal;
            }
        }
        $this->result = $data;
        return $this;
    }

    /**
    *   get full list of sectors with corresponding industries from yahoo.finance.com
    *   @return array $sectorsList - array with sectors
    */
    function sectorList()
    {
        //set yql query
        $yql_query = 'select * from yahoo.finance.sectors';
        //set request url
        $base_url = 'http://query.yahooapis.com/v1/public/yql?q=';
        $config = '&format=json&env=store://datatables.org/alltableswithkeys&callback=';
        $query_url = $base_url . rawurlencode($yql_query) . $config;
        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $response;
        }
        //read json
        $object = json_decode($response['result'], true);
        //check if some data is returned
        if (is_null($object['query']['results'])) {
            return null;
        }
        //select object node
        $data = $object['query']['results']['sector'];
        //check array
        if (empty($data)) {
            $this->result = null;
        } else {
            //sanitize data for sector with single industry
            foreach ($data as $key => $sector) {
                if (!is_array($sector['industry'][0])) {
                    $data[$key]['industry'] = array($sector['industry']);
                }
            }
            //return data
            $this->result = $data;
        }

        return $this;
    }

    /****** CSV Querys ******/

    /**
    *   query url for quotes via direct .csv query
    */
    private function quoteCSV($symbols, $params, $defaultParams)
    {
        //implode symbols to string
        $symbolString = implode('+', $symbols);

        //set list of params
        $params = array_filter($params);
        if (empty($params)) {
            $paramList = $this->quoteParams;
        } else {
            $paramList = array();
            //retrieve params from quoteParam list
            foreach ($params as $param) {
                if (in_array($param, $this->quoteParams)) {
                    $paramKey = array_search($param, $this->quoteParams);
                    $paramList[$paramKey] = $this->quoteParams[$paramKey];

                } elseif (array_key_exists($param, $this->quoteParams)) {
                    $paramList[$param] = $this->quoteParams[$param];
                }
            }
        }
        //merge with default params
        $paramList = array_merge(array_keys($paramList), array_keys($defaultParams));
        $paramString = implode('', $paramList);

        //set request url
        $base_url = 'http://finance.yahoo.com/d/quotes.csv?s=';
        $query_url = $base_url . $symbolString . '&f=' . $paramString . '&e=.csv';

        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $response;
        }

        //parse csv
        $result = str_getcsv($response['result'], "\n"); //parse rows
        foreach ($result as &$row) { //parse items in row
            $row = str_getcsv($row);
        }
        unset($row);
        //assign params as key to values
        $dataKeys = array();
        foreach ($paramList as $key => $value) {
            $dataKeys[$key] = $this->quoteParams[$value];
        }
        // rebuild data array with new data keys
        $data = array();
        foreach ($result as $key => $row) {
            foreach ($row as $rowKey => $rowValue) {
                $data[$key][$dataKeys[$rowKey]] = $rowValue;
            }
        }
        return $data;
    }

    /**
    *   query url for historical quotes via direct .csv query
    */
    private function historicalQuoteCSV($symbol, $startDate = '', $endDate = '', $param)
    {
        //check dates to prevent false query
        if ($startDate > $endDate and !empty($endDate)) {
            $temp       = $startDate;
            $startDate  = $endDate;
            $endDate    = $temp;
        }
        //set startDate and endDate as option
        //query returns complete available historical data if no date is passed
        if (!empty($startDate)) {
            $startDate = explode('-', $startDate);
            $startDate[1] = $startDate[1]-1; //yahoo index starts with 0 for january

            $configStartDate = '&a=' . $startDate[1] . '&b=' . $startDate[2] . '&c=' . $startDate[0];

        } else {
            $configStartDate = '&a=' . '' . '&b=' . '' . '&c=' . '';
        }
        if (!empty($endDate)) {
            $endDate = explode('-', $endDate);
            $endDate[1] = $endDate[1]-1; //yahoo index starts with 0 for january

            $configEndDate = '&d=' . $endDate[1] . '&e=' . $endDate[2] . '&f=' . $endDate[0];

        } else {
            $configEndDate = '&d=' . '' . '&e=' . '' . '&f=' . '';
        }

        //add start and end date to query url if set
        $configDate = $configStartDate . $configEndDate;

        //set request url
        $base_url = 'http://ichart.finance.yahoo.com/table.csv?s=';
        $configValue = '&g=' . $param . '&ignore=.csv';
        $query_url = $base_url . urlencode($symbol) . $configDate . $configValue;

        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $response;
        }

        //parse csv
        $result = str_getcsv($response['result'], "\n"); //parse rows
        foreach ($result as &$row) { //parse items in row
            $row = str_getcsv($row);
        }
        unset($row);
        //assign headers of first row as key to values of following rows
        $dataKeys = $result[0];
        foreach ($dataKeys as $key => $value) {
            $dataKeys[$key] = str_replace(' ', '', $value);//strip white space
        }
        unset($result[0]);
        $result = array_values($result);
        //build array
        $data = array();
        foreach ($result as $key => $row) {
            foreach ($row as $rowKey => $rowValue) {
                $data[$key][$dataKeys[$rowKey]] = $rowValue;
            }
        }
        return $data;
    }

    /**
    *   query url for stock info via direct query
    */
    private function stockInfoDirect($symbol)
    {
        //set request url
        $base_url = 'http://finance.yahoo.com/q/pr?s=';
        $query_url = $base_url . urlencode($symbol);

        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $response;
        }

        //parse html
        $dom = new \DOMDocument();
        @$dom->loadHTML($response['result']);
        $dom->preserveWhiteSpace = false;
        $body = new \DOMXPath($dom);
        $data = array();
        $i = 0;
        //query DOM for key
        foreach ($body->query('//td[@class="yfnc_modtitlew1"]//table[@class="yfnc_datamodoutline1"]//td[@class="yfnc_tablehead1"]') as $node) {
            $data[$i]['key'] = str_replace(' ', '', rtrim($node->nodeValue, ':'));
            $i++;
        }
        $i = 0;
        foreach ($body->query('//td[@class="yfnc_modtitlew1"]//table[@class="yfnc_datamodoutline1"]//td[@class="yfnc_tabledata1"]') as $node) {
            $data[$i]['value'] = $node->nodeValue;
            $i++;
        }
        $list = array();
        foreach ($data as $dataEntry) {
            $list[$dataEntry['key']] = $dataEntry['value'];
        }

        return $list;
    }


    /****** YQL Querys ******/

    /**
    *   prepare query url for quotes via YQL console
    */
    private function quoteYQL($symbols, $params, $defaultParams)
    {
        //prepare list of symbols for string
        foreach ($symbols as $symbolKey => $symbol) {
            // add marks to each symbol
            $symbols[$symbolKey] = '"'.$symbol.'"';
        }
        //implode to string
        $symbolString = implode(', ', $symbols);

        //set list of params
        $params = array_filter($params);
        if (empty($params)) {
            $paramList = $this->quoteParams;
        } else {
            $paramList = array();
            //retrieve params from quoteParam list
            foreach ($params as $param) {
                if (in_array($param, $this->quoteParams)) {
                    $paramKey = array_search($param, $this->quoteParams);
                    $paramList[$paramKey] = $this->quoteParams[$paramKey];

                } elseif (array_key_exists($param, $this->quoteParams)) {
                    $paramList[$param] = $this->quoteParams[$param];
                }
            }
        }
        //merge with default params
        $paramList = array_merge($paramList, $defaultParams);
        $paramString = implode(', ', $paramList);

        //set yql query
        $yql_query = 'select ' . $paramString . ' from yahoo.finance.quotes where symbol in (' .$symbolString . ')';

        //set request url
        $base_url = 'http://query.yahooapis.com/v1/public/yql?q=';
        $config = '&format=json&env=http://datatables.org/alltables.env&callback=';
        $query_url = $base_url . urlencode($yql_query) . $config;

        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $response;
        }

        //read json
        $object = json_decode($response['result']);
        //check if some data is returned
        if (is_null($object->query->results)){
            $data = null;
        } else {
            //select object node
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
    *   prepare query url for historical quotes via YQL console
    */
    private function historicalQuoteYQL($symbol, $startDate, $endDate, $param)
    {
        //check dates to prevent false query
        if ($startDate > $endDate and !empty($endDate)) {
            $temp       = $startDate;
            $startDate  = $endDate;
            $endDate    = $temp;
        }

        if (empty($startDate)) {
            $startDate = '';
        }
        if (empty($endDate)) {
            $endDate = '';
        }

        // set yql query
        $yql_query = 'select * from yahoo.finance.historicaldata where symbol = "'.$symbol.'" and startDate="'.$startDate.'" and endDate="'.$endDate.'"';

        // set request url
        $base_url = 'http://query.yahooapis.com/v1/public/yql?q=';
        $config = '&format=json&env=http://datatables.org/alltables.env&callback=';
        $query_url = $base_url . urlencode($yql_query) . $config;

        //curl request
        $response = $this->curlRequest($query_url);
        if ($this->raw) {
            $this->raw = false; // reset $raw
            return $response;
        }

        //read json
        $object = json_decode($response['result']);
        //check if some data is returned
        if (is_null($object->query->results)){
            $data = null;
        } else {
            //select object node
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
    *   cURL request method
    *
    *   @param string $url
    *   @return array $response
    */
    private function curlRequest($url)
    {
        $response = array(
            'query' => $url,
            );
            
        $userAgent = @($this->config['userAgent'] ?: $_SERVER["HTTP_USER_AGENT"] ?: null);
            
        //curl request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        $response['result'] = curl_exec($ch);
        $response['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response['error'] = curl_error($ch);
        $response['errno'] = curl_errno($ch);
        curl_close($ch);
        //$this->v($response);
        return $response;
    }

    //dev-tool
    /**
    *   function for better var_dump() and print_r()
    *   @param mixed $mixed
    */
    public function v($mixed)
    {
        $numArgs = func_num_args($mixed);
        $args = func_get_args($mixed);
        echo '<pre><code>';
        foreach ($args as $var) {
            ob_start();
            var_dump($var);
            $content = ob_get_clean();
            echo htmlentities($content);
        }
        echo '</code></pre>';
    }

}
