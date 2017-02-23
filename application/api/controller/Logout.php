<?php
namespace app\api\controller;

class Logout {

    public function index() {

        session('loginfo', null);
        setcookie(session_name(), '', time() - 42000, '/');

        return json([
            'code' => '200',
            'msg' => '退出成功',
            'time' => time()
        ]);
    }

}