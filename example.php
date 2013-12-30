<!DOCTYPE html>
<html>
<head>
    <title>YahooFinanceQuery Example</title>
</head>
<body>
<?php
require 'YahooFinanceQuery.php'; 
$query = new YahooFinanceQuery\YahooFinanceQuery;
?>

<h2>YahooFinanceQuery Example</h2>
<hr />

<div>
    <h4>function symbolSuggest($searchString);</h4>
    <p>
        Search for "basf"
        <pre><code></code>$data = $query->symbolSuggest('basf');</code></pre>
    </p>
    <?php $data = $query->symbolSuggest('basf'); ?>
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
</div>
<hr />

<div>
    <h4>function currentQuote($symbol);</h4>
    <p>
        Get current quote for "bas.de"
        <pre><code></code>$data = $query->currentQuote('bas.de');</code></pre>
    </p>

</div>

</body>
</html>