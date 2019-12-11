<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-30
 * Time: 下午4:17
 */

namespace app\v1\controller;
use think\Db;
use think\Exception;
use ext\MailerUtil;
use ext\MailTemplate;
use think\Log;

class Flow extends Common {
    /**
     * 传入类型{"fixed_nos":json..}
     * @return \think\response\Json
     */
    public function apply() {

        try {
            $userId = $this->loginUser['ems'];

            $userInfo = Db::table('ems_user')->where('USER_ID', $userId)->where('IS_DELETED', 0)
                ->field('user_name, mail')->find();

            // 给subject用
            $user = $userInfo['user_name'];
            $from = $userInfo['mail'];

            // 前端需要把数组变成字符串
            $fixed_nos = json_decode($this->request->param('fixed_nos'));// 转为数组

            // 需要插入的数据
            $inputData = array();

            for ($i = 0; $i < count($fixed_nos); $i++) {

                $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                    ->where('model_status', IN_STORE)->where('user_id', null)->find();
                if (!empty($query)) {
                    // 更新状态
                    $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', IN_STORE)->where('user_id', null)
                        ->update([
                            'user_name'    => $user,
                            'user_id'      => $userId,
                            'model_status' => BORROW_REVIEW,
                            'start_date' => Db::raw('now()')
                        ]);

                    // 更新成功
                    if (1 == $res) {
                        $section = $query['section_manager'];
                        $tmp['id'] = $query['fixed_no'];
                        $tmp['name'] = $query['MODEL_NAME'];
                        $tmp['desc'] = $query['remark'];

                        if (array_key_exists($section, $inputData)) {
                            $inputData[$section][] = $tmp;
                        } else {
                            $inputData[$section][] = $tmp;
                        }
                    } else {
                        Log::record('[Machine][apply] update fail ' . $fixed_nos[$i]);
                    }

                }
            }

            // 存入邮件队列表中
            if (!empty($inputData)) {
                foreach ($inputData as $key => $value) {
                    // 插入数据
                    $data = ['insert_date'=>Db::raw('now()'), 'main_body'=>'', 'from'=>$from, 'to'=>'',
                             'table_data' => json_encode($value)];
                    Db::table('ems_mail_queue')->insert($data);
                }

            }

        } catch (Exception $e) {
            Log::record('[Machine][apply] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');

        }
        return apiResponse(ERROR, 'server error');
    }

    private function getSectionAddress($section) {


    }



    private function sendSectionApproveMail($sectionArray, $user, $from) {
        for ($i = 0; $i < count($sectionArray); $i++) {

            try {
                // 先查询ems中的section
                $subSqlA = Db::table('ems_user')->where('SECTION', $sectionArray[$i])
                    ->where('IS_DELETED', 0)->buildSql();
                // 查询T系统roleId 为课长
                $subSqlB = Db::table('users')->where('role_id', MANAGER)->buildSql();

                $res = Db::table($subSqlA . ' a')
                    ->join([$subSqlB=> 'b'], 'a.id=b.ems_uid')->select();

                // 转成一维数组
                $tos = array();
                for ($j = 0; $j < count($res); $j++) {
                    array_push($tos, $res[$j]['email']);
                }

                $subject = MailTemplate::$subjectBorrowApprove. date('Y-m-d H:i:s', time()). ' '. $user.
                                MailTemplate::$subjectEnding;
                MailerUtil::send($from, $tos, null, $subject, MailTemplate::formatter($subject, $user));
            } catch (Exception $e) {
                Log::record('[Machine][apply] error' . $e->getMessage());
            }


        }

    }
}