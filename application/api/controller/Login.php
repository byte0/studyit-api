<?php
namespace app\api\controller;

use think\Request;
use think\Db;

class Login {

    // 登录
    public function index(Request $request) {
        // 登录信息
        $where['tc_name'] = $request->param('tc_name');
        $where['tc_pass'] = md5($request->param('tc_pass'));
        $where['tc_status'] = 0;

        $result = Db::name('teacher')
            ->where($where)
            ->field('tc_id, tc_name, tc_pass, tc_type, tc_avatar')
            ->find();
            
        if($result) {

            if(!empty($result['tc_avatar'])) {
                $result['tc_avatar'] = 'http://static.botue.com/images/avatar/' . $result['tc_avatar'];
            }

            // 记录session信息
            session('loginfo', $result);

            $data = [
                'tc_name' => $result['tc_name'],
                'tc_avatar' => $result['tc_avatar']
            ];

            return json([
                'code' => 200,
                'msg' => '登录成功!',
                'result' => $data,
                'time' => time()
            ]);

        } else {
            abort(404, 'Not Found');
        }

    }
}