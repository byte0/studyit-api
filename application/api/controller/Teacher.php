<?php
namespace app\api\controller;

use think\Db;
use app\api\controller\Base;

class Teacher extends Base {
    // 权限检测
    protected function checkType() {
        // 是否为管理员
        $tc_type = session('loginfo')['tc_type'];

        if($tc_type) {
            return abort(403, 'Forbidden');
        }        
    }

    // 根据id查看讲师信息
    protected function find($tc_id) {

        $result = Db::name('teacher')
            ->field('tc_pass, tc_type, tc_status, tc_update_time', true)
            ->where(['tc_id' => $tc_id])
            ->find();

        if($result) {

            $result['tc_birthday'] = date('Y-m-d', $result['tc_birthday']);
            $result['tc_join_date'] = date('Y-m-d', $result['tc_join_date']);

            return $result;
        }

        abort(500, 'Internal Server Error');
    }

    // 讲师列表
    public function index() {

        $this->checkType();

        // 查询讲师列表
        $result = Db::name('teacher')
            ->field('tc_id, tc_name, tc_roster, tc_gender, tc_cellphone, tc_email, tc_status, tc_birthday, tc_join_date')
            ->where('tc_type', '<>', 0)
            ->select();

        if($result) {

            // 数据格式处理
            foreach ($result as $key => $value) {
                $tc_birthday = $value['tc_birthday'];
                $tc_join_date = $value['tc_join_date'];

                $value['tc_birthday'] = date('Y-m-d', $tc_birthday);
                $value['tc_join_date'] = date('Y-m-d', $tc_join_date);

                $data[] = $value;
            }

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $data
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 添加讲师
    public function add() {

        $this->checkType();

        // 接收提交参数
        $param = $this->request->param();
        // md5加密
        $param['tc_pass'] = md5($param['tc_pass']);
        // 将字符串转时间戳
        $param['tc_join_date'] = strtotime($param['tc_join_date']);

        // 存入数据库
        $result = Db::name('teacher')->insert($param);

        if($result) {
            return json([
                'code' => 200,
                'msg' => 'OK',
                'time' => time()
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 编辑讲师
    public function edit() {

        $this->checkType();

        // 讲师id
        $tc_id = $this->request->param('tc_id');

        $result = Db::name('teacher')
            ->field('tc_id, tc_name, tc_join_date, tc_type, tc_gender')
            ->where(['tc_id' => $tc_id])
            ->find();

        if($result) {
            // 日期格式处理
            $result['tc_join_date'] = date('Y-m-d', $result['tc_join_date']);

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $result,
                'time' => time()
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 查看讲师资料
    public function view() {

        $this->checkType();

        // 讲师id
        $tc_id = $this->request->param('tc_id');

        $result = Db::name('teacher')
            ->field('tc_update_time, tc_type, tc_province, tc_city, tc_district', true)
            ->find($tc_id);

        if($result) {

            $tc_birthday = $result['tc_birthday'];
            $tc_join_date = $result['tc_join_date'];
            $tc_avatar = $result['tc_avatar'];

            $result['tc_birthday'] = date('Y-m-d', $tc_birthday);
            $result['tc_join_date'] = date('Y-m-d', $tc_join_date);

            if(!empty($tc_avatar)) {
                $result['tc_avatar'] = 'http://static.botue.com/images/avatar/' . $tc_avatar;
            }

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $result,
                'time' => time()
            ]);            
        }

        abort(500, 'Internal Server Error');
    }

    // 注销/启用讲师
    public function handle() {

        $this->checkType();

        // 获取参数
        $param = $this->request->param();
        // 讲师id
        $tc_id = $param['tc_id'];
        // 讲师状态
        $status = abs($param['tc_status'] - 1);

        Db::name('teacher')
            ->where(['tc_id' => $tc_id])
            ->update(['tc_status' => $status]);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'result' => ['tc_status' => $status],
            'time' => time()
        ]);
    }

    // 修改讲师资料
    public function modify() {

        // 读取登录信息
        $loginfo = session('loginfo');

        $tc_login_id = $loginfo['tc_id'];

        $tc_type = $loginfo['tc_type'];

        // 获取参数
        $param = $this->request->param();

        $tc_id = intval($param['tc_id']);

        unset($param['tc_id']);

        // 只能修改自已资料
        if($tc_login_id != $tc_id && $tc_type != 0) {
            return abort(403, 'Forbidden');
        }

        // 将字符串转时间戳
        $param['tc_join_date'] = strtotime($param['tc_join_date']);
        //$param['tc_birthday'] = strtotime($param['tc_birthday']);

        // 写入数据库
        Db::name('teacher')
            ->where(['tc_id' => $tc_id])
            ->update($param);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'time' => time()
        ]);
    }

    // 更新讲师信息
    public function update() {

        $param = $this->request->param();

        $tc_id = $param['tc_id'];
        unset($param['tc_id']);
        
        $param['tc_join_date'] = strtotime($param['tc_join_date']);

        Db::name('teacher')
            ->where(['tc_id' => $tc_id])
            ->update($param);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'time' => time()
        ]);

        abort(500, 'Internal Server Error');
    }

    // 用户中心设置
    public function profile() {
        // 登录id
        $tc_id = session('loginfo')['tc_id'];

        $result = Db::name('teacher')
            ->field('tc_pass, tc_status, tc_type, tc_update_time', true)
            ->find($tc_id);

        if(!empty($result['tc_avatar'])) {
            $result['tc_avatar'] = 'http://static.botue.com/images/avatar/' . $result['tc_avatar'];
        }

        $result['tc_birthday'] = date('Y-m-d', $result['tc_birthday']);

        $result['tc_join_date'] = date('Y-m-d', $result['tc_join_date']);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'result' => $result,
            'time' => time()
        ]);
    }

    // 修改密码
    public function repass() {
        // 登录信息
        $loginfo = session('loginfo');
        $tc_login_pass = $loginfo['tc_pass'];
        $tc_login_id = $loginfo['tc_id'];

        // 获取参数
        $param = $this->request->param();

        $tc_pass = md5($param['tc_pass']);
        $tc_new_pass = md5($param['tc_new_pass']);

        if($tc_login_pass != $tc_pass) {
            return abort(403, 'Forbidden');
        }

        Db::name('teacher')
            ->where(['tc_id' => $tc_login_id, 'tc_pass' => $tc_pass])
            ->update(['tc_pass' => $tc_new_pass]);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'time' => time()
        ]);
    }
}





