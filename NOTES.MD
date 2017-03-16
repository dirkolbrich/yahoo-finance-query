# Notes

Rewrite/Refactor of YahooFinanceQuery to unify returning data and integrate basic testing. 

## Return format for querys

The return format of the querys should follow the JSON API Specs, just as a php array.

on Success

```
{
    "ok"; true,
    "meta": [
        "status": 200,
        "query": "",
    ],
    "data": []
}
```

on Failure

```
{
    "ok": false,
    "meta": [
        "status": 404,
        "query": "..."
    ],
    "errors": [
        "status": 404,
        "title": "resource not found"
        "detail": ""
    ]
}
```

## SymbolSuggest

```
{
    "ok": true,
    "meta": [
        "status": 200,
        "query": "basf",
    ],
    "data": [{
        "type": "symbols",
        "id": 1,
        "attributes": {
            "symbol": "BAS.DE",
            "name": "BASF SE",
            "exch": "GER",
            "type": "S",
            "exchDisp": "XETRA",
            "typeDisp": "Equity"
        }        
    }, {
        "type": "symbols",
        "id": 2,
        "attributes": {
            "symbol": "BASFY",
            "name": "BASF SE",
            "exch": "PNK",
            "type": "S",
            "exchDisp": "OTC Markets",
            "typeDisp": "Equity"
        }
    }
        // any number of more results
    ]
}
```

```
{
    "ok": false,
    "meta": {
        "status": 404,
        "query": "mäuse"
    },
    "errors": {
        "status": 404,
        "title": "Resource not found",
        "detail": "Could not find a suggestion for mäuse"
    }
}
```


