<?php


if (! function_exists('getCurrencyRate')){
function getCurrencyRate($currencyCode)
{
    $xml = simplexml_load_file('https://www.bnr.ro/nbrfxrates.xml');
    $namespaces = $xml->getNamespaces(true);
    $body = $xml->children($namespaces['Body']);
    $cube = $body->Cube;

    foreach ($cube->children() as $rate) {
        if ((string)$rate['currency'] === strtoupper($currencyCode)) {
            return (float)$rate;
        }
    }

    // Return null if the currency was not found in the XML
    return null;
}}