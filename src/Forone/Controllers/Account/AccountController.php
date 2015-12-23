<?php
/**
 * User : aishan
 * Date : 12/23/15
 * Time : 10:19
 * Email: aishan520315@vip.126.com
 */

namespace Forone\Admin\Controllers\Account;

use Forone\Admin\Controllers\BaseController;
use Forone\Admin\Requests\UpdateAdminRequest;
use Forone\Admin\User;
use Illuminate\Contracts\Auth\PasswordBroker;

class AccountController extends BaseController {

    function __construct()
    {
        parent::__construct('account', '账号管理');
    }


    /**
     * @return $this|\Illuminate\Support\Facades\View
     */
    public function index()
    {
        $data=\Auth::getUser();
        if ($data) {
            return $this->view('forone::' . $this->uri. "/edit", compact('data'));
        }else{
            return $this->redirectWithError('数据未找到');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, UpdateAdminRequest $request)
    {
        $name = $request->get('name');
        $email = $request->get('email');
        $password= $request->get('new_password');
        $count = User::whereName($name)->where('id', '!=', $id)->count();
        if ($count > 0) {
            return $this->redirectWithError('名称不能重复');
        }
        $count = User::whereEmail($email)->where('id', '!=', $id)->count();
        if ($count > 0) {
            return $this->redirectWithError('邮箱不能重复');
        }
        if(!empty($password)){
            User::findOrFail($id)->update(array('name'=>$name,'email'=>$email,'password'=>bcrypt($password)));
            \Auth::logout();
            return redirect('/admin/auth/login')->withErrors(array('default'=>'密码重置成功，请重新登录'));
        }else{
            User::findOrFail($id)->update($request->only(['name', 'email']));
            return redirect()->route('account.index')->withErrors(array('default'=>'编辑成功'));
        }


    }


}