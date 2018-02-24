<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

class Resis extends SessionHandler{
    /* 方法 */
    public function close (){
        return true;
    }
    
    public function create_sid (){
        return uniqid();    
    }
    
    public function destroy ($session_id ){
        return true;
    }
    
    public function gc ($maxlifetime){
        return 3;
    }
    public function open ($save_path,$session_name){
        return true;
    }
    public function read ($session_id){
        return '2';
    }
    
    public function write ($session_id,$session_data){
        return true;
    }
}