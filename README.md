YahooFinanceQuery
=================
A PHP class to query the Yahoo Finance API.

Implementation
--------------
As simple as that:
```php
require 'YahooFinanceQuery.php';
$query = new YahooFinanceQuery\YahooFinanceQuery;
```

Configuration
-------------
YahooFinanceQuery can be configured to return the data as an array or the raw json. Default `returnType` is 'array'.

The config setting has to be passed as an array `array('returnType' => 'array')` or  `array('returnType' => 'json')`.

At initialisation:
```php
$query = new YahooFinanceQuery\YahooFinanceQuery(array('returnType' => 'json'));
```

At run-time:
```php
$query = new YahooFinanceQuery\YahooFinanceQuery;
$query->config(array('returnType' => 'json'));
```

The current config setting can be retrieved with:
```php
$query->getConfig();
```

Usage
-----
1. `symbolSuggest($string)`

    Query for symbol suggestion via the YAHOO.Finance.SymbolSuggest.ssCallback
    ```php
    $string = 'basf';
    $query->symbolSuggest($string);
    ```

2. `quote(array $symbol [, array $params])`

    Query for current quote for given symbols and given parameters.
    
    The passed parameter `$symbol` must be an array. Several symbols can be passed.
    
    The passed parameter `$params` is optional and must be an array too. It accepts the parameters as a written word or as tags. See as reference the `$quoteParams` variable in the class definition. If `$params` is empty, the query will use all possible params. 
    The params 'Symbol', 'LastTradeTime' and 'LastTradeDate' will be quered by default. 
    ```php
    $symbol = array('bas.de');
    $params = array('LastTradePriceOnly', 'x', 'c1');
    $query->quote($symbol, $params);
    ```

3. `historicalQuote(array $symbol, $startDate, $endDate [, $param])`

    Query for historical quotes for given symbol with given start date and end date.

    Only one `$symbol` can be passed per query and must be a string.

    `$startDate` and `$endDate` must be in the format YYYY-MM-DD.

    `$param`is set to default 'd' = daily. See `$queryParams`in the method for other options. 

    ```php
    $symbol = array('bas.de');
    $startDate = 2013-07-26;
    $endDate = 2014-01-06
    $param = 'd';
    $query->historicalQuote($symbol, $startDate, $endDate, $param);
    ```

4. `index(array $symbol)`

    Query for an index which returns the symbol and name of the components. Several symbols may be passed as aa array.

    See http://finance.yahoo.com/intlindices?e=europe for more symbols to world indices. The caret `^` character must be part of the symbol.

    ```php
    $symbol = array('^GDAXI');
    $query->index($symbol);
    ```

Recources
---------
Some informative blog post and websites:

Yahoo YQL homepage
* http://developer.yahoo.com/yql/

Blog post on that matter
* http://www.yqlblog.net/blog/2009/06/02/getting-stock-information-with-yql-and-open-data-tables/

Blog post from Joseph D. Purcell with an overview over finance API'S is a good entry point
* http://thesimplesynthesis.com/article/finance-apis

www.gummy-stuff.org explains the Yahoo Finance .csv API
* http://www.gummy-stuff.org/Yahoo-data.htm

Tutorial by Thomas Belser (german)
* http://www.thomasbelser.net/2011/12/13/auslesen-von-aktienkursen-und-deren-symbole-mit-php-und-yql/
