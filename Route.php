<?php

namespace OceanWT\Http;

class Route
{ 
    use \OceanWT\Traits\Macro;
    
    /**
     * @var string
     */
    public static $mode;

    /**
     * @var string
     */
    public static $name;

    /**
     * @var array
     */
    public static $routes = [];

    /**
     * @var string
     */
    public static $prefix;
    
    /**
     * @var string|array
     */
    public static $method;

    /**
     * @var string
     */
    public static $namespace=GET_NAMESPACES['CONTROLLERS'];

    /**
     * @var string
     */
    public static $controller;

    /**
     * @var array
     */
    public static $patterns = [
     '{:id[0-9]?}' => '([0-9]+)',
     '{:url[0-9]?}' => '([a-z]+)',
    ];

    /**
     * @param  string $uri
     * @param  array|string|callable|null  $action
     * @return  \OceanWT\Route
     */
    public static function get($uri, $action = null){
        self::match(["GET","HEAD"], $uri, $action);
        return new self();
    }

    /**
     * @param  string $uri
     * @param  array|string|callable|null  $action
     * @return  \OceanWT\Route
     */
    public static function post($uri, $action = null){
        self::match("POST", $uri, $action);
        return new self();
    }

    /**
     * @param  string $namespace
     * @return \OceanWT\Route
     */
    public static function _namespace($namespace){
        self::$namespace = $namespace;
        return new self();
    }

    /**
     * @param  string $prefix
     * @return \OceanWT\Route
     */
    public static function prefix($prefix){
        self::$prefix = $prefix;
        return new self();
    }

    /**
     * @return \OceanWT\Route
     */
    public static function group(\Closure $group){
        $group();
        self::$name = '';
        self::$prefix = '';
        return new self();
    }

    /**
     * @param  string $pattern
     */
    public static function createPattern($pattern){
        foreach(self::$patterns as$key => $value) {
            return preg_replace('#'.$key.'#', $value, $pattern);
        }
    }

    /**
     * @param string|array $method
     * @param string $uri
     * @param array|string|null|callable $action
     */
    public static function match($method, $uri, $action){   
        $controller=self::$controller;
        if(is_array($method)){
         foreach($method as$m){
          self::$method=$method;
          self::$routes[self::$mode][$m][self::createPattern(self::$prefix.$uri)] = [
           'action' => (in_array(gettype($controller),["object","string"],true) ? $controller."::".$action : $action),
           'namespace' => self::$namespace,
           'name'=>self::$name,
          ];
         }
        }else{
         self::$method=$method;
         self::$routes[self::$mode][$method][self::createPattern(self::$prefix.$uri)] = [
           'action' => (in_array(gettype($controller),["object","string"],true) ? $controller."::".$action : $action),
           'namespace' => self::$namespace,
           'name'=>self::$name,
        ];
        }
    }
   
   /**
    * @param  string|object|array $controller
    */
   public static function controller($controller){
    self::$controller=$controller;
    return new self;
   }

   /**
    * @param  string $mode
    */
   public static function mode($mode){
    self::$mode=$mode;
    return new self;
   }
   
   /**
    * @param  string $key
    */
   public static function name($key){
   $rkey=array_key_last(self::$routes[self::$mode]["GET"]);
    self::$routes[self::$mode]["GET"][$rkey]['name']=$key;
    return new self;
   }

   /**
    * @param  string $name
    * @param  array  $params
    */
   public static function url($name,$params=[]){
    $route=array_key_first(array_filter(self::$routes[self::$mode]["GET"], function ($route) use ($name){
        return isset($route['name']) && $route['name'] === $name;
    }));
    return public_url().$route;
   }
   
    /**
     * @return void
     */
    public static function run(){
        $url = Request::security(Request::getUrl(), true);
        foreach(self::$routes[self::$mode][$_SERVER['REQUEST_METHOD']]as$path => $props) {
            $pattern = '#^'.$path.'$#';
            if(preg_match($pattern, $url, $params)) {
                array_shift($params);
                $action = $props['action'];
                if(is_callable($action)) {
                    echo call_user_func_array($action, $params);
                } elseif(is_array($action)) {
                    $className = $action[0];
                    $class = (new ((isset($props['namespace']) ? $props['namespace'] : '').$className));
                    echo call_user_func_array([$class,$action[1]], $params);
                } elseif(is_string($action)) {
                    $className = explode("::", $action)[0];
                    $class = (new ((isset($props['namespace']) ? $props['namespace'] : '').$className));
                    $method = explode("::", $action)[1];
                    echo call_user_func_array([$class,$method], $params);
                }
            }
        }
    }

}
