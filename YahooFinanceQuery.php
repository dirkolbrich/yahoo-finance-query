<?php
/**
 * YahooFinanceQuery - a PHP class to query the Yahoo Finance API
 *
 * @author      Dirk Olbrich <mail@dirkolbrich.de>
 * @copyright   2013 Dirk Olbrich
 * @link        https://github.com/dirkolbrich/YahooFinanceQuery
 * @license     MIT
 * @version     0.1
 * @package     YahooFinanceQuery
 *
 */

namespace YahooFinanceQuery;

class YahooFinanceQuery
{
    public $config = array(
        'returnFormat' => 'string' // 'string' or 'json'
        );

    public function __construct()
    {
    }
    public function config()
    {
    }

    /**
    *   get list of stocks with symbol and market for provided search string from yahoo.finance.com's stock symbol autosuggest callback 
    *   @param string $searchString - name to search for
    *   @return array $list - array with stock symbols
    */
    public function symbolSuggest($searchString)
    {
        // set url for callback
        $url = 'http://d.yimg.com/autoc.finance.yahoo.com/autoc?query='.urlencode($searchString).'&callback=YAHOO.Finance.SymbolSuggest.ssCallback';     
        // initiate curl request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);    
        $result = curl_exec($ch);                               // read data from callback
        $json = preg_replace('/.+?({.+}).+/', '$1', $result);   // convert to JSON
        $object = json_decode($json);                           // decode JSON to object
        $data = $object->ResultSet->Result;                     // select data

        if ($data) {
            $i = 0;
            foreach($data as $suggest) {
                $list[$i]['symbol']     = (empty($suggest->symbol) ? null : $suggest->symbol);
                $list[$i]['name']       = (empty($suggest->name) ? null : $suggest->name);
                $list[$i]['exch']       = (empty($suggest->exch) ? null : $suggest->exch);
                $list[$i]['type']       = (empty($suggest->type) ? null : $suggest->type);
                $list[$i]['exchDisp']   = (empty($suggest->exchDisp) ? null : $suggest->exchDisp);
                $list[$i]['typeDisp']   = (empty($suggest->typeDisp) ? null : $suggest->typeDisp);
                $i++;
            }
        } else {
            // no data found
            return null;
        }
        return $list;
    }

    /**
    *   get stock quotes for provided symbols from yahoo.finance.com
    *   @param mixed $symbol - string or array with symbol/s
    *   @return array $quoteList - array with quotes
    */
    public function currentQuotes($symbol)
    {
        // check if string contains duplicates, remove and rearrange numbers
        $symbolList = array_values(array_unique($symbolList));
        // split up string into packages
        $symbolList = array_chunk($symbolList, 20); // yahoo.finance.quotes accepts up to 20 symbols at once
        // prepare list of symbols for string
        foreach ($symbolList as $listKey => $listChunk) {
            foreach ($listChunk as $key => $symbol) {
                // add marks to each symbol
                $listChunk[$key] = '"'.$symbol.'"';
            }
            // transform array to string
            $listChunkString = implode(', ', $listChunk);
            //put strings into arry
            $symbolList[$listKey] = $listChunkString;
        }
        
        // request quotes
        foreach ($symbolList as $symbolListString) {
            // list of parameters
            $lastTradeDate = 'LastTradeDate';
            $lastTradeTime = 'LastTradeTime';
            $lastTradePriceOnly = 'LastTradePriceOnly';
            $previousClose = 'PreviousClose';
            $change = 'Change';
            $percentChange = 'PercentChange';
            
            /* set yql query */
            $yql_query = 'select Symbol, LastTradeDate, LastTradeTime, LastTradePriceOnly, PreviousClose, Change, PercentChange from yahoo.finance.quotes where symbol in ('.$symbolListString.')';
            
            /* set request url */
            $yql_base_url = 'http://query.yahooapis.com/v1/public/yql';
            $yql_config = '&format=json&env=http://datatables.org/alltables.env&callback=';
            $yql_query_url = $yql_base_url.'?q='.urlencode($yql_query).$yql_config;
            
            /* get response */
            $ch = curl_init($yql_query_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);    
            $json = curl_exec($ch);    
            
            /* JSON-Objekt erzeugen */
            $object = json_decode($json);
            
            // check if some data is returned
            if (is_null($object->query->results)){
                return $quoteList[] = '';
                exit;
            }
            
            /* Objektknoten auswählen */
            $data = $object->query->results->quote;
            
            /* Kursdaten in Array schreiben */
            foreach ($data as $dataEntry) {
                $symbol = $dataEntry->Symbol;
                $quote[$lastTradePriceOnly] = $dataEntry->LastTradePriceOnly;
                
                // normalise timezone for date and time to utc/gmt
                // as it appears that these values are set with different timezones depending 
                // on the symbol/market and the server the request goes to
                $time = $dataEntry->LastTradeTime; // is time of server, EST for finance.yahoo.com
                $timeZone = new DateTimeZone('America/New_York'); // set timezone
                $time = date_create_from_format('g:ia', $time, $timeZone);
                $time->setTimeZone(new DateTimeZone('GMT')); // change timezone
                $time = date_format($time, 'H:i:s'); // extract time for that timezone
            
                $date = $dataEntry->LastTradeDate; // in home exchanges timezone, GMT for Xetra
                $timeZone = new DateTimeZone('GMT'); // set timezone
                $date = date_create_from_format('n/j/Y', $date, $timeZone);
                $date = date_format($date, 'd.m.Y'); // extract date for that timezone
                $dateTime = sprintf('%s %s',$date, $time); // merge date and time as string, separator whitespace
                $timeZone = new DateTimeZone('GMT'); // set timezone
                $timeStamp = date_create_from_format('d.m.Y H:i:s', $dateTime, $timeZone); // generate timestamp
                $quote['LastTradeTimeStamp'] = $timeStamp;
                
                $quote[$previousClose] = $dataEntry->PreviousClose;
                $quote[$change] = $dataEntry->Change;
                $quote[$percentChange] = $dataEntry->PercentChange;
                
                // Array in Kursliste schreiben, Symbol als Key
                $quoteList[$symbol] = $quote;
            }
        }
        // order $quoteList alphabetical
        ksort($quoteList);
        // Rückgabe der Kursliste als array
        return $quoteList;
    }

    /**
    *   get historical quotes for provided symbol from yahoo.finance.com, use YQL and open datatables
    *   @param string $symbol
    *   @param string $startDate yyyy-mm-dd
    *   @param string $endDate yyyy-mm-dd
    *   @return array $quoteList - array with quotes
    */
    public function getYahooHistoricalQuotesYQL($symbol, $startDate, $endDate)
    {
        vdump($symbol, $startDate, $endDate);

        // set yql query
        $yql_query = 'select * from yahoo.finance.historicaldata where symbol = "'.$symbol.'" and startDate="'.$startDate.'" and endDate="'.$endDate.'"';
         
        // set request url 
        $yql_base_url = 'http://query.yahooapis.com/v1/public/yql';
        $yql_config = '&format=json&env=http://datatables.org/alltables.env&callback=';
        $yql_query_url = $yql_base_url.'?q='.urlencode($yql_query).$yql_config;
         
        // get response
        $ch = curl_init($yql_query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $json = curl_exec($ch);     
            
        // JSON-Objekt erzeugen
        $object = json_decode($json);
        //dump_r($object, false, true, '', 0); //uncomment to see what json came back
        
        // Objektknoten auswählen
        $data = $object->query->results->quote;
        // Überprüfung des zurückgegebenen Array
        if (empty($data)) {
            //echo 'Es wurden keine Daten zurückgegeben. Die Abfrage wird beendet.</br>';
            $quoteList = '';
        } else {
            foreach ($data as $quoteSet) {
                if (is_object($quoteSet)) {
                    settype($quoteSet, 'array');
                }
                unset($quoteSet['date']); // unset values with key 'date', which are double
                // change case of keys
                $quoteSet = array_change_key_case($quoteSet, CASE_LOWER);
                $quoteList[] = $quoteSet;
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
    function getYahooStockInfo($companySymbol)
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
        // Überprüfung des zurückgegebenen Array
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
    *   get list with component of provided indices symbol from yahoo.finance.com
    *   @return array $indicesList - array with symbols
    */
    function getYahooIndices()
    {
        //default indices
        $indiceSymbols = array(
                'Germany' => array(
                    'DAX' => '^GDAXI',
                    'MDAX' => '^MDAXI',
                    'SDAX' => '^SDAXI',
                    'TecDAX'=> '^TECDAX'
                ),
                'Europe' => array(
                    'EuroSTOXX' => '^STOXX50E',
                    'FTSE100' => '^FTSE'
                ),
                'USA' => array(
                    'DJI' => '^DJI',
                    'NASDAQ100' => '^NDX'
                )
            );
        $indicesList = array();

        foreach ($indiceSymbols as $groupKey => $groupMember) {
            foreach ($groupMember as $indiceKey => $indiceSymbol) {
            
                // set request url
                $base_url = 'http://download.finance.yahoo.com/d/quotes.csv';
                $config = '&f=sn&e=.csv';
                $query_url = $base_url.'?s=@'.urlencode($indiceSymbol).$config;
                
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

                // delete empty elements
                foreach ($indice as $key => $listEntry) {
                    if ($listEntry == NULL) {
                        unset($indice[$key]);
                    }
                }
                //get list for group indice
                $groupMember[$indiceKey] = array_values($indice);
            }
            $indicesList[$groupKey] = $groupMember;
        }
        return $indicesList;
    }

    /**
    *   get list of sectors with corresponding industries from yahoo.finance.com
    *   @return array $sectorsList - array with sectors
    */
    function getYahooSectors()
    {
        // set yql query
        $yql_query = 'select * from yahoo.finance.sectors';
        // set request url
        $yql_base_url = 'http://query.yahooapis.com/v1/public/yql';
        $yql_config = '&format=json&env=store://datatables.org/alltableswithkeys&callback=';
        $yql_query_url = $yql_base_url.'?q='.rawurlencode($yql_query).$yql_config;

        // get response
        $ch = curl_init($yql_query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        // create json and decode
        $json = curl_exec($ch);
        $object = json_decode($json);
        //vdump($object);  

        // check if some data is returned
        if (is_null($object->query->results)) {
            return $sectorsList[] = '';
            exit; // exit function if no data is provided
        }
        // select object node
        $data = $object->query->results->sector;
        // Überprüfung des zurückgegebenen Array
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
    }//function getYahooSectors()

}
