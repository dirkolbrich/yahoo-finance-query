<!DOCTYPE html>
<html>
<head>
    <title>YahooFinanceQuery Example</title>
</head>
<body>
<?php
require 'YahooFinanceQuery.php'; 
$query = new YahooFinanceQuery\YahooFinanceQuery();
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
    
    <?php 
    if (isset($_POST['searchSymbol'])) {
        $data = $query->symbolSuggest($_POST['string']); ?>
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

<div>
    <h4>function quote($symbol, $params);</h4>
    <p>Get current quote for "bas.de"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de"/>
        <input type="text" name="param" value="LastTradePriceOnly x c1"/>
        <input type="submit" name="searchQuote" value="Search" />
    </form>
    
    <?php 
    if (isset($_POST['searchQuote'])) {
        //strings to array
        $symbol = explode(' ', $_POST['symbol']);
        $param = explode(' ', $_POST['param']);
        $data = $query->quote($symbol, $param); ?>
    <table>
        <thead>
        <?php foreach ($data[0] as $dataKey => $dataEntry) { ?>
            <th><?php echo $dataKey; ?></th>
        <?php } ?>
        </thead>
        <tbody>
        <?php foreach ($data as $dataKey => $dataEntry) { ?>
            <tr>
            <?php foreach ($dataEntry as $dataEntryKey => $dataSet) { ?>
                <td><?php echo $dataSet; ?></td>
            <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>
<hr />

<div>
    <h4>function historicalQuote($symbol, $startDate, $endDate);</h4>
    <p>Get historical quotes for "bas.de"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="bas.de"/>
        <input type="date" name="startDate" value="<?php echo date('Y-m-d', mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))); ?>"/>
        <input type="date" name="endDate" value="<?php echo date('Y-m-d'); ?>"/>
        <select>
            <option>daily</option>
            <option>weekly</option>
            <option>monthly</option>
            <option>dividends</option>
        </select>
        <input type="submit" name="searchHistoricalQuote" value="Search" />
    </form>

    <?php
    if (isset($_POST['searchHistoricalQuote'])) {
        //strings to array
        $data = $query->historicalQuote($_POST['symbol'], $_POST['startDate'], $_POST['endDate']); ?>
    <table>
        <thead>
        <?php foreach ($data[0] as $dataKey => $dataEntry) { ?>
            <th><?php echo $dataKey; ?></th>
        <?php } ?>
        </thead>
        <tbody>
        <?php foreach ($data as $dataKey => $dataEntry) { ?>
            <tr>
            <?php foreach ($dataEntry as $dataEntryKey => $dataSet) { ?>
                <td><?php echo $dataSet; ?></td>
            <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>
<hr />

<div>
    <h4>function index($symbol);</h4>
    <p>Get index components for "^GDAXI"</p>
    <form method="post" action="">
        <input type="text" name="symbol" value="^GDAXI"/>
        <input type="submit" name="searchIndex" value="Search" />
    </form>
    
    <?php 
    if (isset($_POST['searchIndex'])) {
        //strings to array
        $symbol = explode(' ', $_POST['symbol']);
        $data = $query->index($symbol); ?>
    <table>
        <thead>
            <th>Symbol</th>
            <th>Name</th>
        </thead>
        <?php foreach ($data as $dataEntry) { ?>
        <tbody>
            <?php foreach($dataEntry as $component) { ?>
            <tr>
                <td><?php echo $component[0]; ?></td>
                <td><?php echo $component[1]; ?></td>
            </tr>
            <?php } ?>
        </tbody>
        <?php } ?>
    </table>
    <?php } ?>
</div>

</body>
</html>
