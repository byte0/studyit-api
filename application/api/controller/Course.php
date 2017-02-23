<?php
namespace app\api\controller;

use think\Db;
use app\api\controller\Base;

class Course extends Base {

    // 权限检测
    protected function checkType() {
        // 是否为管理员
        $tc_type = session('loginfo')['tc_type'];

        if($tc_type) {
            return abort(403, 'Forbidden');
        }        
    }

    // 课程列表
    public function index() {
        
        $result = Db::query('select cs_id, cs_name, cs_cover, cs_cover_original, tc_name, cg_name from course left join teacher on cs_tc_id=tc_id left join category on cs_cg_id=cg_id');

        if($result) {
            for($i=0; $i<count($result); $i++) {
                $result[$i]['cs_cover'] = 'http://static.botue.com/images/cover/' . $result[$i]['cs_cover_original'] . '?' . $result[$i]['cs_cover'];

                unset($result[$i]['cs_cover_original']);
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

    // 课程名称
    public function create() {

        // 登陆信息
        $loginfo = session('loginfo');

        // 接收提交参数
        $param = $this->request->param();

        // 非管理员
        if($loginfo['tc_type'] > 0) {
            $param['cs_tc_id'] = $loginfo['tc_id'];
        }

        // 存入数据
        $insertId = Db::name('course')->insertGetId($param);

        if($insertId) {

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => ['cs_id' => $insertId],
                'time' => time()
            ]);
        }

        abort(500, 'Internal Server Error');
    }

    // 基本信息
    public function basic() {
        // 登陆信息
        $loginfo = session('loginfo');

        $param = $this->request->param();

        // 查询课程
        $result = Db::query('select cs_id, cs_cg_id, cs_tc_id, cs_name, tc_name, cs_brief, cs_tags, cs_cover, cs_cover_original from course left join teacher on cs_tc_id=tc_id where cs_id=' . $param['cs_id']);

        if($result) {

            $teacher = null;
            // 查询所有讲师
            if($loginfo['tc_type'] == 0) {
                $teacher = Db::name('teacher')
                    ->field('tc_id, tc_name')
                    // ->where('tc_type <> 0')
                    ->select();            
            }

            // 查询顶分类
            $top = Db::name('category')
                ->field('cg_id, cg_name')
                ->where('cg_pid=0')
                ->select();

            // 查询兄弟分类
            $childs = Db::query('select cg_id, cg_pid, cg_name from category where cg_pid = (select cg_pid from category where cg_id='.$result[0]['cs_cg_id'].')');

            $result[0]['cs_cg_pid'] = 0;
            if($childs) {
                $result[0]['cs_cg_pid'] = $childs[0]['cg_pid'];
            }

            $result[0]['teacher'] = $teacher;
            $result[0]['category'] = ['top' => $top, 'childs' => $childs];

            $cs_cover = $result[0]['cs_cover'];
            $cs_cover_original = $result[0]['cs_cover_original'];

            unset($result[0]['cs_cover_original']);
            // 封面图
            if($result[0]['cs_cover']) {
                $result[0]['cs_cover'] = 'http://static.botue.com/images/cover/' . $cs_cover_original . '?' . $cs_cover;
            }

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $result[0],
                'time' => time()
            ]);            
        }

        abort(500, 'Internal Server Error');
    }

    // 课程图片
    public function picture() {

        $param = $this->request->param();

        $cs_id = $param['cs_id'];

        $result = Db::query('select cs_id, cs_name, tc_name, cs_cover, cs_cover_original from course left join teacher on cs_tc_id=tc_id where cs_id='.$cs_id);

        $cs_cover = $result[0]['cs_cover'];
        $cs_cover_original = $result[0]['cs_cover_original'];

        if($cs_cover) {
            $result[0]['cs_cover'] = 'http://static.botue.com/images/cover/' . $cs_cover_original . '?' . $cs_cover;
        }

        if($cs_cover_original) {
            $result[0]['cs_cover_original'] = 'http://static.botue.com/images/cover/' . $cs_cover_original;
        }

        if($result) {
            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => $result[0],
                'time' => time()
            ]);
        }
    }

    // 课时管理
    public function lesson() {

        $param = $this->request->param();

        $cs_id = $param['cs_id'];

        $result = Db::query('select cs_id, cs_name, tc_name, cs_cover, cs_cover_original from course left join teacher on cs_tc_id=tc_id where cs_id='.$cs_id);

        $lesson = Db::name('chapter')
            ->field('ct_id, ct_name, ct_video_duration')
            ->where(['ct_cs_id' => $cs_id])
            ->select();

        $cs_cover = $result[0]['cs_cover'];
        $cs_cover_original = $result[0]['cs_cover_original'];

        if($cs_cover) {
            $result[0]['cs_cover'] = 'http://static.botue.com/images/cover/' . $cs_cover_original . '?' . $cs_cover;

            unset($result[0]['cs_cover_original']);
        }

        $result[0]['lessons'] = $lesson;

        return json([
            'code' => 200,
            'msg' => 'OK',
            'result' => $result[0],
            'time' => time()
        ]);
    }

    // 更新基本信息
    public function updateBasic() {

        // 获取请求参数
        $param = $this->request->param();

        // 课程ID
        $cs_id = $param['cs_id'];

        unset($param['cs_id']);

        // 更新数据库
        Db::name('course')
            ->where(['cs_id' => $cs_id])
            ->update($param);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'result' => ['cs_id' => $cs_id],
            'time' => time()
        ]);
    }

    public function updatePicture() {

        $param = $this->request->param();

        $cs_id = $param['cs_id'];
        $w = ceil($param['w']);
        $h = ceil($param['h']);
        $x = ceil($param['x']);
        $y = ceil($param['y']);
        $cs_cover = 'x-oss-process=image/crop,x_'.$x.',y_'.$y.',w_'.$w.',h_'.$h;

        Db::name('course')
            ->where(['cs_id' => $cs_id])
            ->update(['cs_cover' => $cs_cover]);

        return json([
            'code' => 200,
            'msg' => 'OK',
            'result' => ['cs_id' => $cs_id],
            'time' => time()
        ]);
    }
}








