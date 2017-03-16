### Changelog

- 1.0.5 (2015-10-26)
    * fixed indexList() screen scrapper to underlying Yahoo website, this uses React now, closes #16 and #17
    * fix function accessor
    * add doc
    * format code PSR2

- 1.0.4 (2016-10-16)
    * fixed type hinting in quote() if no $param Array is given 

- 1.0.3 (2016-10-06)
    * updated query url for symbolSuggest(), due to changes in Yahoo query string

- 1.0.2 (2015-09-28)
    * validate symbol and response in quotes() 

- 1.0.1 (2015-09-28)
    * validate symbol and response in historicalQuotes()
 
- 1.0.0 (2015-09-28)
    * fixed some broken method, partially with custom screen scrapper
    * reorganisation of classes, `YahooFinanceQuery.php` functions as a controller/router class, each method has its own underlying class
    * stil need to add further testing

- 0.4.0 (2014-06-24)

    * made PSR-4 package and added to packagist.org
    * started adding unit tests

- 0.3.1 (2014-03-13)

    * fixed returned array in `stockInfo()` to simple array

- 0.3.0 (2014-03-09)

    * added chainable methods
    * added optional direct query or query via yql console if possible
    * added function `get()`
    * added function `raw()`
    * added function `toJson()`
    * added function `intraDay()`
    * added unified UTC timestamp to `quote()`

- 0.2.3 (2014-01-08)

    * added function `stockInfo()`
    * added function `sectorList()`
    * changed function `index()` to `indexList()`

- 0.2.2 (2014-01-06)

    * added function `historicalQuote()`
    * added function `index()`
    * unified cURL request in separate method `curlRequest($url)`

- 0.2.1 (2014-01-03)

    * fixed bug in `quote()` for single or multipe query symbols

- 0.2.0 (2011-01-03)

    * added function `quote()`

- 0.1.0 (2013-12-30 Initial commit)

    * added function `autoSuggest()`
