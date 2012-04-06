<?php
/**
 * Created by JetBrains PhpStorm.
 * User: HuzZzo
 * Date: 14.03.12
 * Time: 22:18
 * To change this template use File | Settings | File Templates.
 */
include 'autoload.php';
$userData= new httpcontext();
$calc = new calculus();
$result='';
$errMsg='';
try
{
    $result =  $calc->getAnswer($userData->getHttpRequest());
    $status='OK';
}
catch(Exception $e)
{
    $errMsg=$e->getMessage();
    $status='ERR';
}
$userData->postHttpResponse(array('resp' =>$result,'status'=>$status,'errmsg'=>$errMsg));
?>