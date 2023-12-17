<?php

namespace OceanWT\Http;

class Request
{   
    use \OceanWT\Support\Traits\Macro;
    /**
     * @return string
     */
    public static function method()
    {
     return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return boolean
     */
    public static function isGet()
    {
      return self::method()=='GET';
    }
    
    /**
     * @return boolean
     */
    public static function isPost()
    {
      return self::method()=='POST';
    }

    /**
     * @return string
     */
    public static function getUrl()
    {
        $dirname = dirname($_SERVER['SCRIPT_NAME']);
        $dirname = $dirname != '/' ? $dirname : '';
        $basename = basename($_SERVER['SCRIPT_NAME']);
        $path = $_SERVER['REQUEST_URI'];
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return str_replace([$dirname, $basename], '', $path);
    }

    /**
     * @param  string|int  $data
     * @param  boolean $st
     */
    public static function security($data, $st = false)
    {
        if($st) {
            return strip_tags($data);
        } else {
            return $data;
        }
        return $data;
    }
    
    /**
     * @param  int          $data
     * @param  bool|boolean $st
     */
    public static function get(string|int $data,bool $st = false)
    {
     return self::security($_GET[$data],$st);
    }
    
    /**
     * @param  int          $data
     * @param  bool|boolean $st  
     */
    public static function post(string|int $data,bool $st = false)
    {
     return self::security($_POST[$data],$st);
    }
}
