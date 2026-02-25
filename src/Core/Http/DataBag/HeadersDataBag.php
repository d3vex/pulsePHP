<?php

namespace D3vex\Pulsephp\Core\Http\DataBag;


use D3vex\Pulsephp\Core\Utils\DataBag;

class HeadersDataBag extends DataBag
{
    public static function fromGlobals(): HeadersDataBag
    {
        $headersBag = new HeadersDataBag();
        $headers = getallheaders();
        if (count($headers) > 0) {
            $headersBag->init($headers);
            return $headersBag;
        }

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = substr($key, 5);
                $headers[$headerName] = $value;
            }
        }
        $headersBag->init($headers);
        return $headersBag;
    }
}
