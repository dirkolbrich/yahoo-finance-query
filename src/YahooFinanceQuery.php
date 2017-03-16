<?php namespace YahooFinanceQuery;

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

use YahooFinanceQuery\Queries\IndexList;
use YahooFinanceQuery\Queries\StockInfo;
use YahooFinanceQuery\Queries\SectorList;
use YahooFinanceQuery\Queries\CurrentQuote;
use YahooFinanceQuery\Queries\IntraDayQuote;
use YahooFinanceQuery\Queries\SymbolSuggest;
use YahooFinanceQuery\Queries\HistoricalQuote;

class YahooFinanceQuery
{
    /**
     * @var array
     */
    protected $config = array(
        'returnType' => 'array', // 'array' or 'json'
        );

    /**
     * @var string
     */
    protected $query;

    /**
     * @var bool
     */
    protected $raw = false;

    /**
     * @var bool
     */
    protected $yql = false;

    /**
     * @var bool
     */
    protected $toJson = false;

    /**
     * constructor with optional config param
     * @param array $config - key / value for configuration
     */
    public function __construct($config = array())
    {
        if (!empty($config)) {
            $this->config($config);
        }
    }

    /**
     * creator with optional config param
     * @param array $config - key / value for configuration
     * @return YahooFinanceQuery $query
     */
    public static function make($config = array())
    {
        return new YahooFinanceQuery($config);
    }

    /**
     * configurator
     * @param array $config - key => value for configuration
     * @return self
     */
    public function config($config = array())
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
     * @return array $config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * return the raw response from a request
     * @return self
     */
    public function raw()
    {
        $this->raw = true;
        return $this;
    }

    /**
     * directs all requests via the YQL console, default if possible is requests via .csv
     * @return self
     */
    public function yql()
    {
        $this->yql = true;
        return $this;
    }

    /**
     * return the result as JSON format
     * @return self
     */
    public function toJson()
    {
        $this->toJson = true;
        return $this;
    }

    /**
     * get results from the query
     * @return string $data
     */
    protected function get()
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
        return $this->query->result;
    }

    /**
     * get a list of stocks with symbol and market for provided search string
     * from yahoo.finance.com's stock symbol autosuggest callback
     * @param string $searchString - the name/string to search for
     * @return self
     */
    public function symbolSuggest($searchString)
    {
        $query = new SymbolSuggest($this->yql);
        $this->query = $query->query($searchString);
        return $this->get();
    }

    /**
     * get stock quotes for provided symbols from yahoo.finance.com
     * @param array $symbolList - array with symbol/s
     * @param array $searchParams - array with query params
     * @return self
     */
    public function quote($symbolList, $searchParams = null)
    {
        $query = new CurrentQuote($this->yql);
        $this->query = $query->query($symbolList, $searchParams);
        return $this->get();
    }

    /**
     * get historical quotes for provided symbol from yahoo.finance.com, direct query to csv
     * @param string $symbol
     * @param string $startDate - yyyy-mm-dd
     * @param string $endDate - yyyy-mm-dd
     * @param string $param - type of data
     * @return self
     */
    function historicalQuote($symbol, $startDate = '', $endDate = '', $param = 'd')
    {
        $query = new HistoricalQuote($this->yql);
        $this->query = $query->query($symbol, $startDate, $endDate, $param);
        return $this->get();
    }

    /**
     * get intraday quotes for provided symbol from yahoo.finance.com, direct query to csv
     *
     * @param string $symbol
     * @param string $period
     * @param string $param - type of data
     * @return self
     */
    function intraDay($symbol, $period = '1d', $param = 'quote')
    {
        $query = new IntraDayQuote($this->yql);
        $this->query = $query->query($symbol, $period, $param);
        return $this->get();
    }

    /**
     * get list of info for provided symbol from yahoo.finance.com
     * @param string $symbol - stock symbol
     * @return self
     */
    function stockInfo($symbol)
    {
        $query = new StockInfo($this->yql);
        $this->query = $query->query($symbol);
        return $this->get();
    }

    /**
     * get list of component for indices symbol from yahoo.finance.com
     * @param array $symbols
     * @return self
     */
    function indexList($symbols)
    {
        $query = new IndexList($this->yql);
        $this->query = $query->query($symbols);
        return $this->get();
    }

    /**
     * get full list of sectors with corresponding industries from yahoo.finance.com
     * @return self
    */
    function sectorList()
    {
        $query = new SectorList($this->yql);
        $this->query = $query->query();
        return $this->get();
    }
}
