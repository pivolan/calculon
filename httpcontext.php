<?php
/**
 * Created by JetBrains PhpStorm.
 * User: HuzZzo
 * Date: 02.04.12
 * Time: 22:50
 * To change this template use File | Settings | File Templates.
 */
class httpcontext
{
    public static function getHttpRequest()
    {
        return $_REQUEST['InputBox'];
    }

    public static function postHttpResponse($arr)
    {
        echo json_encode($arr);
    }
}
