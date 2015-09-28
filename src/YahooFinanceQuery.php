<?php namespace DirkOlbrich\YahooFinanceQuery;

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

use DirkOlbrich\YahooFinanceQuery\Query\IndexList;
use DirkOlbrich\YahooFinanceQuery\Query\StockInfo;
use DirkOlbrich\YahooFinanceQuery\Query\SectorList;
use DirkOlbrich\YahooFinanceQuery\Query\CurrentQuote;
use DirkOlbrich\YahooFinanceQuery\Query\IntraDayQuote;
use DirkOlbrich\YahooFinanceQuery\Query\SymbolSuggest;
use DirkOlbrich\YahooFinanceQuery\Query\HistoricalQuote;

class YahooFinanceQuery
{
    /**
     * @var array
     */
    protected $config = array(
        'returnType' => 'array', // 'array' or 'json'
        );

    protected $query;
    protected $raw = false;
    protected $yql = false;
    protected $toJson = false;

    /**
     * constructor with optional config param
     * @param array $config - key => value for configuration
     * @return void
     */
    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->config($config);
        }
    }

    /**
     * creator with optional config param
     *
     * @param array $config - key => value for configuration
     * @return YahooFinanceQuery $query
     */
    public static function make(array $config = array())
    {
        return new YahooFinanceQuery($config);
    }

    /**
     * configurator
     *
     * @param array $config - key => value for configuration
     * @return self
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
     * get configurator setting
     *
     * @return array $this-config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * as it says, returns the raw response from a request
     *
     * @return self
     */
    public function raw()
    {
        $this->raw = true;
        return $this;
    }

    /**
     * directs all requests via the YQL console, default if possible is requests via .csv
     *
     * @return self
     */
    public function yql()
    {
        $this->yql = true;
        return $this;
    }

    /**
     * as it says, get the results
     *
     * @return $data
     */
    public function get()
    {
        // check for raw response
        if ($this->raw) {
            $this->raw = false;
            return $this->query->response;
        }
        // check returnType
        if ($this->config['returnType'] == 'json' or ($this->toJson)) {
            $this->toJson = false;
            return json_encode($this->query->result);
        }
        // reset $this->yql
        return $this->query->result;
    }

    /**
     * return the result as JSON format
     *
     * @return self
     */
    public function toJson()
    {
        $this->toJson = true;
        return $this;
    }

    /**
     * get a list of stocks with symbol and market for provided search string
     * from yahoo.finance.com's stock symbol autosuggest callback
     *
     * @param string $searchString - the name/string to search for
     * @return self
     */
    public function symbolSuggest($searchString)
    {
        $query = new SymbolSuggest($this->yql);
        $this->query = $query->query($searchString);

        return $this;
    }

    /**
     * get stock quotes for provided symbols from yahoo.finance.com
     *
     * @param array $symbolList - array with symbol/s
     * @param array $searchParams - array with query params
     * @return self
     */
    public function quote(array $symbolList, array $searchParams = null)
    {
        $query = new CurrentQuote($this->yql);
        $this->query = $query->query($symbolList, $searchParams);

        return $this;
    }

    /**
     * get historical quotes for provided symbol from yahoo.finance.com, direct query to csv
     *
     * @param string $symbol
     * @param string $startDate - yyyy-mm-dd
     * @param string $endDate - yyyy-mm-dd
     * @param string $param - type of data
     */
    function historicalQuote($symbol, $startDate = '', $endDate = '', $param = 'd')
    {
        $query = new HistoricalQuote($this->yql);
        $this->query = $query->query($symbol, $startDate, $endDate, $param);

        return $this;
    }

    /**
     * get intraday quotes for provided symbol from yahoo.finance.com, direct query to csv
     *
     * @param string $symbol
     * @param string $param - type of data
     * @return array $quoteList - array with quotes
     * @return self
     */
    function intraDay($symbol, $period = '1d', $param = 'quote')
    {
        $query = new IntraDayQuote($this->yql);
        $this->query = $query->query($symbol, $period, $param);

        return $this;        
    }

    /**
     * get list of info for provided symbol from yahoo.finance.com
     *
     * @param string $symbol - stock symbol
     */
    function stockInfo($symbol)
    {
        $query = new StockInfo($this->yql);
        $this->query = $query->query($symbol);

        return $this;
    }

    /**
     * get list of component for indices symbol from yahoo.finance.com
     *
     * @param array $symbol
     */
    function indexList(array $symbols)
    {
        $query = new IndexList($this->yql);
        $this->query = $query->query($symbols);

        return $this;
    }

    /**
    *   get full list of sectors with corresponding industries from yahoo.finance.com
    *
    *   @return self
    */
    function sectorList()
    {
        $query = new SectorList($this->yql);
        $this->query = $query->query();

        return $this;
    }

}
