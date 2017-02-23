<?php
namespace app\api\controller;

class User {
    public function index() {
        return json(['msg' => '用户管理']);
    }
}