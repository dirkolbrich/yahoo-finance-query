# return format for querys

## SymbolSuggest


{
    "ok": true,
    "status": 200,
    "query": "basf",
    "symbols": [
        {
            "symbol": "BAS.DE",
            "name": "BASF SE",
            "exch": "GER",
            "type": "S",
            "exchDisp": "XETRA",
            "typeDisp": "Equity"
        },
        {
            "symbol": "BASFY",
            "name": "BASF SE",
            "exch": "PNK",
            "type": "S",
            "exchDisp": "OTC Markets",
            "typeDisp": "Equity"
        }
        // any number of more results
    ]
}

{
    "ok": false,
    "status": 404,
    "query": "mäuse",
    "error": "Could not find a suggestion for mäuse"
}