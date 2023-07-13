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