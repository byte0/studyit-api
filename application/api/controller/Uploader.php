<?php
namespace app\api\controller;

use think\Db;
use app\api\controller\Base;

use OSS\OssClient;
use OSS\Core\OssException;

class Uploader extends Base {

    protected function upload() {
        $oss = config('oss');

        $accessKeyId = $oss['accessKeyId'];
        $accessKeySecret = $oss['accessKeySecret'];
        $endpoint = $oss['endpoint'];

        return new OssClient($accessKeyId, $accessKeySecret, $endpoint, true);
    }

    public function avatar() {

        $tc_id = $this->request->session()['loginfo']['tc_id'];

        $file = $this->request->file('tc_avatar');

        $info = $file->rule('uniqid')
            ->validate(['ext'=>'jpg,png,gif'])
            ->move(RUNTIME_PATH . 'uploads');
        
        if($info) {
            // 文件名
            $filename = $info->getFilename();
            // 全路径
            $pathname = $info->getPathName();
            // 上传至阿里OSS
            $result = $this->upload()->uploadFile('studyit', 'images/avatar/'.$filename, $pathname);

            // unlink($pathname);

            Db::name('teacher')
                ->where(['tc_id' => $tc_id])
                ->update(['tc_avatar' => $filename]);

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => [
                    'path' => $result['oss-request-url']
                ],
                'time' => time()
            ]);

        } else {
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

    // 上传封面
    public function cover() {

        $cs_id = $this->request->param()['cs_id'];

        $file = $this->request->file('cs_cover_original');

        $info = $file->rule('uniqid')
            ->validate(['ext'=>'jpg,png,gif'])
            ->move(RUNTIME_PATH . 'uploads');

        if($info) {
            // 文件名
            $filename = $info->getFilename();
            // 全路径
            $pathname = $info->getPathName();
            // 上传至阿里OSS
            $result = $this->upload()->uploadFile('studyit', 'images/cover/'.$filename, $pathname);

            // unlink($pathname);

            Db::name('course')
                ->where(['cs_id' => $cs_id])
                ->update(['cs_cover_original' => $filename]);

            return json([
                'code' => 200,
                'msg' => 'OK',
                'result' => [
                    'filename' => pathinfo($result['oss-request-url'] ,PATHINFO_BASENAME),
                    'path' => $result['oss-request-url']
                ],
                'time' => time()
            ]);

        } else {
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }
}




