<!DOCTYPE html>
<!-- 
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
 -->
<html>
<head>
    <title>YahooFinanceQuery Example</title>
</head>
<body>
<?php
require './src/YahooFinanceQuery.php';

use DirkOlbrich\YahooFinanceQuery\YahooFinanceQuery;

$query = new YahooFinanceQuery;
?>

<h2>YahooFinanceQuery Example</h2>
<hr />

<!-- symbolSuggest($string); -->
<div>
    <h4>function symbolSuggest($string);</h4>
    <p>Search for "basf"</p>
    <form method="post" action="">
        <input type="text" name="string" value="basf" placeholder="basf"/>
        <input type="submit" name="searchSymbol" value="Search" />
    </form>

    <?php
    if (isset($_POST['searchSymbol'])) {
        $data = $query->symbolSuggest($_POST['string'])->get(); ?>
    <table>
        <thead>
            <th>Symbol</th>
            <th>Name</th>
            <th>Exchange</th>
            <th>Exchange Display</th>
            <th>Type</th>
            <th>Type Display</th>
        </thead>
        <tbody>
        <?php foreach ($data as $dataEntry) { ?>
            <tr>
                <td><?php echo $dataEntry['symbol']; ?></td>
                <td><?php echo $dataEntry['name']; ?></td>
                <td><?php echo $dataEntry['exch']; ?></td>
                <td><?php echo $dataEntry['exchDisp']; ?></td>
                <td><?php echo $dataEntry['type']; ?></td>
                <td><?php echo $dataEntry['typeDisp']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>
<hr />

<!-- quote($symbol[, $params]); -->
<div>
    <h4>function quote($symbol[, $params]);</h4>
    <p>Get current quote for "bas.de", "sdf.de", "aapl"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de sdf.de aapl"/>
        <input type="text" name="param" value="LastTradePriceOnly x c1"/>
        <input type="submit" name="getQuote" value="Search" />
        <input type="checkbox" name="getQuoteYQL" /><label for="getQuoteYQL">via YQL</label>
    </form>

    <?php
    if (isset($_POST['getQuote'])) {
        //strings to array
        $symbol = explode(' ', $_POST['symbol']);
        $param = explode(' ', $_POST['param']);
        if (isset($_POST['getQuoteYQL'])) {
            echo '<p>Query via YQL console.</p>';
            $data = $query->yql()->quote($symbol, $param)->get();
        } else {
            $data = $query->quote($symbol, $param)->get();
            echo '<p>Direct query via csv.</p>';
        }
    ?>
        <?php if (!empty($data)) { ?>
        <table>
            <thead>
            <?php foreach ($data[0] as $dataKey => $dataEntry) { ?>
                <th><?php echo $dataKey; ?></th>
            <?php } ?>
            </thead>
            <tbody>
            <?php foreach ($data as $dataSet) { ?>
                <tr>
                <?php foreach ($dataSet as $key => $value) { ?>
                    <td><?php echo $value; ?></td>
                <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
        <p>No Data found.</p>
        <?php } ?>        
    <?php } ?>
</div>
<hr />

<!-- historicalQuote($symbol[, $startDate, $endDate, $param); -->
<div>
    <h4>function historicalQuote($symbol[, $startDate, $endDate, $param);</h4>
    <p>Get historical quotes for "bas.de"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de"/>
        <input type="date" name="startDate" value="<?php echo date('Y-m-d', mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))); ?>"/>
        <input type="date" name="endDate" value="<?php echo date('Y-m-d'); ?>"/>
        <select name="param">
            <option value="d" selected="selected">daily</option>
            <option value="w">weekly</option>
            <option value="m">monthly</option>
            <option value="v">dividends</option>
        </select>
        <input type="submit" name="getHistQuote" value="Search" />
        <input type="checkbox" name="getHistQuoteYQL" /><label for="getHistQuoteYQL">via YQL</label>
    </form>

    <?php
    if (isset($_POST['getHistQuote'])) {
        if (isset($_POST['getHistQuoteYQL'])) {
            $data = $query->yql()->historicalQuote($_POST['symbol'], $_POST['startDate'], $_POST['endDate'], $_POST['param'])->get();
            echo '<p>Query via YQL console. Datasets: <b>' . count($data) . '</b></p>';
        } else {
            $data = $query->historicalQuote($_POST['symbol'], $_POST['startDate'], $_POST['endDate'], $_POST['param'])->get();
            echo '<p>Direct query via csv. Datasets: <b>' . count($data) . '</b></p>';
        }
    ?>
        <?php if (!empty($data)) { ?>
            <table>
                <thead>
                    <?php foreach ($data[0] as $dataKey => $dataEntry) { ?>
                        <th><?php echo $dataKey; ?></th>
                    <?php } ?>
                </thead>
                <tbody>
                <?php foreach ($data as $dataKey => $dataEntry) { ?>
                    <tr>
                    <?php foreach ($dataEntry as $key => $dataSet) { ?>
                        <td><?php echo $dataSet; ?></td>
                    <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
        <p>No Data found.</p>
        <?php } ?>
    <?php } ?>
</div>
<hr />

<!-- intraDay($symbol[, $period, $param]); -->
<div>
    <h4>function intraDay($symbol[, $period, $param]);</h4>
    <p>Get intraday quotes for "bas.de"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de"/>
        <select name="period">
            <option value="1d" selected="selected">1 day</option>
            <option value="5d">5 days</option>
            <option value="10d">10 days</option>
            <option value="15d">15 days</option>
        </select>
        <select name="param">
            <option value="quote" selected="selected">quote</option>
            <option value="sma">sma</option>
            <option value="close">close</option>
            <option value="volume">volume</option>
        </select>
        <input type="submit" name="getIntraDay" value="Search" />
        <!--
        <input type="checkbox" name="getIntraDayYQL" /><label for="getIntraDayYQL">via YQL</label>
        -->
    </form>

    <?php
    if (isset($_POST['getIntraDay'])) {
        //strings to array
        $symbol = $_POST['symbol'];
        $period = $_POST['period'];
        $param = $_POST['param'];
        echo '<p>Direct query via csv.</p>';
        $data = $query->intraDay($symbol, $period, $param)->get();
    ?>
    <table>
        <thead>
        <?php foreach ($data[0] as $dataKey => $dataEntry) { ?>
            <th><?php echo $dataKey; ?></th>
        <?php } ?>
        </thead>
        <tbody>
        <?php foreach ($data as $dataKey => $dataEntry) { ?>
            <tr>
            <?php foreach ($dataEntry as $key => $dataSet) { ?>
                <td><?php echo $dataSet; ?></td>
            <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>
<hr />

<!-- stockInfo($symbol); -->
<div>
    <h4>function stockInfo($symbol);</h4>
    <p>Get stock info for "bas.de"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de"/>
        <input type="submit" name="getStockInfo" value="Search" />
    </form>

    <?php
    if (isset($_POST['getStockInfo'])) {
        //strings to array
        //$symbol = explode(' ', $_POST['symbol']);
        $data = $query->stockInfo($_POST['symbol'])->get(); ?>
    <table>
        <tbody>
        <?php foreach ($data as $key => $val) { ?>
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo $val; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>

<hr />

<!-- index($symbol); -->
<div>
    <h4>function index($symbol);</h4>
    <p>Get index components for "^GDAXI"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="^GDAXI"/>
        <input type="submit" name="getIndex" value="Search" />
    </form>

    <?php
    if (isset($_POST['getIndex'])) {
        //strings to array
        $symbol = explode(' ', $_POST['symbol']);
        $data = $query->indexList($symbol)->get();
            foreach ($data as $index) { ?>
                <p><?php echo $index['symbol']; ?></p>
                <table>
                    <thead>
                        <th>#</th>
                        <th>Symbol</th>
                        <th>Name</th>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach($index['components'] as $component) { ?>
                        <tr>
                            <td><?php echo $i; $i++; ?></td>
                            <?php foreach($component as $val) { ?>
                                <td><?php echo $val; ?></td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        <?php } ?>
</div>
<hr />

<!-- sectors(); -->
<div>
    <h4>function sectors();</h4>
    <p>Get full list of sectors with corresponding industries</p>
    <form method="post" action="">
        <input type="submit" name="getSectors" value="Get Sectors" />
    </form>

    <?php
    if (isset($_POST['getSectors'])) {
        $data = $query->sectorList()->get();
    ?>
        <p>
            <?php foreach ($data as $sector) { ?>
                <h4><?php echo $sector['name']; ?></h4>
                <table>
                    <thead>
                        <th>ID</th>
                        <th>Name</th>
                    </thead>
                    <tbody>
                    <?php foreach ($sector['industries'] as $industry) { ?>
                        <tr>
                            <td><?php echo $industry['id']; ?></td>
                            <td><?php echo $industry['name']; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </p>
    <?php } ?>
</div>

</body>
</html>
