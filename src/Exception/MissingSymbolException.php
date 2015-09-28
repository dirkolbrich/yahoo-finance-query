<?php namespace DirkOlbrich\YahooFinanceQuery\Exception;

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
 * Exception thrown when no symbol is provided.
 *
 * @trown
 */
class MissingSymbolException extends \InvalidArgumentException
{

}
