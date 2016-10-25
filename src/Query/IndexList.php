<?php namespace DirkOlbrich\YahooFinanceQuery\Query;

    /**
     * YahooFinanceQuery - a PHP package to query the Yahoo Finance API
     *
     * @author      Dirk Olbrich <mail@dirkolbrich.de>, Ralf Geisthardt<RalfGe@ihr-it-projekt.de>
     * @copyright   2013-2015 Dirk Olbrich
     * @link        https://github.com/dirkolbrich/YahooFinanceQuery
     * @license     MIT
     * @version     1.0.0
     * @package     YahooFinanceQuery
     */

/**
 * Class IndexList
 *
 * @package DirkOlbrich\YahooFinanceQuery\Query
 */
class IndexList extends Query
{
    /**
     * get list of component for indices symbol from yahoo.finance.com
     *
     * @param array $symbol
     *
     * @return $this
     */
    public function query(array $symbol)
    {
        $this->queryString = $symbol;

        if ($this->yql) { // request via yql console
            $data = $this->queryYQL();
        } else { // direct request via .csv
            $data = $this->queryDirect();
        }

        $this->result = $data;

        return $this;
    }

    /**
     * query finance.yahoo.com via screen scrapper
     *
     * @return array
     */
    private function queryDirect()
    {
        $data = [];

        foreach ($this->queryString as $indexSymbol) {
            //set request url
            $this->queryUrl = sprintf(
                'https://query2.finance.yahoo.com/v10/finance/quoteSummary/%s?modules=components&corsDomain=finance.yahoo.com',
                urlencode($indexSymbol)
            );
            //curl request
            $this->curlRequest($this->queryUrl);

            $result = json_decode($this->response['result'], true);

            $index = [];
            if ($this->componentsExists($result)) {
                $index = $this->getComponents($result);
            }

            // set array
            $indexData['symbol']     = $indexSymbol;
            $indexData['components'] = $index;
            $data[]                  = $indexData;
        }

        return $data;
    }

    /**
     * @return array
     */
    private function queryYQL()
    {
        return [];
    }

    /**
     * @param $result
     *
     * @return bool
     */
    private function componentsExists($result)
    {
        if (array_key_exists('quoteSummary', $result)
            && array_key_exists('result', $result['quoteSummary'])
            && array_key_exists(0, $result['quoteSummary']['result'])
            && array_key_exists(
                'components',
                $result['quoteSummary']['result'][0]
            )
            && is_array($result['quoteSummary']['result'][0]['components'])
            && array_key_exists(
                'components',
                $result['quoteSummary']['result'][0]['components']
            )
            && is_array(
                $result['quoteSummary']['result'][0]['components']['components']
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $result
     *
     * @return array
     */
    private function getComponents($result)
    {
        return $result['quoteSummary']['result'][0]['components']['components'];
    }
}