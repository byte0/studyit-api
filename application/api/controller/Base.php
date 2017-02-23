<?php
namespace app\api\controller;

use think\Controller;

class Base extends Controller {

    public function _initialize() {
        $this->checkLogin();
    }

    public function checkLogin() {
        $session = session('loginfo');

        if(!$session) {
            abort(401, 'Unauthorized');
        }
    }
}