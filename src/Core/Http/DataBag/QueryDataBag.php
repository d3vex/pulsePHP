<?php

namespace D3vex\Pulsephp\Core\Http\DataBag;


use D3vex\Pulsephp\Core\Utils\DataBag;

class QueryDataBag extends DataBag
{
    public static function fromUri(?string $queryString): QueryDataBag
    {
        if (empty($queryString)) {
            return new QueryDataBag();
        }
        $queryBag = new QueryDataBag();
        parse_str($queryString, $queryParameters);
        $queryBag->init($queryParameters);
        return $queryBag;
    }
}
