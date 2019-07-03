<?php

namespace App\Traits;

use App\User;
use App\Http\Controllers\MailerController;

trait Scheduler
{
    protected $limit = 100;
    protected $offset = 0;

    function weeklySummaryTrigger()
    {
        $date['from'] = date('Y-m-d 09:00:00', strtotime('last Friday'));
        $date['to'] = date('Y-m-d 08:59:59');
        $user_basic = User::getFirstLastNameOfUsers();
        
        do {
            $this->offset += $this->limit;
            
            $data_array = [];
            $total_post_count = 0;
            
            $user_data = User::weeklyEmailUsers($date, $this->limit, $this->offset);
            
            foreach ($user_data as $key => $user) {
                $data_array['email'] = $user['email'];
                $data_array['first_name'] = $user['first_name'];
                $data_array['last_name'] = $user['last_name'];
                $data_array['user_id'] = $user['id'];

                if (!empty($user['space_user'])) {
                    foreach ($user['space_user'] as $user_share => $space_info) {
                        $data_array['share'][$user_share]['share_name'] = $space_info['share']['share_name'];
                        $data_array['share'][$user_share]['company_seller_logo'] = $space_info['share']['seller_processed_logo'];
                        $data_array['share'][$user_share]['company_buyer_logo'] = $space_info['share']['buyer_processed_logo'];
                        $data_array['share'][$user_share]['space_id'] = $space_info['share']['id'];

                        if (!empty($space_info['share']['posts'])) {
                            foreach ($space_info['share']['posts'] as $share_post => $post_data) {
                                if (stripos($post_data['visibility'], $user['id']) !== false || stripos($post_data['visibility'], 'All') !== false) {
                                    $post_by = [
                                        'first_name' => $user_basic[$post_data['user_id']]['first_name'],
                                        'last_name' => $user_basic[$post_data['user_id']]['last_name']
                                    ];
                                    array_push($post_data, ['post_by' => $post_by['first_name']]);
                                    $data_array['share'][$user_share]['posts'][$share_post] = $post_data;
                                    $total_post_count = $total_post_count + 1;
                                }
                            }
                        }
                    }
                    //mail function
                    $data['total_post'] = $total_post_count;
                    $data['to'] = $data_array['email'];
                    $data['subject'] = "Weekly Summary email";
                    $data['link'] = url('/');
                    $data['user_first_name'] = $data_array['first_name'];
                    $data['user_last_name'] = $data_array['last_name'];
                    $data['space_id'] = $data_array['share'][$user_share]['space_id'];
                    $data['unsubscribe_share'] =  env('APP_URL') . "/setting/" . $data_array['share'][$user_share]['space_id'] . "?email=".base64_encode($data_array['email']). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';
                        $user_weekly_setting = $user['settings'];
                    if (isset($user_weekly_setting['weekly_summary_setting'])) {
                        $user_mail_send_setting = $user_weekly_setting['weekly_summary_setting'];
                        if (isset($user_mail_send_setting) && $user_mail_send_setting == 1) {
                            $email_log[] = [
                                'type' => 'email',
                                'subtype' => 'weekly_email',
                                'data' => ($data),
                                'data_array' => ($data_array)
                            ];
                        }
                    }
                    $total_post_count = 0;
                    unset($data_array);
                }
            }
        } while (sizeOfCustom($user_data) >= $this->limit);
        $this->sendEmails($email_log);
    }

    function sendEmails(array $email_logs) {
        if (!sizeOfCustom($email_logs))
            return 0;

        foreach ($email_logs as $index => $email_log) {
            $total_post = $email_log['data']['total_post'];
            if ($total_post > 0)
                (new MailerController)->weeklyEmail("{$email_log['type']}.{$email_log['subtype']}", $email_log['data'], $email_log['data_array']);
        }
    }
}