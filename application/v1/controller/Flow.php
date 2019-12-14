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
                    $mainBody = MailTemplate::getBorrowApply($section, $user);

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
                        Log::record('[Flow][replyBorrowApplyFromSection] no data ' . $fixed_nos[$i]);
                    }
                }
                // 存入邮件队列表中
                if (!empty($inputData)) {
                    $to = $this->getSampleAddress(EMS_ADMIN);

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
                        Log::record('[Flow][replyBorrowApplyFromSection] no data ' . $fixed_nos[$i]);
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
                    }
                    return apiResponse(SUCCESS, '[Flow][replyBorrowApplyFromSection] success');
                }
            }
        } catch (Exception $e) {
            Log::record('[Flow][replyBorrowApplyFromSection] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
        return apiResponse(ERROR, 'server error');
    }

    public function replyBorrowApplyFromSample() {
       try {
           $userId = $this->loginUser['ems'];

           $judge = $this->request->param('judge');
           $predictDate = $this->request->param('predictDate'); // 预计归还时间
           $fixed_nos = json_decode($this->request->param('fixed_nos'));

           $user = $this->getUserInfoById($userId);

           $inputData = array();
           // 分配
           if ('agree' == $judge) {
               for ($i = 0; $i < count($fixed_nos); $i++) {
                   $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                       ->where('model_status', ASSIGNING)->find();

                   if (!empty($query)) {
                       $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                           ->where('model_status', ASSIGNING)
                           ->update([
                               'predict_date'  => $predictDate,
                               'model_status' => USING
                           ]);

                       if (1 == $res) {
                           $tmp['id'] = $query['fixed_no'];
                           $tmp['name'] = $query['MODEL_NAME'];
                           $tmp['desc'] = $query['remark'];

                           $inputData[$query['user_id']][] = $tmp;

                           // 添加记录到history中
                           $updateQuery = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                                            ->find();
                           $data = ['ID'=>null,
                                    'FIXED_NO'=>$fixed_nos[$i],
                                    'user_id'=>$updateQuery['user_id'],
                                    'user_name'=>$updateQuery['user_name'],
                                    'start_date'=>$updateQuery['start_date'],
                                    'predict_date'=>$updateQuery['predict_date'],
                                    'remark'=>$updateQuery['remark'],
                                    'approver_id'=>$updateQuery['approver_id'],
                                    'approver_name'=>$updateQuery['approver_name'],
                                    'approve_date'=>$updateQuery['approve_date'],
                                    'assign_operator_name'=>$user['USER_NAME'],
                                    'assign_operator_ID'=>$user['USER_ID']
                                    ];

                           $r = Db::table('ems_borrow_history')->insert($data);
                           if (1 != $r) {
                               Log::record('[Flow][replyBorrowApplyFromSample] add history fail ' . $fixed_nos[$i]);
                           }

                       } else {
                           Log::record('[Flow][replyBorrowApplyFromSample] update fail ' . $fixed_nos[$i]);
                       }
                   } else {
                       Log::record('[Flow][replyBorrowApplyFromSample] no data ' . $fixed_nos[$i]);
                   }
               }

               if (!empty($inputData)) {
                   foreach ($inputData as $key => $value) {
                       $to = $this->getUserInfoById($key);
                       $subject = MailTemplate::$subjectBorrowApplyApproveFromSample.$user['USER_NAME'];
                       $mainBody = MailTemplate::getReplyApproveBorrowApplyFromSample($to['USER_NAME']);

                       // 插入数据
                       $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                           'from'=>$user['MAIL'], 'to'=>json_encode(array($to['MAIL'])),
                           'table_data' => json_encode($value)];

                       Db::table('ems_mail_queue')->insert($data);
                   }
                   return apiResponse(SUCCESS, '[Flow][replyBorrowApplyFromSample] success');
               }
           } else {
               for ($i = 0; $i < count($fixed_nos); $i++) {
                   $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                       ->where('model_status', ASSIGNING)->find();

                   if (!empty($query)) {
                       $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                           ->where('model_status', ASSIGNING)
                           ->update([
                               'user_name'    => null,
                               'user_id'      => null,
                               'approver_id'    => null,
                               'start_date'    => null,
                               'approve_date'  => null,
                               'approver_name'  => null,
                               'model_status' => IN_STORE,
                           ]);

                       if (1 == $res) {
                           $tmp['id'] = $query['fixed_no'];
                           $tmp['name'] = $query['MODEL_NAME'];
                           $tmp['desc'] = $query['remark'];

                           $inputData[$query['user_id']][] = $tmp;

                       } else {
                           Log::record('[Flow][replyBorrowApplyFromSample] update fail ' . $fixed_nos[$i]);
                       }
                   } else {
                       Log::record('[Flow][replyBorrowApplyFromSample] no data ' . $fixed_nos[$i]);
                   }
               }
               if (!empty($inputData)) {
                   foreach ($inputData as $key => $value) {
                       $to = $this->getUserInfoById($key);
                       $subject = MailTemplate::$subjectBorrowApplyRejectFromSample.$user['USER_NAME'];
                       $mainBody = MailTemplate::getReplyRejectBorrowApplyFromSample($to['USER_NAME']);

                       // 插入数据
                       $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                           'from'=>$user['MAIL'], 'to'=>json_encode(array($to['MAIL'])), // 定时任务判断是数组
                           'table_data' => json_encode($value)];

                       Db::table('ems_mail_queue')->insert($data);
                   }
                   return apiResponse(SUCCESS, '[Flow][replyBorrowApplyFromSection] success');
               }
           }

       } catch (Exception $e) {
           Log::record('[Flow][replyBorrowApplyFromSample] error' . $e->getMessage());
           return apiResponse(ERROR, 'server error');
       }
        return apiResponse(ERROR, 'server error');
    }

    public function returnSample() {
        try {
            $userId = $this->loginUser['ems'];

            $fixed_nos = json_decode($this->request->param('fixed_nos'));

            $user = $this->getUserInfoById($userId);

            $inputData = array();

            for ($i = 0; $i < count($fixed_nos); $i++) {
                $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                    ->where('model_status', USING)->find();

                if (!empty($query)) {
                    $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', USING)
                        ->update([
                            'user_name'    => null,
                            'user_id'      => null,
                            'approver_id'    => null,
                            'start_date'    => null,
                            'approve_date'  => null,
                            'approver_name'  => null,
                            'predict_date'  => null,
                            'model_status' => IN_STORE
                        ]);

                    if (1 == $res) {
                        $tmp['id'] = $query['fixed_no'];
                        $tmp['name'] = $query['MODEL_NAME'];
                        $tmp['desc'] = $query['remark'];

                        $inputData[$query['user_id']][] = $tmp;

                        // 更新记录到history中
                        $data = [
                            'end_date' => Db::raw('now()'), // 使用结束时间
                            'confirm_operator_id'=>$user['USER_ID'],
                            'confirm_operator_name'=>$user['USER_NAME']
                        ];

                        $r = Db::table('ems_borrow_history')->where('fixed_no', $fixed_nos[$i])
                                        ->whereNull('end_date')->whereNull('confirm_operator_id')
                                        ->whereNull('confirm_operator_name')->update($data);
                        if (1 != $r) {
                            Log::record('[Flow][returnSample] update history fail ' . $fixed_nos[$i]);
                        }

                    } else {
                        Log::record('[Flow][returnSample] update fail ' . $fixed_nos[$i]);
                    }
                } else {
                    Log::record('[Flow][returnSample] no data ' . $fixed_nos[$i]);
                }
            }

            if (!empty($inputData)) {
                foreach ($inputData as $key => $value) {
                    $to = $this->getUserInfoById($key);
                    $subject = MailTemplate::$subjectReturnSample.$user['USER_NAME'];
                    $mainBody = MailTemplate::getReturnSample($to['USER_NAME']);

                    // 插入数据
                    $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                        'from'=>$user['MAIL'], 'to'=>json_encode(array($to['MAIL'])), // 定时任务判断是数组
                        'table_data' => json_encode($value)];

                    Db::table('ems_mail_queue')->insert($data);
                }
                return apiResponse(SUCCESS, '[Flow][returnSample] success');
            }

        } catch (Exception $e) {
            Log::record('[Flow][returnSample] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
        return apiResponse(ERROR, 'server error');
    }

    public function deleteApply() {
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
                            'model_status'    => DELETE_REVIEW,
                            'scrap_operator'      => $user, // 老系统存入的就为用户名
                            'scrap_date' => Db::raw('now()'),
                        ]);

                    // 更新成功
                    if (1 == $res) {
                        $tmp['id'] = $query['fixed_no'];
                        $tmp['name'] = $query['MODEL_NAME'];
                        $tmp['desc'] = $query['remark'];

                        $inputData[$query['section_manager']][] = $tmp;
                    } else {
                        Log::record('[Flow][deleteApply] update fail ' . $fixed_nos[$i]);
                    }

                }
            }

            // 存入邮件队列表中
            if (!empty($inputData)) {
                foreach ($inputData as $sec => $value) {
                    $to = $this->getSectionAddress($sec);
                    $section = $sectionArray[$sec];
                    $subject = MailTemplate::$subjectDeleteApply. $section. ' '.$user;
                    $mainBody = MailTemplate::getDeleteApply($section, $user);

                    // 插入数据
                    $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                        'from'=>$from, 'to'=>json_encode($to), 'table_data' => json_encode($value)];
                    Db::table('ems_mail_queue')->insert($data);
                }
                return apiResponse(SUCCESS, '[Flow][deleteApply] success');
            }

        } catch (Exception $e) {
            Log::record('[Flow][deleteApply] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');

        }
        return apiResponse(ERROR, 'server error');

    }

    public function replyDeleteApplyFromSection() {
        try {
            $userId = $this->loginUser['ems'];

            $judge = $this->request->param('judge');
            $fixed_nos = json_decode($this->request->param('fixed_nos'));

            $user = $this->getUserInfoById($userId);

            $inputData = array();
            if ('agree' == $judge) {
                // 课长同意， 则删除机器
                for ($i = 0; $i < count($fixed_nos); $i++) {
                    $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', DELETE_REVIEW)->find();

                    if (!empty($query)) {
                        $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                            ->where('model_status', DELETE_REVIEW)
                            ->delete();
                        if (1 == $res) {
                            $tmp['id'] = $query['fixed_no'];
                            $tmp['name'] = $query['MODEL_NAME'];
                            $tmp['desc'] = $query['remark'];

                            $inputData[] = $tmp;
                        } else {
                            Log::record('[Flow][replyDeleteApplyFromSection] delete fail ' . $fixed_nos[$i]);
                        }
                    } else {
                        Log::record('[Flow][replyDeleteApplyFromSection] no data ' . $fixed_nos[$i]);
                    }
                }
                // 存入邮件队列表中
                if (!empty($inputData)) {
                    $to = $this->getSampleAddress(EMS_ADMIN);

                    $subject = MailTemplate::$subjectDeleteApplyApproveFromSection.$user['USER_NAME'];
                    $mainBody = MailTemplate::getDeleteApproveFromSection();

                    // 插入数据
                    $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                        'from'=>$user['MAIL'], 'to'=>json_encode($to), 'table_data' => json_encode($inputData)];

                    $res = Db::table('ems_mail_queue')->insert($data);

                    if (1 == $res) {
                        return apiResponse(SUCCESS, '[Flow][replyDeleteApplyFromSection] success');
                    }
                    return apiResponse(ERROR, 'server error');
                }

            } else {
                // 更新状态到在库
                for ($i = 0; $i < count($fixed_nos); $i++) {
                    $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', DELETE_REVIEW)->find();

                    if (!empty($query)) {
                        $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                            ->where('model_status', DELETE_REVIEW)
                            ->update([
                                'scrap_operator'      => null, // 老系统还会更新approve_id等根本没啥卵用
                                'scrap_date' => null,
                                'model_status'    => IN_STORE,
                            ]);

                        if (1 == $res) {
                            $tmp['id'] = $query['fixed_no'];
                            $tmp['name'] = $query['MODEL_NAME'];
                            $tmp['desc'] = $query['remark'];

                            $inputData[][] = $tmp;
                        } else {
                            Log::record('[Flow][replyDeleteApplyFromSection] update fail ' . $fixed_nos[$i]);
                        }
                    } else {
                        Log::record('[Flow][replyDeleteApplyFromSection] no data ' . $fixed_nos[$i]);
                    }
                }

                if (!empty($inputData)) {
                    $to = $this->getSampleAddress(EMS_ADMIN);

                    $subject = MailTemplate::$subjectDeleteApplyRejectFromSection.$user['USER_NAME'];
                    $mainBody = MailTemplate::getDeleteRejectFromSection();

                    // 插入数据
                    $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                        'from'=>$user['MAIL'], 'to'=>json_encode($to), 'table_data' => json_encode($inputData)];

                    $res = Db::table('ems_mail_queue')->insert($data);

                    if (1 == $res) {
                        return apiResponse(SUCCESS, '[Flow][replyDeleteApplyFromSection] success');
                    }
                    return apiResponse(ERROR, 'server error');
                }
            }
        } catch (Exception $e) {
            Log::record('[Flow][replyDeleteApplyFromSection] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
        return apiResponse(ERROR, 'server error');
    }

    public function scrapApply() {
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
                            'model_status'    => SCRAP_REVIEW,
                            'scrap_operator'      => $user, // 老系统存入的就为用户名
                            'scrap_date' => Db::raw('now()'),
                            'user_id'    => $userId        // 这里存入不是使用者的意思，表示申请者
                        ]);

                    // 更新成功
                    if (1 == $res) {
                        $tmp['id'] = $query['fixed_no'];
                        $tmp['name'] = $query['MODEL_NAME'];
                        $tmp['desc'] = $query['remark'];

                        $inputData[$query['user_id']][] = $tmp;
                    } else {
                        Log::record('[Flow][scrapApply] update fail ' . $fixed_nos[$i]);
                    }

                }
            }

            // 存入邮件队列表中
            if (!empty($inputData)) {
                foreach ($inputData as $key => $value) {
                    $to = $this->getSampleAddress(EMS_AUDITOR);

                    $subject = MailTemplate::$subjectScrapApply.$user;
                    $mainBody = MailTemplate::getScrapApply();

                    // 插入数据
                    $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                        'from'=>$from, 'to'=>json_encode($to), 'table_data' => json_encode($value)];
                    Db::table('ems_mail_queue')->insert($data);
                }
                return apiResponse(SUCCESS, '[Flow][scrapApply] success');
            }

        } catch (Exception $e) {
            Log::record('[Flow][scrapApply] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');

        }
        return apiResponse(ERROR, 'server error');

    }

    public function replyScrapApplyFromSample() {
        try {
            $userId = $this->loginUser['ems'];

            $judge = $this->request->param('judge');
            $fixed_nos = json_decode($this->request->param('fixed_nos'));

            $user = $this->getUserInfoById($userId);

            $inputData = array();
            if ('agree' == $judge) {
                // 审批员同意， 则删除机器
                for ($i = 0; $i < count($fixed_nos); $i++) {
                    $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', SCRAP_REVIEW)->find();

                    if (!empty($query)) {
                        $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                            ->where('model_status', SCRAP_REVIEW)
                            ->update([
                                'model_status'    => SCRAPED,
                                'approver_id'    => $userId,
                                'approve_date'  => Db::raw('now()'),
                                'approver_name'  => $user['USER_NAME']

                            ]);
                        if (1 == $res) {
                            $tmp['id'] = $query['fixed_no'];
                            $tmp['name'] = $query['MODEL_NAME'];
                            $tmp['desc'] = $query['remark'];

                            $inputData[$query['user_id']][] = $tmp;
                        } else {
                            Log::record('[Flow][replyScrapApplyFromSample] delete fail ' . $fixed_nos[$i]);
                        }
                    } else {
                        Log::record('[Flow][replyScrapApplyFromSample] no data ' . $fixed_nos[$i]);
                    }
                }
                // 存入邮件队列表中
                if (!empty($inputData)) {
                    foreach ($inputData as $key => $value) {
                        $to = $this->getUserInfoById($key);
                        $subject = MailTemplate::$subjectDeleteApplyApproveFromSection.$user['USER_NAME'];
                        $mainBody = MailTemplate::getDeleteApproveFromSection($to['USER_NAME']);

                        // 插入数据
                        $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                            'from'=>$user['MAIL'], 'to'=>json_encode(array($to['MAIL'])), // 定时任务判断是数组
                            'table_data' => json_encode($value)];

                        Db::table('ems_mail_queue')->insert($data);
                    }
                    return apiResponse(SUCCESS, '[Flow][replyScrapApplyFromSample] success');
                }

            } else {
                // 更新状态到在库
                for ($i = 0; $i < count($fixed_nos); $i++) {
                    $query = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                        ->where('model_status', DELETE_REVIEW)->find();

                    if (!empty($query)) {
                        $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_nos[$i])
                            ->where('model_status', DELETE_REVIEW)
                            ->update([
                                'scrap_operator'      => null, // 老系统还会更新approve_id等根本没啥卵用
                                'scrap_date' => null,
                                'user_id'    => null,
                                'model_status'    => IN_STORE,
                            ]);

                        if (1 == $res) {
                            $tmp['id'] = $query['fixed_no'];
                            $tmp['name'] = $query['MODEL_NAME'];
                            $tmp['desc'] = $query['remark'];

                            $inputData[$query['user_id']][] = $tmp;
                        } else {
                            Log::record('[Flow][replyScrapApplyFromSample] update fail ' . $fixed_nos[$i]);
                        }
                    } else {
                        Log::record('[Flow][replyScrapApplyFromSample] no data ' . $fixed_nos[$i]);
                    }
                }

                if (!empty($inputData)) {
                    foreach ($inputData as $key => $value) {
                        $to = $this->getUserInfoById($key);
                        $subject = MailTemplate::$subjectDeleteApplyRejectFromSection.$user['USER_NAME'];
                        $mainBody = MailTemplate::getDeleteRejectFromSection($to['USER_NAME']);

                        // 插入数据
                        $data = ['id'=>null, 'main_body'=>$mainBody, 'subject'=>$subject,
                            'from'=>$user['MAIL'], 'to'=>json_encode(array($to['MAIL'])), // 定时任务判断是数组
                            'table_data' => json_encode($value)];

                        Db::table('ems_mail_queue')->insert($data);
                    }
                    return apiResponse(SUCCESS, '[Flow][replyScrapApplyFromSample] success');
                }
            }
        } catch (Exception $e) {
            Log::record('[Flow][replyScrapApplyFromSample] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
        return apiResponse(ERROR, 'server error');
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

    private function getSampleAddress($roleId) {
        $address = array();
        try {
            $subSqlA = Db::table('ems_user')
                ->where('IS_DELETED', 0)->buildSql();

            $subSqlB = Db::table('users')->where('role_id', $roleId)->buildSql();

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