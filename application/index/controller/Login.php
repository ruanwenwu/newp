<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Config;

class Login extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //
        $this->assignPublicForView();
        $seoData = array(
            "title"         =>  "t",
            "description"   =>  "d",
            "keywords"      =>  "k",
        );

        $backUrl  = $request->get("backurl");
        $backUrl  = $backUrl ? $backUrl : Config::get("main_host");
        $this->assign("backUrl",$backUrl);
        $this->assign("seoData", $seoData);
        return $this->fetch("index");
    }

    public function register(Request $request){
        $this->assignPublicForView();
        $seoData = array(
            "title"         =>  "t",
            "description"   =>  "d",
            "keywords"      =>  "k",
        );
        $backUrl  = $request->get("backurl");
        $backUrl  = $backUrl ? $backUrl : Config::get("main_host");
        $captcheKeyInfo = \app\index\service\Captche::generateKey(array("vtime"=>2));    //获得验证码信息
        $this->assign("verifycodeunderkey",$captcheKeyInfo['verifykey']);
        $this->assign("verifycodekey",$captcheKeyInfo['key']);
        $this->assign("backUrl",$backUrl);
        $this->assign("seoData", $seoData);
        $this->assign("requestMsgUrl","/ajax/sendmsg/register");
        $this->assign("doregisterUrl","/ajax/doregister");
        $this->assign("pagetype","ordinary");
        return $this->fetch('auth/register');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
