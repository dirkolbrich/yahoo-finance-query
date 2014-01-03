<?php
/**
 * YahooFinanceQuery - a PHP class to query the Yahoo Finance API
 *
 * @author      Dirk Olbrich <mail@dirkolbrich.de>
 * @copyright   2013 Dirk Olbrich
 * @link        https://github.com/dirkolbrich/YahooFinanceQuery
 * @license     MIT
 * @version     0.2.0
 * @package     YahooFinanceQuery
 */
 
namespace YahooFinanceQuery;
 
class YahooFinanceQuery
{
    private $config = array(
        'returnType' => 'array', //'array' or 'json'
    );
     
    public $quoteParams = array(
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
        'y' => 'DividendYield'  
    );
 
    /**
    *   constructor with optional config param
    *   @param array $config
    */
    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->config = $this->config($config);
        }
    }
     
    /**
    *   set $config with user setting
    *   @param 
    */
    public function config(array $config)
    {
        foreach ($config as $settingKey => $setting) {
            if (array_key_exists($settingKey, $this->config)) {
                $this->config[$settingKey] = $setting;
            }
        }
    }
     
    /**
    *   get current $config setting
    */
    public function getConfig()
    {
        return $this->config;  
    }
 
    /**
    *   get list of stocks with symbol and market for provided search string from yahoo.finance.com's stock symbol autosuggest callback 
    *   @param string $string - name to search for
    *   @return array $suggestList - array with stock symbols
    */
    public function symbolSuggest($string)
    {
        //set url for callback
        $query_url = 'http://d.yimg.com/autoc.finance.yahoo.com/autoc?query=' . urlencode($string) . '&callback=YAHOO.Finance.SymbolSuggest.ssCallback';     
        //curl request
        $ch = curl_init($query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);    
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //read json
        $json = preg_replace('/.+?({.+}).+/', '$1', $result);
        if ($this->config['returnType'] == 'json') {
            return $json;
        }
        //convert json to array
        $object = json_decode($json);
        $data = $object->ResultSet->Result;
        if ($data) {
            $i = 0;
            foreach($data as $suggest) {
                $suggestList[$i]['symbol']     = (empty($suggest->symbol) ? null : $suggest->symbol);
                $suggestList[$i]['name']       = (empty($suggest->name) ? null : $suggest->name);
                $suggestList[$i]['exch']       = (empty($suggest->exch) ? null : $suggest->exch);
                $suggestList[$i]['type']       = (empty($suggest->type) ? null : $suggest->type);
                $suggestList[$i]['exchDisp']   = (empty($suggest->exchDisp) ? null : $suggest->exchDisp);
                $suggestList[$i]['typeDisp']   = (empty($suggest->typeDisp) ? null : $suggest->typeDisp);
                $i++;
            }
        } else {
            //no data found
            return null;
        }
        return $suggestList;
    }
 
    /**
    *   get stock quotes for provided symbols from yahoo.finance.com
    *   @param array $symbol - array with symbol/s
    *   @param array $params - array with query params
    *   @return string $paramType - what type the params are
    */
    public function quote(array $symbol, $params = array())
    {
        $defaultParams = array(
            's'  => 'Symbol',
            't1' => 'LastTradeTime',
            'd1' => 'LastTradeDate'
        );
     
        //check if array contains duplicates, remove and rearrange numbers
        $symbols = array_values(array_unique($symbol));
        //prepare list of symbols for string
        foreach ($symbols as $symbolKey => $symbol) {
            // add marks to each symbol
            $symbols[$symbolKey] = '"'.$symbol.'"';
        }
        //implode to string
        $symbolsString = implode(', ', $symbols);
         
        //set list of params
        $params = array_filter($params);
        if (empty($params)) {
            $paramList = $this->quoteParams;
        } else {
            $paramList = array();
            //retrieve pamrams from quoteParam list
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
        $yql_query = 'select ' . $paramString . ' from yahoo.finance.quotes where symbol in (' .$symbolsString . ')';
        //set request url
        $base_url = 'http://query.yahooapis.com/v1/public/yql';
        $config = '&format=json&env=http://datatables.org/alltables.env&callback=';
        $query_url = $base_url . '?q=' . urlencode($yql_query) . $config;
        //curl request
        $ch = curl_init($query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);    
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($this->config['returnType'] == 'json') {
            return $result;
        }
        //read json
        $object = json_decode($result);
        //check if some data is returned
        if (is_null($object->query->results)){
            return null;
        }
        //select object node
        $data = $object->query->results->quote;
        //add UTC timestamp to dataset
        foreach ($data as $dataEntryKey => $dataEntry) {
            //normalize time zones for date and time to UTC
            // as it appears that these values are set with different timezones depending 
            // on the symbol/market and the server the request goes to
            if (isset($dataEntry->LastTradeTime) and isset($dataEntry->LastTradeDate)) {
                $time = $dataEntry->LastTradeTime; // is time of server, EST for finance.yahoo.com
                $timeZone = new \DateTimeZone('America/New_York'); //set timezone
                $time = date_create_from_format('g:ia', $time, $timeZone);
                $time->setTimeZone(new \DateTimeZone('UTC')); // change timezone
                $time = date_format($time, 'H:i:s'); // extract time for that timezone
             
                $date = $dataEntry->LastTradeDate; //in home exchanges timezone, GMT for Xetra
                $timeZone = new \DateTimeZone('America/New_York'); //set timezone
                $date = date_create_from_format('n/j/Y', $date, $timeZone);
                $date->setTimeZone(new \DateTimeZone('UTC')); // change timezone
                $date = date_format($date, 'd.m.Y'); //extract date for that timezone
                $dateTime = sprintf('%s %s',$date, $time); //merge date and time as string, separator whitespace
                $timeZone = new \DateTimeZone('UTC'); //set timezone
                $timeStamp = date_create_from_format('d.m.Y H:i:s', $dateTime, $timeZone); //generate timestamp
                $dataEntry->LastTradeDateTime = $timeStamp;
                //remove old date and time
                unset($dataEntry->LastTradeTime);
                unset($dataEntry->LastTradeDate);
            }
        }
        //return data
        return $data;
    }
 
    /**
    *   get historical quotes for provided symbol from yahoo.finance.com, use YQL and open datatables
    *   @param string $symbol
    *   @param string $startDate yyyy-mm-dd
    *   @param string $endDate yyyy-mm-dd
    *   @return array $quoteList - array with quotes
    */
    public function historicalQuote($symbol, $startDate, $endDate)
    {
        $this->vdump($symbol, $startDate, $endDate);
        //set yql query
        $yql_query = 'select * from yahoo.finance.historicaldata where symbol = "' . $symbol . '" and startDate="' . $startDate . '" and endDate="' . $endDate . '"';
          
        //set request url 
        $base_url = 'http://query.yahooapis.com/v1/public/yql';
        $config = '&format=json&env=http://datatables.org/alltables.env&callback=';
        $query_url = $base_url.'?q=' . urlencode($yql_query) . $config;
          
        //get response
        $ch = curl_init($query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($this->config['returnType'] == 'json') {
            return $result;
        }
        //read json
        $object = json_decode($result);
        //select object node
        $data = $object->query->results->quote;
        // ÃœberprÃ¼fung des zurÃ¼ckgegebenen Array
        if (empty($data)) {
            echo 'No data found.';
            return null;
        } else {
            foreach ($data as $quote) {
                if (is_object($quote)) {
                    settype($quote, 'array');
                }
                unset($quote['date']); //unset values with key 'date', which are double
                $quoteList[] = $quote;
            }
            $quoteList = array_values($quoteList);
        }
        return $quoteList;
    }
 
    /**
    *   get historical quotes for provided symbol from yahoo.finance.com, direct query to url
    *   @param string $symbol
    *   @param string $startDate yyyy-mm-dd
    *   @param string $endDate yyyy-mm-dd
    *   @return array $quoteList - array with quotes
    */
    function getYahooHistoricalQuotesDirect($symbol, $startDate, $endDate)
    {
        $lineCount = 0;
        $dividendList = array();
        $startDay = date('j', strtotime($startDate));
        $startMonth = (string)(date('m', strtotime($startDate)) - 1); // yahoo index starts with 0 for january
        if ($startMonth < 10) { $startMonth = '0'.$startMonth; }
        $startYear = date('Y', strtotime($startDate));
        $endDay = date('j', strtotime($endDate));
        $endMonth = (string)(date('m', strtotime($endDate)) - 1); // yahoo index starts with 0 for january;
        if ($endMonth < 10) { $endMonth = '0'.$endMonth; }
        $endYear = date('Y', strtotime($endDate));
         
        // set request url
        $base_url = 'http://ichart.finance.yahoo.com/table.csv';
        $configStartDate = '&a='.$startMonth.'&b='.$startDay.'&c='.$startYear;
        $configEndDate = '&d='.$endMonth.'&e='.$endDay.'&f='.$endYear;
        $configValue = '&g=d&ignore=.csv'; // var d = daily
        $query_url = $base_url.'?s='.urlencode($symbol).$configStartDate.$configEndDate.$configValue;
         
        /*
        $ch = curl_init($query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $json = curl_exec($ch);     
             
        // JSON-Objekt erzeugen
        $object = json_decode($json);
        //dump_r($object, false, true, '', 0); //uncomment to see what json came back
        */
         
        // open query
        $fileHandle = fopen($query_url, 'r');
        if ($fileHandle) {
            do {
                // put each csv row into array
                $quoteValues = fgetcsv($fileHandle, 99999, ',');
                if ($quoteValues) {
                    // set new keys
                    foreach ($quoteValues as $key => $value) {
                        switch ($key) {
                            case 0:
                                $quoteValues['date'] = $value;
                                break;
                            case 1:
                                $quoteValues['open'] = $value;
                                break;
                            case 2:
                                $quoteValues['high'] = $value;
                                break;
                            case 3:
                                $quoteValues['low'] = $value;
                                break;
                            case 4:
                                $quoteValues['close'] = $value;
                                break;
                            case 5:
                                $quoteValues['volume'] = $value;
                                break;
                            case 6:
                                $quoteValues['adj_close'] = $value;
                                break;
                        } // end switch
                        unset($quoteValues[$key]);
                    }
                    $quoteList[$lineCount] = $quoteValues;
                    $lineCount++;
                }
            } while ($quoteValues);
        } else {
            $quoteList= '';
        }
        // close query handle
        fclose($fileHandle);
         
        //validate returned data
        if ( !empty($quoteList) ) {
            // delete empty elements
            foreach ($quoteList as $key => $listEntry) {
                if ( $listEntry['date'] == 'Date' ) {
                    unset($quoteList[$key]);
                }
            }
            $quoteList = array_values($quoteList);
        }
        return $quoteList;
    }
 
    /**
    *   get list of stocks info for provided symbol from yahoo.finance.com
    *   @param string $companySymbol - stock symbol
    *   @return array $stockInfoList - array with stock infos
    */
    function stockInfo($symbol)
    {
        // set yql query
        $yql_query = 'select * from yahoo.finance.stocks where symbol="'.$companySymbol.'"';
        // set request url
        $yql_base_url = 'http://query.yahooapis.com/v1/public/yql';
        $yql_config = '&format=json&env=store://datatables.org/alltableswithkeys&callback=';
        $yql_query_url = $yql_base_url.'?q='.rawurlencode($yql_query).$yql_config;
 
        // get response
        $ch = curl_init($yql_query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
 
        // create json and decode
        $json = curl_exec($ch);
        //vdump($json);  
        $object = json_decode($json);
        //vdump($object);  
 
        // check if some data is returned
        if (is_null($object->query->results)) {
            return $stockInfoList[] = '';
            exit; // exit function if no data is provided
        }
        // select object node
        $data = $object->query->results->stock;
        // ÃœberprÃ¼fung des zurÃ¼ckgegebenen Array
        if (empty($data)) {
            $stockInfoList = '';
        } else {
            foreach ($data as $key => $stockInfo) {
                if (is_object($stockInfo)) {
                    settype($stockInfo, 'array');
                }
                $key = lcfirst($key);
                $stockInfoList[$key] = $stockInfo;
            }
        }
        return $stockInfoList;
    }
 
    /**
    *   get list of component for indices symbol from yahoo.finance.com
    *   @param mixed $symbol
    *   @return array $indicesList - array with symbols
    */
    function index($symbol = array())
    {
        //default indices
        $defaultSymbols = array(
            'DAX' => '^GDAXI',
            'MDAX' => '^MDAXI',
            'SDAX' => '^SDAXI',
            'TecDAX'=> '^TECDAX',
            'EuroSTOXX' => '^STOXX50E',
            'FTSE100' => '^FTSE',
            'DJI' => '^DJI',
            'NASDAQ100' => '^NDX'
        );
        $this->vdump($symbol);
        foreach ($symbol as $symbolKey => $symbolVal) {
            //set request url
            $base_url = 'http://download.finance.yahoo.com/d/quotes.csv';
            $config = '&f=sn&e=.csv';
            $query_url = $base_url.'?s=@'.urlencode($indiceSymbol).$config;
             
            //get response
            $ch = curl_init($query_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            $result = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($this->config['returnType'] == 'json') {
                return $result;
            }
            $this->vdump($result);
            //read json
            $object = json_decode($result);
            //select object node
            $data = $object->query->results->quote;
         
        /*
            // open query
            $fileHandle = fopen($query_url, 'r');
            if ($fileHandle) {
                $lineCount = 0;
                $indice = array();
                do {
                    // put each csv row into array
                    $indicesValues = fgetcsv($fileHandle, 99999, ',');
                    if ($indicesValues) {
                        $indice[$lineCount] = $indicesValues[0];
                        $lineCount++;
                    }
                } while ($indicesValues);
            }
            // close query handle
            fclose($fileHandle);
            */
 
            // delete empty elements
            foreach ($indice as $key => $listEntry) {
                if ($listEntry == NULL) {
                    unset($indice[$key]);
                }
            }
            //get list for group indice
            $groupMember[$indiceKey] = array_values($indice);
        }
        return $indicesList;
    }
 
    /**
    *   get list of sectors with corresponding industries from yahoo.finance.com
    *   @return array $sectorsList - array with sectors
    */
    function sectors()
    {
        //set yql query
        $yql_query = 'select * from yahoo.finance.sectors';
        //set request url
        $base_url = 'http://query.yahooapis.com/v1/public/yql';
        $config = '&format=json&env=store://datatables.org/alltableswithkeys&callback=';
        $query_url = $base_url . '?q=' . rawurlencode($yql_query) . $config;
 
        //get response
        $ch = curl_init($_query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($this->config['returnType'] == 'json') {
            return $result;
        }
        //read json
        $object = json_decode($result);
        // check if some data is returned
        if (is_null($object->query->results)) {
            return null;
        }
        // select object node
        $data = $object->query->results->sector;
        // ÃœberprÃ¼fung des zurÃ¼ckgegebenen Array
        if (empty($data)) {
            $sectorsList = '';
        } else {
            foreach ($data as $sector) {
                if (is_object($sector)) {
                    settype($sector, 'array');
                }
                $sectorEntry = array('name' => $sector['name']);
                $industryList = array();
                if (empty($sector['industry'])) {
                    $industryList = '';
                } elseif( is_object($sector['industry']) ) {
                    settype($sector['industry'], 'array');
                    $sectorEntry['industry'] = array($sector['industry']);
                } else {
                    foreach ($sector['industry'] as $industry) {
                        if (is_object($industry)) {
                            settype($industry, 'array');
                        }
                        $industryList[] = $industry;
                    }
                    $sectorEntry['industry'] = $industryList;
                }
                $sectorsList[] = $sectorEntry;
            }
        }
        return $sectorsList;
    }
     
    /**
    *   helper function to wrap var_dump() with <pre></pre>
    *   @param string $mixed
    */
    function vdump($mixed)
    {
        echo '<pre><code>';
        $variables = func_get_args($mixed);
        foreach ($variables as $var) {
            ob_start();
            var_dump($var);
            $content = ob_get_contents();
            ob_end_clean();
            echo $content;
        }
        echo '</code></pre>';
    }
 
}
