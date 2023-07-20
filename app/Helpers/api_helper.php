<?php


if (! function_exists('succesResponse'))
{
function succesResponse($response)
{
    return [
        'status' => 200,
        'code' => "success",
        'response' => $response
    ];
}
}

if (! function_exists('failResponse'))
{
function failResponse($response)
{
    return [
        'status' => 200,
        'code' => "error",
        'response' => $response
    ];
}
}

if (! function_exists('validateTokenAndFetchData')){
function validateTokenAndFetchData()
{
    // Validate the provided token against the private token set in your environment variables
    $token = service('request')->getHeaderLine('YBO-Token');
    if ($token !== getenv('PRIVATE_TOKEN')) {
        throw new \Exception('Invalid token');
    }

    // Fetch the JSON data from the request and validate the presence of the 'id' field
    $requestData = service('request')->getJSON(true);

    return $requestData;
}}