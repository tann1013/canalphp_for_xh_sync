<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-11-11
 * @version 1.0
 */

//对于用户输入一串字符串$string,要求$string中只能包含
//大于0的数字和英文逗号，
//
//请用正则表达式验证，对于不符合要求的$string返回出错信息

//function _isOk($str){
//
//    $reg = '/\d+\,/';
//    $res = preg_match($reg, $str);
//    if($res){
//        return true;
//    }else{
//        return false;
//    }
//}

//2 如何判断一个中英混合字符串中有多少字符
//utf8
//str=你好22ab
//总统计字节长

//3 一台服务器的使用nginx+php提供服务，
/**
 * 服务器为4核8G，
   对一个php接口进行压力测试，
  并发为300时一切正常，
  当并发为350时大量出现502错误，此时CPU为接近100%，内存为60%，如何进行性能优化，请描述你的思路
 */
//一、PHP接口优化。
//1.1 减少DB访问
//2.2 代码考虑性能。避免写消耗性能的逻辑

//二、502着手
//2.1 PHP本身，优化php-fpm调优
//2.1.1 当前fastcgi进程数量是否够用
//2.1.2 php.ini 内存是否够用
























