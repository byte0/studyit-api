<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use app\api\controller\Base;

class Dashboard extends Base {

    public function index() {
        // 模拟数据
        $data = [
            'title' => [
                'text' => '课程数量'
            ],
            'tooltip' => ['' =>''],
            'legend' => [
                'data' => ['数量']
            ],
            'xAxis' => [
                'data' => ["HTML","CSS","Mobile","Angular","vue","Nodejs"]
            ],
            'yAxis' => ['' => ''],
            'series' => [[
                'name' => '数量',
                'type' => 'bar',
                'data' => [5, 20, 36, 10, 10, 20]
            ]]
        ];

        return json($data);
    }

}
