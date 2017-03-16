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

/**
 *
 */
class Query
{
    /**
     * @var bool
     */
    protected $yql;

    /**
     * @var string
     */
    protected $queryString;

    /**
     * @var array
     */
    protected $queryParams;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $queryUrl;

    /**
     * @var string
     */
    public $result;

    /**
     * @var array
     */
    public $response;

    /**
     * constructor with $yql param
     * @param bool $yql - setting if YQL is used
     */
    function __construct(bool $yql)
    {
        $this->yql = $yql;
    }

    /**
    *   cURL request method
    *
    *   @param string $url
    *   @return array $response
    */
    protected function curlRequest(string $url)
    {
        $response = [];

        // check for config setting of CURLOPT_USERAGENT in $this->config, else set to NULL
        $userAgent = @($this->config['userAgent'] ?: $_SERVER["HTTP_USER_AGENT"] ?: null);

        //curl request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response['result'] = curl_exec($ch);
        $response['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response['error'] = curl_error($ch);
        $response['errno'] = curl_errno($ch);
        curl_close($ch);

        $this->response = $response;
    }
}