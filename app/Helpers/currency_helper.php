<?php

if (! function_exists('getCurrencyRate')) {
    function getCurrencyRate($currencyCode)
    {
        $xml = simplexml_load_file('https://www.bnr.ro/nbrfxrates.xml');

        // Namespace required to properly parse the XML
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('d', $namespaces['']);

        // XPath query to directly access the 'Rate' elements
        $rates = $xml->xpath("//d:Rate");

        foreach ($rates as $rate) {
            if ((string)$rate['currency'] === strtoupper($currencyCode)) {
                return (float)$rate;
            }
        }

        // Return null if the currency was not found in the XML
        return null;
    }
}