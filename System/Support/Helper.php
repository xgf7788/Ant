<?php

use Ant\App;
use Ant\Support\ArrayHandle;

/**
 * 打印信息
 */
function debug()
{
    echo "<pre>";
    var_dump(...func_get_args());
    echo "</pre>";
    die;
}

function ArraySetIn(&$array,$path,$value)
{
    if(is_string($path)){
        $path = explode('.',$path);
    }

    ArrayHandle::setIn($array,$path,$value);
}

function container($serviceName = null, $parameters = [])
{
    if (is_null($serviceName)) {
        return App::getInstance();
    }

    return App::getInstance()->make($serviceName, $parameters);
}

function get($key = null)
{
    return container('request')->get($key);
}

function post($key = null)
{
    return container('request')->post($key);
}

function body($key = null)
{
    return container('request')->getBodyParam($key);
}

function cookie($key = null)
{
    return container('request')->cookie($key);
}

/**
 * 保证json编码不会出错
 * @param $value
 * @param int $options
 * @param int $depth
 * @return string
 */
function safe_json_encode($value, $options = 0, $depth = 512)
{
    $value = json_encode($value, $options, $depth);

    if ($value === false && json_last_error() !== JSON_ERROR_NONE) {
        throw new \UnexpectedValueException(json_last_error_msg(), json_last_error());
    }

    return $value;
}

/**
 * 保证json解码不会出错
 * @param $json
 * @param bool|false $assoc
 * @param int $depth
 * @param int $options
 * @return mixed
 */
function safe_json_decode($json, $assoc = false, $depth = 512, $options = 0)
{
    $value = json_decode($json, $assoc, $depth, $options);

    if ($value === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new \UnexpectedValueException(json_last_error_msg(), json_last_error());
    }

    return $value;
}

/**
 * 检查指定字符串是否包含另一个字符串
 * @param $haystack
 * @param $needles
 * @return bool
 */
function contains($haystack, $needles)
{
    foreach ((array) $needles as $needle) {
        if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function handleError($exception,Ant\Http\Request $request,Ant\Http\Response $response)
{
    if ($exception instanceof Ant\Exception\HttpException) {
        // 获取HTTP状态码
        $status = $exception->getStatusCode();
        // 将头信息写入响应头
        foreach($exception->getHeaders() as $name => $value){
            $response->withAddedHeader($name,$value);
        }
    } else {
        $status = 500;
    }
    $response->withStatus($status);

    foreach (exceptionHandle($exception) as $key => $value) {
        $response->write($value.'<br />');
    }

    return $response;
}

/**
 * 获取错误信息
 * @param $exception
 * @return array
 */
function exceptionHandle($exception)
{
    if($exception->getPrevious()){
        return exceptionHandle($exception->getPrevious());
    }

    $exceptionInfo = [];
    $exceptionInfo['Exception'] = sprintf(
        "{$exception->getFile()}({$exception->getLine()}) %s %s",get_class($exception),$exception->getMessage()
    );

    foreach(explode("\n",$exception->getTraceAsString()) as $index => $line){
        $key = sprintf('X-Exception-Trace-%02d', $index);
        $exceptionInfo[$key] = $line;
    }

    return $exceptionInfo;
}