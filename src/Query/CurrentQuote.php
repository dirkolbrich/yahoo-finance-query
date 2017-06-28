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

/**
 * Class CurrentQuote
 *
 * @package DirkOlbrich\YahooFinanceQuery\Query
 */
class CurrentQuote extends Query
{
    protected $defaultParams = [
        's'  => 'Symbol',
        't1' => 'LastTradeTime',
        'd1' => 'LastTradeDate',
    ];

    protected $quoteParams = [
        'a'  => 'Ask',
        'a2' => 'AverageDailyVolume',
        'b'  => 'Bid',
        'b2' => 'AskRealtime',
        'b3' => 'BidRealtime',
        'b4' => 'BookValue',
        'b6' => 'BidSize', // missing in YQL opendatatables
        'c'  => 'Change_PercentChange',
        'c1' => 'Change',
        'c3' => 'Commission',
        'c4' => 'Currency',
        'c6' => 'ChangeRealtime',
        'c8' => 'AfterHoursChangeRealtime',
        'd'  => 'DividendShare',
        'd1' => 'LastTradeDate',
        'd2' => 'TradeDate',
        'e'  => 'EarningsShare',
        'e1' => 'ErrorIndicationreturnedforsymbolchangedinvalid',
        'e7' => 'EPSEstimateCurrentYear',
        'e8' => 'EPSEstimateNextYear',
        'e9' => 'EPSEstimateNextQuarter',
        'f6' => 'FloatShares', // missing in YQL opendatatables
        'g'  => 'DaysLow',
        'h'  => 'DaysHigh',
        'j'  => 'YearLow',
        'k'  => 'YearHigh',
        'g1' => 'HoldingsGainPercent',
        'g3' => 'AnnualizedGain',
        'g4' => 'HoldingsGain',
        'g5' => 'HoldingsGainPercentRealtime',
        'g6' => 'HoldingsGainRealtime',
        'i'  => 'MoreInfo',
        'i5' => 'OrderBookRealtime',
        'j1' => 'MarketCapitalization',
        'j3' => 'MarketCapRealtime',
        'j4' => 'EBITDA',
        'j5' => 'ChangeFromYearLow',
        'j6' => 'PercentChangeFromYearLow',
        'k1' => 'LastTradeRealtimeWithTime',
        'k2' => 'ChangePercentRealtime',
        'k3' => 'LastTradeSize', // missing in YQL opendatatables
        'k4' => 'ChangeFromYearHigh',
        'k5' => 'PercentChangeFromYearHigh',
        'l'  => 'LastTradeWithTime',
        'l1' => 'LastTradePriceOnly',
        'l2' => 'HighLimit',
        'l3' => 'LowLimit',
        'm'  => 'DaysRange',
        'm2' => 'DaysRangeRealtime',
        'm3' => 'FiftydayMovingAverage',
        'm4' => 'TwoHundreddayMovingAverage',
        'm5' => 'ChangeFromTwoHundreddayMovingAverage',
        'm6' => 'PercentChangeFromTwoHundreddayMovingAverage',
        'm7' => 'ChangeFromFiftydayMovingAverage',
        'm8' => 'PercentChangeFromFiftydayMovingAverage',
        'n'  => 'Name',
        'n4' => 'Notes',
        'o'  => 'Open',
        'p'  => 'PreviousClose',
        'p1' => 'PricePaid',
        'p2' => 'ChangeinPercent',
        'p5' => 'PriceSales',
        'p6' => 'PriceBook',
        'q'  => 'ExDividendDate',
        'r'  => 'PERatio',
        'r1' => 'DividendPayDate',
        'r2' => 'PERatioRealtime',
        'r5' => 'PEGRatio',
        'r6' => 'PriceEPSEstimateCurrentYear',
        'r7' => 'PriceEPSEstimateNextYear',
        's'  => 'Symbol',
        's1' => 'SharesOwned',
        's7' => 'ShortRatio',
        't1' => 'LastTradeTime',
        't6' => 'TradeLinks', // missing in YQL opendatatables
        't7' => 'TickerTrend',
        't8' => 'OneyrTargetPrice',
        'v'  => 'Volume',
        'v1' => 'HoldingsValue',
        'v7' => 'HoldingsValueRealtime',
        'w'  => 'YearRange',
        'w1' => 'DaysValueChange',
        'w4' => 'DaysValueChangeRealtime',
        'x'  => 'StockExchange',
        'y'  => 'DividendYield',
    ];

    /**
     * get stock quotes for provided symbols from yahoo.finance.com
     *
     * @param array $symbol - array with symbol/s
     * @param array $params - array with query params
     *
     * @return self
     */
    public function query(array $symbol, array $params = null)
    {
        $this->queryString = $symbol;
        $this->queryParams = $params;
        //check if array contains duplicates, remove and rearrange numbers
        $symbols = array_values(array_unique($this->queryString));

        // validate symbols
        if (empty($symbols[0])) {
            $this->result = [];

            return $this;
        }

        if ($this->yql) { // request via yql console
            $data = $this->queryYQL($symbols);
        } else { // direct request via .csv
            $data = $this->queryCSV($symbols);
        }

        // add unix timestamp and UTC time
        foreach ($data as &$dataSet) {
            $timeString = null;
            if (isset($dataSet['LastTradeTime']) && $dataSet['LastTradeTime'] != "N/A" && $dataSet['LastTradeDate'] != "N/A") {
                $time       = $dataSet['LastTradeTime'];
                $date       = $dataSet['LastTradeDate'];
                $timeString = $time . ' ' . $date;
            }

            $yahooTimezone = new \DateTimeZone('America/New_York');
            $yahooDateTime = new \DateTime($timeString, $yahooTimezone);
            $yahooDateTime->setTimezone(new \DateTimeZone('UTC'));

            $dataSet['LastTradeTimestamp'] = $yahooDateTime->format('U');
            $dataSet['LastTradeUTCTime']   = $yahooDateTime->format('c');
        }

        $this->result = $data;

        return $this;
    }


    /****** CSV Querys ******/

    /**
     * set the param list for the query
     *
     * @return array $paramList;
     */
    protected function setParamList()
    {
        // set list of params
        if (empty($this->queryParams)) {
            $paramList = $this->quoteParams;
        } else {
            $queryParams = array_filter($this->queryParams);
            $paramList   = [];
            //retrieve params from quoteParam list
            foreach ($queryParams as $param) {
                if (in_array($param, $this->quoteParams)) {
                    $paramKey             = array_search(
                        $param,
                        $this->quoteParams
                    );
                    $paramList[$paramKey] = $this->quoteParams[$paramKey];
                } elseif (array_key_exists($param, $this->quoteParams)) {
                    $paramList[$param] = $this->quoteParams[$param];
                }
            }
        }

        // merge with default params
        $paramList = array_merge($paramList, $this->defaultParams);

        return $paramList;
    }

    /****** YQL Querys ******/

    /**
     * query url for quotes via direct .csv query
     *
     * @param array $symbols
     *
     * @return void
     */
    private function queryCSV($symbols)
    {
        //implode symbols to string
        $symbolString = implode('+', $symbols);

        $paramList   = $this->setParamList();
        $paramString = implode(
            '',
            array_keys($paramList)
        ); // use only the param keys for csv query

        // set request url
        $this->baseUrl  = 'http://finance.yahoo.com/d/quotes.csv?s=';
        $this->queryUrl = $this->baseUrl . $symbolString . '&f=' . $paramString . '&e=.csv';

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

        // reassign paramList as indexed array
        $dataKeys = [];
        foreach ($paramList as $key => $value) {
            $dataKeys[] = $this->quoteParams[$key];
        }

        // rebuild data array with new data keys
        $data = [];
        foreach ($result as $key => $entry) {
            foreach ($entry as $entryKey => $entryValue) {
                $data[$key][$dataKeys[$entryKey]] = $entryValue;
            }
        }

        return $data;
    }

    /**
     * prepare query url for quotes via YQL console
     */
    private function queryYQL($symbols)
    {
        // prepare list of symbols for string
        foreach ($symbols as $symbolKey => $symbol) {
            // add marks to each symbol
            $symbols[$symbolKey] = '"' . $symbol . '"';
        }
        // implode symbols to string
        $symbolString = implode(', ', $symbols);

        $paramList   = $this->setParamList();
        $paramString = implode(', ', $paramList);

        // set yql query
        $query_string = 'select ' . $paramString . ' from yahoo.finance.quotes where symbol in (' . $symbolString . ')';

        // set request url
        $this->baseUrl  = 'http://query.yahooapis.com/v1/public/yql?q=';
        $config         = '&format=json&env=http://datatables.org/alltables.env&callback=';
        $this->queryUrl = $this->baseUrl . urlencode($query_string) . $config;

        // curl request
        $this->curlRequest($this->queryUrl);

        if (404 == $this->response['status']) {
            return $data = [];
        }

        // read json
        $object = json_decode($this->response['result']);
        // check if some data is returned
        if (is_null($object->query->results)) {
            $data = null;
        } else {
            // select object node
            $data = $object->query->results->quote;

            // put single object into array to unify $data
            if (is_object($data)) {
                $data = [$data];
            }
            // cast single datasets to array
            foreach ($data as &$dataSet) {
                if (is_object($dataSet)) {
                    $dataSet = (array) $dataSet;
                }
            }
            unset($dataSet);
        }

        return $data;
    }
}
