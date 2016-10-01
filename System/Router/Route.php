<?php
namespace Ant\Router;

use BadFunctionCallException;
use Ant\Interfaces\Router\RouteInterface;

class Route implements RouteInterface
{
    use ParseGroupAttributes;
    /**
     * Http 请求
     *
     * @var array
     */
    protected $method;

    /**
     * 请求资源
     *
     * @var string
     */
    protected $uri;

    /**
     * 回调的函数
     *
     * @var callable
     */
    protected $callback;

    /**
     * 路由需要的中间件
     *
     * @var array
     */
    protected $middleware;

    /**
     * Route constructor.
     * @param $method
     * @param $uri
     * @param $action
     * @param array $groupAttributes
     */
    public function __construct($method,$uri,$action,array $groupAttributes = [])
    {
        $action = $this->parseAction($action);
        $this->groupAttributes = $groupAttributes;

        if(isset($groupAttributes)){
            //继承路由组信息
            $uri = $this->mergeGroupPrefixAndSuffix($uri);

            $action = $this->mergeGroupNamespace(
                $this->mergeMiddlewareGroup($action)
            );
        }

        //获取路由映射的回调函数
        if(!isset($action['use'])){
            foreach($action as $value){
                if($value instanceof \Closure){
                    $action['use'] = $value;
                    break;
                }

                throw new BadFunctionCallException('Routing callback failed');
            }
        }

        $this->method = $method;
        $this->uri = '/'.trim($uri,'/');
        $this->callback = $action['use'];
        $this->middleware = isset($action['middleware']) ? $action['middleware'] : [];
    }
    
    /**
     * 解析行为
     *
     * @param $action
     * @return array
     */
    protected function parseAction($action)
    {
        if(is_string($action)){
            return ['uses' => $action];
        }elseif(!is_array($action)){
            return [$action];
        }

        if(isset($action['middleware']) && is_string($action['middleware'])){
            $action['middleware'] = explode('|',$action['middleware']);
        }

        return $action;
    }

    /**
     * 获取请求方式
     *
     * @return array
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 获取路由Uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * 获取行为
     *
     * @return $this
     */
    public function getAction()
    {
        return $this;
    }

    /**
     * 获取路由回调
     *
     * @return array
     */
    public function getCallable()
    {
        return $this->callback;
    }

    /**
     * 获取路由需要的中间件
     *
     * @return mixed
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}