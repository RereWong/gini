<?php

namespace Model {

    class URI {

        static function url($url=null, $query=null, $fragment=null) {
            
            if (!$url) $url = CGI::route();
        
            $ui = parse_url($url);
        
            if($ui['scheme']=='mailto') {
                //邮件地址
                return 'mailto:'.$ui['user'].'@'.$ui['host'];
            }
        
            if ($query) {
                if ($ui['query']) {
                    if(is_string($query))parse_str($query, $query);
                    parse_str($ui['query'], $old_query);
                    $ui['query']=http_build_query(array_merge($old_query, $query));
                } else {
                    $ui['query']=is_string($query)?$query:http_build_query($query);
                }
            }
            
            if ($fragment) $ui['fragment']=$fragment;
        
            if ($ui['host']) {
                $url = $ui['scheme'] ?: 'http';
                $url.='://';
                if($ui['user']){
                    if($ui['pass']){
                        $url.=$ui['user'].':'.$ui['pass'].'@';
                    }else{
                        $url.=$ui['user'].'@';
                    }
                }
                $url.=$ui['host'];
                if($ui['port'])$url.=':'.$ui['port'];
                $url .= '/';        
            }
            else {
                $url = self::base();
            }
            
            if($ui['path']){
                $url.=ltrim($ui['path'], '/');
            }
            
            if($ui['query']){
                $url.='?'.$ui['query'];
            }
            
            if($ui['fragment']){
                $url.='#'.$ui['fragment'];
            }
            
            return $url;
        }

        static function encode($text) {
            return rawurlencode(strtr($text, array('.'=>'\.', '/'=>'\/')));
        }
        
        static function decode($text) {
            return strtr($text, array('\.'=>'.', '\/'=>'/'));
        }
        
        static function anchor($url, $text = null, $extra=null, $options=array()) {
            if ($extra) $extra = ' '.$extra;
            if (!$text) $text = $url;
            $url = URI::url($url, $options['query'], $options['fragment']);
            return '<a href="'.$url.'"'.$extra.'>'.$text.'</a>';
        }
        
        static function mailto($mail, $name=null, $extra=null) {
            if (!$name) $name = $mail;
            if ($extra) $extra = ' '.$extra;
            return '<a href="mailto:'.$mail.'"'.$extra.'>'.$name.'</a>';
        }

        protected static $_base, $_rurl;
        static function setup() {
            $host = $_SERVER['HTTP_HOST'];
            $scheme = $_SERVER['HTTPS'] ? 'https':'http';
            $dir = dirname($_SERVER['SCRIPT_NAME']);
            if (substr($dir, -1) != '/') $dir .= '/';
            self::$_base = $scheme.'://'.$host.$dir;     
            
            self::$_rurl = \Gini\Core::path_info(APP_SHORTNAME)->rurl;               
        }

        static function base() {
            return self::$_base;
        }
        
        static function rurl($path, $type) {
            $base = self::$_rurl[$type] ?: (self::$_rurl['*'] ?: '');
            if (substr($base, -1) != '/') $base .= '/';
            return $base . $path;
        }
            
    }

}

namespace {

    function URL() {
        $args = func_get_args();
        return call_user_func_array('\\Model\\URI::url', $args);
    }

    function MAILTO() {
        $args = func_get_args();
        return call_user_func_array('\\Model\\URI::mailto', $args);
    }

    function RURL($path, $type) {
        return \Model\URI::rurl($path, $type);
    }
    
}
