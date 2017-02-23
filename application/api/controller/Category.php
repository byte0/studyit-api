<?php
namespace app\api\controller;

use think\Db;
use app\api\controller\Base;

class Category extends Base {
    // 分类列表
    public function index() {
        $result = Db::name('category')
            ->field('cg_id, cg_name, cg_pid, cg_order, cg_is_hide, count(*) as total')
            ->join('course', 'cg_id = cs_cg_id', 'LEFT')
            ->group('cg_id')
            ->select();

        if($result) {
            $tree = $this->tree($result, 0, 0);

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $tree
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 数据处理
    protected function tree($arr, $pid, $lev) {
        static $tree;
        foreach($arr as $row) {
            if($row['cg_pid'] == $pid) {
                $row['level'] = $lev++;
                $tree[] = $row;
                $this->tree($arr, $row['cg_id'], $lev);
            }
        }
        return $tree;
    }

    // 获取顶级分类
    public function top($flag=true) {
        $result = Db::name('category')
            ->field('cg_id, cg_name')
            ->where(['cg_pid' => 0])
            ->select();

        if($result) {

            if($flag) {
                return json([
                    'code' => 200,
                    'msg' => 'OK',
                    'result'=> $result,
                    'time' => time()
                ]);
            } else {
                return $result;
            }

        }

        abort(500, 'Internal Server Error');
    }

    // 子分类
    public function child() {

        $cg_id = $this->request->param()['cg_id'];

        $result = Db::name('category')
            ->field('cg_id, cg_name')
            ->where(['cg_pid' => $cg_id])
            ->select();

        if($result) {
            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $result,
                'time' => time()
            ]);

            abort(500, 'Internal Server Error');
        }
    }

    // 添加分类
    public function add() {
        // 获取参数
        $param = $this->request->param();

        $result = Db::name('category')->insert($param);

        if($result) {
            return json([
                'code' => 200,
                'msg' => 'OK',
                'time' => time()
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 编辑分类
    public function edit() {
        // 分类id
        $cg_id = $this->request->param('cg_id');

        $result = Db::name('category')
            ->field('cg_update_time', true)
            ->find($cg_id);

        if($result) {
            // 顶级分类
            $top = $this->top(false);

            $result['top'] = $top;

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $result,
                'time' => time()
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 修改分类
    public function modify() {
        // 获取参数
        $param = $this->request->param();

        $cg_id = intval($param['cg_id']);

        unset($param['tc_id']);

        // 写入数据库
        Db::name('category')
            ->where(['cg_id' => $cg_id])
            ->update($param);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'time' => time()
        ]);
    }
}

