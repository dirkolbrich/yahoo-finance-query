<!DOCTYPE html>
<html>
<head>
    <title>YahooFinanceQuery Example</title>
</head>
<body>
<?
require 'YahooFinanceQuery.php';
$query = DirkOlbrich\YahooFinanceQuery\YahooFinanceQuery::make();
?>

<h2>YahooFinanceQuery Example</h2>
<hr />

<div>
    <h4>function symbolSuggest($string);</h4>
    <p>Search for "basf"</p>
    <form method="post" action="">
        <input type="text" name="string" value="basf" placeholder="basf"/>
        <input type="submit" name="searchSymbol" value="Search" />
    </form>

    <?
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
        <? foreach ($data as $dataEntry) { ?>
            <tr>
                <td><?=$dataEntry['symbol']; ?></td>
                <td><?=$dataEntry['name']; ?></td>
                <td><?=$dataEntry['exch']; ?></td>
                <td><?=$dataEntry['exchDisp']; ?></td>
                <td><?=$dataEntry['type']; ?></td>
                <td><?=$dataEntry['typeDisp']; ?></td>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <? } ?>
</div>
<hr />

<div>
    <h4>function quote($symbol[, $params]);</h4>
    <p>Get current quote for "bas.de", "sdf.de", "aapl"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de sdf.de aapl"/>
        <input type="text" name="param" value="LastTradePriceOnly x c1"/>
        <input type="submit" name="getQuote" value="Search" />
        <input type="checkbox" name="getQuoteYQL" /><label for="getQuoteYQL">via YQL</label>
    </form>

    <?
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
    <table>
        <thead>
        <? foreach ($data[0] as $dataKey => $dataEntry) { ?>
            <th><?=$dataKey; ?></th>
        <? } ?>
        </thead>
        <tbody>
        <? foreach ($data as $dataKey => $dataEntry) { ?>
            <tr>
            <? foreach ($dataEntry as $key => $dataSet) { ?>
                <td><?=$dataSet; ?></td>
            <? } ?>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <? } ?>
</div>
<hr />

<div>
    <h4>function historicalQuote($symbol[, $startDate, $endDate, $param);</h4>
    <p>Get historical quotes for "bas.de"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de"/>
        <input type="date" name="startDate" value="<?=date('Y-m-d', mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))); ?>"/>
        <input type="date" name="endDate" value="<?=date('Y-m-d'); ?>"/>
        <select name="param">
            <option value="d" selected="selected">daily</option>
            <option value="w">weekly</option>
            <option value="m">monthly</option>
            <option value="v">dividends</option>
        </select>
        <input type="submit" name="getHistQuote" value="Search" />
        <input type="checkbox" name="getHistQuoteYQL" /><label for="getHistQuoteYQL">via YQL</label>
    </form>

    <?
    if (isset($_POST['getHistQuote'])) {
        if (isset($_POST['getHistQuoteYQL'])) {
            $data = $query->yql()->historicalQuote($_POST['symbol'], $_POST['startDate'], $_POST['endDate'], $_POST['param'])->get();
            echo '<p>Query via YQL console. Datasets: <b>' . count($data) . '</b></p>';
        } else {
            $data = $query->historicalQuote($_POST['symbol'], $_POST['startDate'], $_POST['endDate'], $_POST['param'])->get();
            echo '<p>Direct query via csv. Datasets: <b>' . count($data) . '</b></p>';
        }
    ?>
    <table>
        <thead>
        <? foreach ($data[0] as $dataKey => $dataEntry) { ?>
            <th><?=$dataKey; ?></th>
        <? } ?>
        </thead>
        <tbody>
        <? foreach ($data as $dataKey => $dataEntry) { ?>
            <tr>
            <? foreach ($dataEntry as $key => $dataSet) { ?>
                <td><?=$dataSet; ?></td>
            <? } ?>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <? } ?>
</div>
<hr />

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

    <?
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
        <? foreach ($data[0] as $dataKey => $dataEntry) { ?>
            <th><?=$dataKey; ?></th>
        <? } ?>
        </thead>
        <tbody>
        <? foreach ($data as $dataKey => $dataEntry) { ?>
            <tr>
            <? foreach ($dataEntry as $key => $dataSet) { ?>
                <td><?=$dataSet; ?></td>
            <? } ?>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <? } ?>
</div>
<hr />

<div>
    <h4>function stockInfo($symbol);</h4>
    <p>Get stock info for "bas.de"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de"/>
        <input type="submit" name="getStockInfo" value="Search" />
    </form>

    <?
    if (isset($_POST['getStockInfo'])) {
        //strings to array
        //$symbol = explode(' ', $_POST['symbol']);
        $data = $query->stockInfo($_POST['symbol'])->get(); ?>
    <table>
        <tbody>
        <? foreach ($data as $key => $val) { ?>
            <tr>
                <td><?=$key; ?></td>
                <td><?=$val; ?></td>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <? } ?>
</div>

<hr />

<div>
    <h4>function index($symbol);</h4>
    <p>Get index components for "^GDAXI"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="^GDAXI"/>
        <input type="submit" name="getIndex" value="Search" />
    </form>

    <?
    if (isset($_POST['getIndex'])) {
        //strings to array
        $symbol = explode(' ', $_POST['symbol']);
        $data = $query->indexList($symbol)->get(); ?>
        <? foreach ($data as $key => $index) { ?>
        <p><?=$key ?></p>
        <table>
            <thead>
                <th>Symbol</th>
                <th>Name</th>
                <th>Market</th>
            </thead>
            <tbody>
                <? foreach($index as $component) { ?>
                <tr>
                    <? foreach($component as $val) { ?>
                        <td><?=$val ?></td>
                    <? } ?>
                </tr>
                <? } ?>
            </tbody>
        </table>
        <? } ?>
    <? } ?>
</div>
<hr />

<div>
    <h4>function sectors();</h4>
    <p>Get full list of sectors with corresponding industries</p>
    <form method="post" action="">
        <input type="submit" name="getSectors" value="Get Sectors" />
    </form>

    <?
    if (isset($_POST['getSectors'])) {
        $data = $query->sectorList()->get(); ?>
    <p>
        <? foreach ($data as $sector) { ?>
        <h4><?=$sector['name']; ?></h4>
        <table>
            <thead>
                <th>ID</th>
                <th>Name</th>
            </thead>
            <tbody>
            <? foreach ($sector['industry'] as $industry) { ?>
                <tr>
                <? foreach ($industry as $value) { ?>
                    <td><?=$value; ?></td>
                <? } ?>
                </tr>
            <? } ?>
            </tbody>
        </table>
        <? } ?>
    </p>

    <? } ?>
</div>
<hr />

</body>
</html>
