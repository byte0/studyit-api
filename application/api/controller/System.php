<?php
namespace app\api\controller;

class System {
    public function index() {
        return json(['msg' => '系统设置']);
    }
}