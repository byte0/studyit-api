<?php
namespace app\api\controller;

use think\Db;
use app\api\controller\Base;

class Chapter extends Base {

    public function index() {
        return json([
            'code' => 200,
            'msg' => '啥都没有',
            'time' => time()
        ]);
    }

    // 添加
    public function add () {
        
        $param = $this->request->param();

        $ct_minutes = $param['ct_minutes'];
        $ct_seconds = $param['ct_seconds'];

        unset($param['ct_minutes']);
        unset($param['ct_seconds']);

        $param['ct_video_duration'] = $ct_minutes . ':' . $ct_seconds;

        $insertId = Db::name('chapter')->insertGetId($param);

        if($insertId) {
            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $insertId,
                'time' => time()
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 编辑
    public function edit() {
        
        $param = $this->request->param();
        $ct_id = $param['ct_id'];

        $result = Db::name('chapter')
            ->field('ct_update_time', true)
            ->where(['ct_id' => $ct_id])
            ->find();

        if($result) {

            $duration = explode(':', $result['ct_video_duration']);

            $result['ct_minutes'] = $duration[0];
            $result['ct_seconds'] = $duration[1];

            unset($result['ct_video_duration']);

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $result,
                'time' => time()
            ]);
        }
        
        abort(500, 'Internal Server Error');
    }

    // 修改
    public function modify() {
        
        $param = $this->request->param();

        $ct_id = $param['ct_id'];
        $ct_minutes = $param['ct_minutes'];
        $ct_seconds = $param['ct_seconds'];

        unset($param['ct_id']);
        unset($param['ct_minutes']);
        unset($param['ct_seconds']);

        $param['ct_video_duration'] = $ct_minutes . ':' . $ct_seconds;

        Db::name('chapter')
            ->where(['ct_id' => $ct_id])
            ->update($param);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'time' => time()
        ]);

        abort(500, 'Internal Server Error');
    }

    // 删除
    public function delete() {

    }
}