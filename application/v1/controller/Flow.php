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
use ext\MailTemplate;
use think\Log;

class Flow extends Common {
    /**
     * 传入类型{"fixed_nos":json..}
     * @return \think\response\Json
     */
    public function borrowApply() {

        try {
            $userId = $this->loginUser['ems'];

            $userInfo = $this->getUserInfoById($userId);

            // 给subject用
            $user = $userInfo['USER_NAME'];
            $from = $userInfo['MAIL'];
            $sectionArray = json_decode(SECTION, true);

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
                            'model_status' => BORROW_REVIEW
                        ]);

                    // 更新成功
                    if (1 == $res) {
                        $tmp['id'] = $query['fixed_no'];
                        $tmp['name'] = $query['MODEL_NAME'];
                        $tmp['desc'] = $query['remark'];

                        $inputData[$query['section_manager']][] = $tmp;
                    } else {
                        Log::record('[Flow][borrowApply] update fail ' . $fixed_nos[$i]);
                    }

                }
            }

            // 存入邮件队列表中
            if (!empty($inputData)) {
                foreach ($inputData as $sec => $value) {
                    $to = $this->getSectionAddress($sec);
                    $section = $sectionArray[$sec];
                    $subject = MailTemplate::$subjectBorrowApply. $section. ' '.$user;
                    $mainBody = MailTemplate::getBorrowApplyMainBody($section, $user);

                    // 插入数据
                    $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                             'from'=>$from, 'to'=>json_encode($to), 'table_data' => json_encode($value)];
                    Db::table('ems_mail_queue')->insert($data);
                }
                return apiResponse(SUCCESS, '[Flow][borrowApply] success');
            }

        } catch (Exception $e) {
            Log::record('[Flow][borrowApply] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');

        }
        return apiResponse(ERROR, 'server error');
    }

    public function replyBorrowApplyFromSection() {
        try {
            $userId = $this->loginUser['ems'];

            $judge = $this->request->param('judge');
            $fixed_nos = json_decode($this->request->param('fixed_nos'));

            $user = $this->getUserInfoById($userId);

            $inputData = array();
            if ('agree' == $judge) {
                // 更新状态到审批通过(待分配)
                for ($i = 0; $i < count($fixed_nos); $i++) {
                    $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', BORROW_REVIEW)->whereNotNull('user_id')->find();

                    if (!empty($query)) {
                        $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                            ->where('model_status', BORROW_REVIEW)->whereNotNull('user_id')
                            ->update([
                                'approver_id'    => $userId,
                                'start_date'    => Db::raw('now()'),
                                'approve_date'  => Db::raw('now()'),
                                'approver_name'  => $user['USER_NAME'],
                                'model_status' => ASSIGNING
                            ]);

                        if (1 == $res) {
                            $tmp['id'] = $query['fixed_no'];
                            $tmp['name'] = $query['MODEL_NAME'];
                            $tmp['desc'] = $query['remark'];

                            $inputData[] = $tmp;
                        } else {
                            Log::record('[Flow][replyBorrowApplyFromSection] update fail ' . $fixed_nos[$i]);
                        }
                    } else {
                        Log::record('[Flow][replyBorrowApplyFromSection] update fail ' . $fixed_nos[$i]);
                    }
                }
                // 存入邮件队列表中
                if (!empty($inputData)) {
                    $to = $this->getMachineAdminAddress();

                    $subject = MailTemplate::$subjectBorrowApplyApproveFromSection.$user['USER_NAME'];
                    $mainBody = MailTemplate::getReplyApproveBorrowApplyFromSection();

                    // 插入数据
                    $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                        'from'=>$user['MAIL'], 'to'=>json_encode($to), 'table_data' => json_encode($inputData)];

                    $res = Db::table('ems_mail_queue')->insert($data);

                    if (1 == $res) {
                        return apiResponse(SUCCESS, '[Flow][replyBorrowApplyFromSection] success');
                    }
                    return apiResponse(ERROR, 'server error');
                }

            } else {
                // 更新状态到在库
                for ($i = 0; $i < count($fixed_nos); $i++) {
                    $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', BORROW_REVIEW)->find();

                    if (!empty($query)) {
                        $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                            ->where('model_status', BORROW_REVIEW)
                            ->update([
                                'user_name'    => null,
                                'user_id'      => null,
                                'model_status' => IN_STORE
                            ]);

                        if (1 == $res) {
                            $tmp['id'] = $query['fixed_no'];
                            $tmp['name'] = $query['MODEL_NAME'];
                            $tmp['desc'] = $query['remark'];

                            $inputData[$query['user_id']][] = $tmp;
                        } else {
                            Log::record('[Flow][replyBorrowApplyFromSection] update fail ' . $fixed_nos[$i]);
                        }
                    } else {
                        Log::record('[Flow][replyBorrowApplyFromSection] update fail ' . $fixed_nos[$i]);
                    }
                }

                if (!empty($inputData)) {
                    foreach ($inputData as $key => $value) {
                        $to = $this->getUserInfoById($key);
                        $subject = MailTemplate::$subjectBorrowApplyRejectFromSection.$user['USER_NAME'];
                        $mainBody = MailTemplate::getReplyRejectBorrowApplyFromSection($to['USER_NAME']);

                        // 插入数据
                        $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                                'from'=>$user['MAIL'], 'to'=>json_encode(array($to['MAIL'])), // 定时任务判断是数组
                                'table_data' => json_encode($value)];

                        Db::table('ems_mail_queue')->insert($data);

                        return apiResponse(SUCCESS, '[Flow][replyBorrowApplyFromSection] success');
                    }
                }
            }
        } catch (Exception $e) {
            Log::record('[Flow][replyBorrowApplyFromSection] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    public function replyBorrowApplyFromSample() {


    }

    private function getSectionAddress($section) {
        $address = array();
        try {
            $subSqlA = Db::table('ems_user')->where('SECTION', $section)
                ->where('IS_DELETED', 0)->buildSql();
            // 查询T系统roleId 为课长
            $subSqlB = Db::table('users')->whereIn('role_id', [T_MANAGER, S_MANAGER])->buildSql();

            $res = Db::table($subSqlA . ' a')
                ->join([$subSqlB=> 'b'], 'a.id=b.ems_uid')->field('MAIL')->select();

            foreach ($res as $k => $v) {
                $address[] = $v['MAIL'];
            }
            return $address;
        } catch (Exception $e) {
            Log::record('[Flow][getSectionAddress] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    private function getMachineAdminAddress() {
        $address = array();
        try {
            $subSqlA = Db::table('ems_user')
                ->where('IS_DELETED', 0)->buildSql();
            // 查询T系统roleId 为课长
            $subSqlB = Db::table('users')->where('role_id', EMS_ADMIN)->buildSql();

            $res = Db::table($subSqlA . ' a')
                ->join([$subSqlB=> 'b'], 'a.id=b.ems_uid')->field('MAIL')->select();

            foreach ($res as $k => $v) {
                $address[] = $v['MAIL'];
            }
            return $address;
        } catch (Exception $e) {
            Log::record('[Flow][getMachineAdminAddress] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }

    private function getUserInfoById($userId) {

        try {
            $user = Db::table('ems_user')->where('user_id', $userId)->where('IS_DELETED', 0)
                ->find();
            return $user;
        } catch (Exception $e) {
            Log::record('[Flow][getUserInfoById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
}