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
 
<h2><a href="example.php">YahooFinanceQuery Example</a></h2>
<hr />
 
<div>
    <h4>function symbolSuggest($string);</h4>
    <p>
        Search for "basf"
        <pre><code>$data = $query->symbolSuggest('basf');</code></pre>
    </p>
    <form method="post" action="">
        <input type="text" name="symbolSuggest" value="basf" placeholder="basf"/>
        <input type="submit" name="searchSymbol" value="Search" />
    </form>
     
    <?php 
    if (isset($_POST['searchSymbol'])) {
        $data = $query->symbolSuggest($_POST['symbolSuggest']); ?>
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
    <h4>function index($symbol);</h4>
    <p>
        Get index components for "^GDAXI"
        <pre><code></code>$data = $query->index('^GDAXI');</code></pre>
    </p>
    <form method="post" action="">
        <input type="text" name="symbolIndex" value="^GDAXI"/>
        <input type="submit" name="searchIndex" value="Search" />
    </form>
     
    <?php 
    if (isset($_POST['searchIndex'])) {
        //strings to array
        $symbolIndex = explode(' ', $_POST['symbolIndex']);
        $data = $query->index($symbolIndex); ?>
    <table>
        <thead>
            <th>Symbol</th>
            <th>Name</th>
            <th>Last Price</th>
        </thead>
        <tbody>
        <?php foreach ($data as $dataEntry) { ?>
            <tr>
                <td><?php echo $dataEntry['Symbol']; ?></td>
                <td><?php echo $dataEntry['Name']; ?></td>
                <td><?php echo $dataEntry['LastPrice']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>
<hr />
 
<div>
    <h4>function quote($symbol, $params);</h4>
    <p>
        Get current quote for "bas.de", "aapl", "goog"
        <pre><code></code>$data = $query->quote(array('bas.de', 'aapl', 'goog'), array('LastTradePriceOnly', 'x', 'c1'));</code></pre>
    </p>
    <form method="post" action="">
        <input type="text" name="symbolQuote" value="bas.de aapl goog"/>
        <input type="text" name="searchParam" value="LastTradePriceOnly x c1"/>
        <input type="submit" name="searchQuote" value="Search" />
    </form>
     
    <?php 
    if (isset($_POST['searchQuote'])) {
        //strings to array
        $symbolQuote = explode(' ', $_POST['symbolQuote']);
        $searchParam = explode(' ', $_POST['searchParam']);
        $data = $query->quote($symbolQuote, $searchParam); ?>
    <table>
        <thead>
        <?php foreach ($data[0] as $dataKey => $dataEntry) { ?>
            <th><?php echo $dataKey; ?></th>
        <?php } ?>
        </thead>
        <tbody>
        <?php foreach ($data as $dataKey => $dataEntry) { ?>
            <tr>
            <?php foreach ($dataEntry as $dataEntryKey => $dataSet) {
                if (is_a($dataSet, 'DateTime')) { ?>
                    <td><?php echo $dataSet->format('Y-m-d H:i:s'); ?></td>
                <?php } else { ?>
                    <td><?php echo $dataSet; ?></td>
                <?php } ?>
            <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
     
</div>
 
</body>
</html>
