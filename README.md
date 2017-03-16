# YahooFinanceQuery

A PHP class to query the Yahoo Finance API.

## Features

- [symbolSuggest()](#symbol-suggest) - search for symbol via Yahoo.Finance.Symbol.AutoSuggest
- [quote()](#quote) - query current quotes for symbols
- [historicalQuote()](#historical-quote) - query historical quotes for single symbol
- [intraDay()](#intra-day) - query intraday quotes for single symbol
- [stockInfo()](#stock-info) - query basic stock info
- [indexList()](#index-list) - query list of related stocks for index symbols
- [sectorList()](#sector-list) - query full list of sectors with related industries

## Example

You can test an example.php at http://code.dirkolbrich.de/YahooFinanceQuery/

## Installation

require via `composer.json` in your project root
```json
{
    "require": {
        "dirkolbrich/yahoo-finance-query": "dev-master"
    }
}
```

## Implementation

As simple as that:
```php
use YahooFinanceQuery\YahooFinanceQuery;
// [...]
$query = new YahooFinanceQuery;
```

or as static:
```php
use YahooFinanceQuery\YahooFinanceQuery;
// [...]
YahooFinanceQuery->make();
```

## Configuration

YahooFinanceQuery can be configured to return the data as an array or the raw json. Default `returnType` is `array`.

The config setting has to be passed as an array `array('returnType' => 'array')` or  `array('returnType' => 'json')`.

At initialisation:
```php
$query = new YahooFinanceQuery(array('returnType' => 'json'));
```
or as static:
```php
YahooFinanceQuery->make(array('returnType' => 'json'));
```

At run-time:
```php
$query = new YahooFinanceQuery;
$query->config(array('returnType' => 'json'));
```

The current config setting can be retrieved with:
```php
$query->getConfig();
```

## Usage

To retrieve the results simple call the appropiate method.
```php
$data = $query->method();
```

To change the return type to json at runtime use `toJson()` as addition to the query. This method must be called before the actural query method.
```php
$query->toJson()->method();
```

To retrieve the raw cURL result use `raw()` as addition to the query. This method must be called before the actural query method.
```php
$query->raw()->method();
```

To force the query via the YQL api (if possible), use the `yql()` method within the query string. This method must be called before the actural query method. The default is set to not use YQL, as I think YQL querys are unreliable and often return truncated results.
```php
$query->yql()->method();
```

The following query methods are available:

## Methods

<a name="symbol-suggest"></a>
### `symbolSuggest($string)`

Query for a symbol suggestion via the YAHOO.Finance.SymbolSuggest.ssCallback

```php
$string = 'basf';
$data = $query->symbolSuggest($string);
```

Returns a formated array:
```php
array[
    'ok' => true,
    'meta' => array[
        'status' => 200,
        'query' => 'basf',
    ],
    'data' => array[
        0 => array[
            'type' => 'symbols',
            'id' => 1,
            'attributes' => array[
                'symbol' => 'BAS.DE',
                'name' => 'BASF SE',
                'exch' => 'GER',
                'type' => 'S',
                'exchDisp' => 'XETRA',
                'typeDisp' => 'Equity'
            ]
        ],
        // any number of following results            
    ]
];
```

If no symbol is found but the query was vald, the query will return an empty `data` array:
```php
array[
    'ok' => true,
    'meta' => array[
        'status' => 200,
        'query' => 'basf'
    ],
    'data' => array[],
];
```

If the query was invalid, the query will return a 404 error:
```php
array[
    'ok' => false,
    'meta' => array[
        'status' => 404,
        'query' => 'basf'
    ],
    'errors' => array[
        'status' => 404,
        'title' =>  'Recource not found'
        'details' => 'Could not find symbol for "basf"'
    ],
];
```

<a name="quote"></a>
### `quote(array $symbol [, array $params])`

Query for a current quote for the given symbols and given parameters.

The passed parameter `$symbol` must be an array. Multiple symbols can be combinde and passed together.

The passed parameter `$params` is optional and must be an array too. It accepts the parameters as a written word or as tags. See the `$quoteParams` variable in the class definition as reference. If `$params` is empty, the query will use all possible params.

The params 'Symbol', 'LastTradeTime' and 'LastTradeDate' will be quered by default. There will be a unified UTC 'LastTradeTimestamp' added to the result array.

```php
$symbol = array('bas.de');
$params = array('LastTradePriceOnly', 'x', 'c1');
$data = $query->quote($symbol, $params);
```

<a name="historical-quote"></a>
### `historicalQuote(array $symbol [, $startDate, $endDate, $param])`

Query for historical quotes for given symbol with given start date and end date.

Only one `$symbol` can be passed per query and must be a string.

`$startDate` and `$endDate` must be in the format YYYY-MM-DD. If no dates are passed, the query will grab all available historical quotes. If only one date is passed, the other one will be set to the maximum available.

`$param` is set to default `d` = daily. See `$historicalQuoteParams` variable for other options.

```php
$symbol = array('bas.de');
$startDate = 2013-07-26;
$endDate = 2014-01-06;
$param = 'd';
$data = $query->historicalQuote($symbol, $startDate, $endDate, $param);
```
I recommend not to use the `yql()` method with historical quotes, as the YQL console permits only up to 365 single result quotes. To retrieve a full set of historical quotes will not be possible via YQL.

<a name="intra-day"></a>
### `intraDay($symbol [, $period, $param])`

Query finance.yahoo.com for intraday quotes. The symbol must be passed as as string.

`$period` is optional and default set to `1d`. It is possible to retrieve intraday quotes for up to the last 15 days.

`$param` is optional and default set to `quote`. For other options see the `$intraDayParams` variable.

```php
$symbol = 'bas.de';
$period = '5d';
$data = $query->intraDay($symbol, $period);
```

<a name="stock-info"></a>
### `stockInfo($symbol)`

Query finance.yahoo.com for basic stock information. The symbol must be passed as as string.

```php
$symbol = 'bas.de';
$data = $query->stockInfo($symbol);
```

<a name="index-list"></a>
### `indexList(array $symbol)`

Query for an index which returns the symbol and name of the components. Several symbols may be passed as an array.

See http://finance.yahoo.com/intlindices?e=europe for more symbols to world indices. The caret `^` character must be part of the symbol.

```php
$symbol = array('^GDAXI');
$data = $query->indexList($symbol);
```

<a name="sector-list"></a>
### `sectorList()`

Query for a complete list of sectors and their corresponding industries.

This function is static without any params.

```php
$data = $query->sectorList();
```

Which returns an array in this form:

```php
array[
    0 => array[
        'name' => 'Basic Materials',
        'industries' => array[
            0 => array[
                'name' => 'Agricultural Chemicals',
                'id' => 112
                ],
            1 => array[
                'name' => 'Aluminum'
                'id' => 132
                ],
        ],
    ],
    1 => array[
        'name' => 'Conglomerates',
        'industries' => array[...],
    ],
];
```

## Recources

Some informative blog post and websites:

Yahoo YQL homepage
* http://developer.yahoo.com/yql/

Blog post on that matter
* http://www.yqlblog.net/blog/2009/06/02/getting-stock-information-with-yql-and-open-data-tables/

Blog post from Joseph D. Purcell with an overview over finance API'S is a good entry point
* http://thesimplesynthesis.com/article/finance-apis

www.gummy-stuff.org explains the Yahoo Finance .csv API
* http://www.gummy-stuff.org/Yahoo-data.htm

An overview over different api endpoints by Matthias Brusdeylins (german)
* http://brusdeylins.info/projects/yahoo-finance-api/

Tutorial for the YQL console by Thomas Belser (german)
* http://www.thomasbelser.net/2011/12/13/auslesen-von-aktienkursen-und-deren-symbole-mit-php-und-yql/
