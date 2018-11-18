<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('htag')) {
    
    function htag($tag, $content, $attrib = false) {
        $ret = "<{$tag} ";
        if ($attrib) {
            if (is_object($attrib)) { $attrib = (array)$attrib; }
            foreach($attrib as $att => $val) {
                if ($att == 'style') {
                    if (is_array($val)) {
                        $val = implode(';',$val);
                    }
                    $ret .= "{$att} = '{$val}' "; 
                } else {
                    $ret .= "{$att} = '{$val}' ";
                }
            }
        }
        $ret .= ">{$content}";        
        $ret .= "</{$tag}>";
        
        return $ret;
    }
    
}

if ( ! function_exists('button')) {

    function button($content, $attrib = false) {
        if (!$attrib) {
            $attrib=array();
        }
        $attrib["type"] = "button";
        return htag('BUTTON',$content,$attrib);
    }
    
}


if ( ! function_exists('span')) {

    function span($content, $attrib = false) {
        return htag('SPAN',$content,$attrib);
    }
    
}

if ( ! function_exists('div')) {

    function div($content, $attrib = false) {
        return htag('DIV',$content,$attrib);
    }
    
}
/*
if ( ! function_exists('table')) {

    function table($content, $attrib = false) {
        return htag('TABLE',$content,$attrib);
    }
    
}
if ( ! function_exists('tr')) {

    function tr($content, $attrib = false) {
        return htag('TR',$content,$attrib);
    }
    
}
if ( ! function_exists('td')) {

    function td($content, $attrib = false) {
        return htag('TD',$content,$attrib);
    }
    
}
*/


if ( ! function_exists('br')) {

    function br() {
        return '<br/>';
    }
    
}


if ( ! function_exists('hr')) {

    function hr() {
        return '<hr/>';
    }
    
}