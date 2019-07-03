<?php
namespace App\Traits;

use Excel;
use Mail;
use Illuminate\Http\Request;
use App\{OTP, Post, Space, SpaceUser};
use App\ManagementInformation as MIModel;
use Config;

trait ManagementInformation {

  public $offset = 0;
  public $limit = 50;

  private function getColumns()
  {
    return [
      'o-cont-value-th'=> [
        config('constants.MI_COLUMNS.CONTRACT_VALUE')
      ],
      'o-cont-date-th'=> [
        config('constants.MI_COLUMNS.CONTRACT_END_DATE')
      ],
      'b-comm-th'=> [
        config('constants.MI_COLUMNS.BUYER_COMMUNITY'),
        config('constants.MI_COLUMNS.BUYER_COMMUNITY').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      's-comm-th'=> [
        config('constants.MI_COLUMNS.SUPPLIER_COMMUNITY'),
        config('constants.MI_COLUMNS.SUPPLIER_COMMUNITY').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      'o-comm-th'=> [
        config('constants.MI_COLUMNS.OVERALL_COMMUNITY_SIZE'),
        config('constants.MI_COLUMNS.OVERALL_COMMUNITY_SIZE').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      'b-csi-th'=> [
        config('constants.MI_COLUMNS.BUYER_CSI_SCORE'),
        config('constants.MI_COLUMNS.BUYER_CSI_SCORE').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      's-csi-th'=> [
        config('constants.MI_COLUMNS.SUPPLIER_CSI_SCORE'),
        config('constants.MI_COLUMNS.SUPPLIER_CSI_SCORE').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      'o-csi-th'=> [
        config('constants.MI_COLUMNS.OVERALL_CSI_AVERAGE'),
        config('constants.MI_COLUMNS.OVERALL_CSI_AVERAGE').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      'b-posts-th'=> [
        config('constants.MI_COLUMNS.BUYER_POSTS'),
        config('constants.MI_COLUMNS.BUYER_POSTS').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      's-posts-th'=> [
        config('constants.MI_COLUMNS.SUPPLIER_POSTS'),
        config('constants.MI_COLUMNS.SUPPLIER_POSTS').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      'o-posts-th'=> [
        config('constants.MI_COLUMNS.OVERALL_POSTS'),
        config('constants.MI_COLUMNS.OVERALL_POSTS').' '.config('constants.MI_COLUMNS.GROWTH')
      ],
      'b-pi-th'=> [
        config('constants.MI_COLUMNS.BUYER_POSTS_INTERACTION')
      ],
      's-pi-th'=> [
        config('constants.MI_COLUMNS.SUPPLIER_POSTS_INTERACTION')
      ],
      'o-pi-th'=> [
        config('constants.MI_COLUMNS.OVERALL_POSTS_INTERACTION')
      ],
      'o-nps-th'=> [
        config('constants.MI_COLUMNS.NPS_SCORE')
      ],
      'o-pinv-th'=> [
        config('constants.MI_COLUMNS.PENDING_INVITES')
      ],
      'o-prog-th'=> [
        config('constants.MI_COLUMNS.PERCENTAGE_COMPLETE')
      ]
    ];
  }

	private function sendEmail(array $mail_data){
    $mail_data['path'] = env('APP_URL');
    \Mail::send('email.management_information_report_excel', [
      'mail_data' => $mail_data
    ], function ($message) use($mail_data) {
      $message->from(env('SENDER_FROM_EMAIL'), env('SENDER_NAME'));
      $message->to( $mail_data['to'] );
      if($mail_data['cc'])
        $message->cc( $mail_data['cc'] );
      $message->subject($mail_data['subject']);
    });
  }

  private function generateXLSFile($response, $include_column_filter)
  {
    $response_data = $response['data'];
    $response_spaces = $response['spaces'];
    $filter_column = [];
    $filter_column[] = '#';
    $filter_column[] = config('constants.MI_COLUMNS.SUPPLIER');
    $filter_column[] = config('constants.MI_COLUMNS.BUYER');
    $filter_column[] = config('constants.MI_COLUMNS.SHARE_NAME');
    $filter_column[] = config('constants.MI_COLUMNS.STATUS');

    foreach ($include_column_filter as $key => $column_group) 
    {
        $filter_column = array_merge($filter_column, $this->getColumns()[$column_group]);
    }
    $column_id = Config::get('constants.COLUMN_ID');
    $column_names = [];
    $column_width = [];
    $column_merge_sets = [];
    $prev_column = '';
    if(!empty($filter_column)) {
      foreach($filter_column as $column) {
        $column_names[$column_id] = $column;
        if($column_id == Config::get('constants.COLUMN_ID')) {
          $column_width[$column_id] = Config::get('constants.COLUMN_WIDTH_SMALL');
        } else {
          $column_width[$column_id] = Config::get('constants.COLUMN_WIDTH_LARGE');
        }
        if(trim($column) == '') {
          $column_merge_sets[] = "{$prev_column}1:{$column_id}1";
        }
        $prev_column = $column_id;
        $column_id++;
      }
    }

    $excel_data = [
      $filter_column
    ];   
    $row_num = 1; 
    foreach ($response_spaces as $response) 
    {
      if(
        !isset($response_data['spaces'][$response]) 
        || !isset($response_data['community'][$response]) 
        || !isset($response_data['csi'][$response])
        || !isset($response_data['posts'][$response])
        || !isset($response_data['pending_invites'][$response])
        || !isset($response_data['progress_bar'][$response])
        || !isset($response_data['posts_intractions'][$response])
        || !isset($response_data['nps'][$response])
      ) {
        continue;
      }
      
      if($response_data['nps'][$response]['feedback_status'] == TRUE || $response_data['nps'][$response]['total'] >= 1)
        $nps_total = $response_data['nps'][$response]['total'];
      else
        $nps_total = '-';

      $filter_column = [];
      $filter_column[] = $row_num;
      $filter_column[] = $response_data['spaces'][$response]['seller_name'];
      $filter_column[] = $response_data['spaces'][$response]['buyer_name'];
      $filter_column[] = $response_data['spaces'][$response]['share_name'];
      $filter_column[] = $response_data['spaces'][$response]['status'];

      if(!empty($include_column_filter))
      {
        if(in_array('o-cont-value-th', $include_column_filter))
          $filter_column[] = $response_data['spaces'][$response]['contract_value'] ? sprintf("%.2f", $response_data['spaces'][$response]['contract_value']/config('constants.MANAGEMENT_INFORMATION.contract_value_division')):'-';
        
        if(in_array('o-cont-date-th', $include_column_filter))
          $filter_column[] = ($response_data['spaces'][$response]['contract_end_date'])?date('m/y',strtotime($response_data['spaces'][$response]['contract_end_date'])):'';
        
        if(in_array('b-comm-th', $include_column_filter))
          $filter_column[] = $response_data['community'][$response]['buyers'];

        if(in_array('b-comm-th', $include_column_filter))
          $filter_column[] = $response_data['community'][$response]['cal_buyers'];

        if(in_array('s-comm-th', $include_column_filter)){
          $filter_column[] = $response_data['community'][$response]['sellers'];
          $filter_column[] = $response_data['community'][$response]['cal_sellers'];
        }
        if(in_array('o-comm-th', $include_column_filter)){
          $filter_column[] = $response_data['community'][$response]['over_all'];
          $filter_column[] = $response_data['community'][$response]['over_all_performance'];
        }
        if(in_array('b-csi-th', $include_column_filter)){
          $filter_column[] = $response_data['csi'][$response]['buyer_csi_score_this_month'];
          $filter_column[] = $response_data['csi'][$response]['buyer_csi_score_change'].'%';
        }
        if(in_array('s-csi-th', $include_column_filter)){
          $filter_column[] = $response_data['csi'][$response]['seller_csi_score_this_month'];
          $filter_column[] = $response_data['csi'][$response]['seller_csi_score_change'].'%';
        }
        if(in_array('o-csi-th', $include_column_filter)){
          $filter_column[] = $response_data['csi'][$response]['overall_csi_score'];
          $filter_column[] = $response_data['csi'][$response]['overall_csi_score_change'].'%';
        }
        if(in_array('b-posts-th', $include_column_filter)){
          $filter_column[] = $response_data['posts'][$response]['buyer_posts_total'];
          $filter_column[] = $response_data['posts'][$response]['buyer_posts_change'];
        }
        if(in_array('s-posts-th', $include_column_filter)){
          $filter_column[] = $response_data['posts'][$response]['supplier_posts_total'];
          $filter_column[] = $response_data['posts'][$response]['supplier_posts_change'];
        }
        if(in_array('o-posts-th', $include_column_filter)){
          $filter_column[] = $response_data['posts'][$response]['overall_posts_total'];
          $filter_column[] = $response_data['posts'][$response]['overall_posts_change'];
        }
        if(in_array('b-pi-th', $include_column_filter))
          $filter_column[] = $response_data['posts_intractions'][$response]['buyer_interations'];

        if(in_array('s-pi-th', $include_column_filter))
          $filter_column[] = $response_data['posts_intractions'][$response]['seller_interations'];

        if(in_array('o-pi-th', $include_column_filter))
          $filter_column[] = $response_data['posts_intractions'][$response]['total_interations'];

        if(in_array('o-nps-th', $include_column_filter))
          $filter_column[] = $nps_total;

        if(in_array('o-pinv-th', $include_column_filter))
          $filter_column[] = $response_data['pending_invites'][$response]['total'];   

        if(in_array('o-prog-th', $include_column_filter))
          $filter_column[] = config('constants.TASK_'.$response_data['progress_bar'][$response]['total'].'_PROGRESS').'%';      
      }

      $excel_data[] = $filter_column;
      $row_num++;
    }

    return $this->generateFile($excel_data, $column_names, $column_width, $column_merge_sets);
  }

  function generateFile($excel_data, $column_names, $column_width, $column_merge_sets){
    return Excel::create('ManagementInformationReport', function($excel) use(
      $excel_data,
      $column_names,
      $column_width,
      $column_merge_sets
    ) {
        $excel->sheet('Report', function($sheet) use(
          $excel_data,
          $column_names,
          $column_width,
          $column_merge_sets
        ) {
            $sheet->fromArray($excel_data, NULL, 'A1', TRUE, FALSE);
            if(!empty($column_merge_sets))
              foreach($column_merge_sets as $column_merge_set)
                $sheet->mergeCells($column_merge_set);
            
            end($column_names);
            $last_column = key($column_names); 
            $sheet->setWidth($column_width)
              ->cell("A1:{$last_column}1", function($cell) {
              $cell->setFontWeight('bold')->setAlignment('center');
            })->setHeight(1, 18);
            if (is_array($excel_data) && sizeOfCustom($excel_data) > 1) 
            {
              $num = Config::get('constants.NUMBER_COUNT');
              $first = true;
              foreach ($excel_data as $row => $data) 
              {
                $color_code = '#000000';
                if ($first) 
                {
                  $first = false;
                  continue;
                }
                $sheet->cells("A{$num}:$last_column{$num}", function ($cells) {
                  $cells->setAlignment('center');
                });
                $sheet->setHeight($num, 15);
                $increment = Config::get('constants.LOOP_START_COUNT'); $column_id = Config::get('constants.COLUMN_ID');
                foreach ($column_names as $key => $value) 
                {
                  if ($value == 'Buyer Community' && $data[$increment] >= Config::get('constants.COMMUNITY_GROWTH') || $value == 'Supplier Community' && $data[$increment] >= Config::get('constants.COMMUNITY_GROWTH')
                    || $value == 'Overall Community Size' && $data[$increment] >= Config::get('constants.OVERALL_GROWTH') || $value == 'Buyer CSI Score' && $data[$increment] >= Config::get('constants.CSI_GROWTH')
                    || $value == 'Supplier CSI Score' && $data[$increment] >= Config::get('constants.CSI_GROWTH') || $value == 'Overall CSI Average' && $data[$increment] >= Config::get('constants.CSI_GROWTH')
                    || $value == 'Buyer Posts' && $data[$increment] >= Config::get('constants.COMMUNITY_GROWTH') || $value == 'Supplier Posts' && $data[$increment] >= Config::get('constants.COMMUNITY_GROWTH') 
                    || $value == 'Overall Posts' && $data[$increment] >= Config::get('constants.COMMUNITY_GROWTH')) 
                  {
                    $color_code = '#2E8B41';
                    $sheet->cell("$column_id{$num}", function($cell) use($color_code) {
                      $cell->setFontColor($color_code);
                    });
                  }
                  elseif ($value == 'Buyer Community' && $data[$increment] < Config::get('constants.COMMUNITY_GROWTH') || $value == 'Supplier Community' && $data[$increment] < Config::get('constants.COMMUNITY_GROWTH')
                    || $value == 'Overall Community Size' && $data[$increment] < Config::get('constants.OVERALL_GROWTH') || $value == 'Buyer CSI Score' && $data[$increment] < Config::get('constants.CSI_GROWTH')
                    || $value == 'Supplier CSI Score' && $data[$increment] < Config::get('constants.CSI_GROWTH') || $value == 'Overall CSI Average' && $data[$increment] < Config::get('constants.CSI_GROWTH')
                    || $value == 'Buyer Posts' && $data[$increment] < Config::get('constants.COMMUNITY_GROWTH') || $value == 'Supplier Posts' && $data[$increment] < Config::get('constants.COMMUNITY_GROWTH') 
                    || $value == 'Overall Posts' && $data[$increment] < Config::get('constants.COMMUNITY_GROWTH')) 
                  {
                    $color_code = '#DC1515';
                    $sheet->cell("$column_id{$num}", function($cell) use($color_code) {
                      $cell->setFontColor($color_code);
                    });  
                  }

                  $increment++; $column_id++;
                }
                $num++;
              }
            }
        });
    });
  }

    private function getData(Request $request){
        $this->request = (object) $request->all();
        $this->setDefaults();

        switch ($this->request->sort) {
            case isset(Space::SPACE_SORT[$this->request->sort]):
                $response = $this->getShareData()->getCommunityData()->getCsiData()->getPostsData()->getPendingInvitesData()->getNpsData()->getProgressBarData()->getPostInteractionData(); 
                break;
            case 'buyer':
                $response = $this->getShareData()->getCommunityData()->getCsiData()->getPostsData()->getPendingInvitesData()->getNpsData()->getProgressBarData()->getPostInteractionData();
                break;
            case isset(MIModel::COMMUNITY_SORT[$this->request->sort]):
                $response = $this->getCommunityData()->getCsiData()->getPostsData()->getPendingInvitesData()->getShareData()->getNpsData()->getProgressBarData()->getPostInteractionData();
                break;
            case isset(MIModel::CSI_SORT[$this->request->sort]):
                $response = $this->getCsiData()->getCommunityData()->getPostsData()->getPendingInvitesData()->getShareData()->getNpsData()->getProgressBarData()->getPostInteractionData();
                break;
            case isset(MIModel::POST_SORT[$this->request->sort]):
                $response = $this->getPostsData()->getCommunityData()->getCsiData()->getPendingInvitesData()->getShareData()->getNpsData()->getProgressBarData()->getPostInteractionData();
                break;
            case 'pending':
                $response = $this->getPendingInvitesData()->getPostsData()->getCommunityData()->getCsiData()->getShareData()->getNpsData()->getProgressBarData()->getPostInteractionData();
                break;
            case 'nps':
                $response = $this->getNpsData()->getPendingInvitesData()->getPostsData()->getCommunityData()->getCsiData()->getShareData()->getProgressBarData()->getPostInteractionData();
                break;
            case 'progress_bar':
                $response = $this->getProgressBarData()->getNpsData()->getPendingInvitesData()->getPostsData()->getCommunityData()->getCsiData()->getShareData()->getPostInteractionData();
                break;
            case isset(MIModel::POST_INTERACTIONS[$this->request->sort]):
                $response = $this->getPostInteractionData()->getNpsData()->getPendingInvitesData()->getPostsData()->getCommunityData()->getCsiData()->getProgressBarData()->getShareData();
                break;
        }
        if(sizeOfCustom($response->request->spaces))
            $response->request->spaces_data = MIModel::ManagementInformationSpacesData($response->request->spaces);

        if(sizeOfCustom($response->request->spaces))
            $response->request->user_data = MIModel::ManagementInformationAdminUserData($response->request->spaces);

        return (array) $response->request;
    }

    private function setDefaults() {
        $this->request->sort = $this->request->sort ?? 'supplier';
        $this->request->sort_order = $this->request->sort_order ?? 'asc';
        $this->request->date_value = $this->request->date_value ? date('Y-m-d', strtotime($this->request->date_value)) : date('Y-m-d', time());
        $this->request->suppliers = array_filter($this->request->suppliers) ?? [];
        $this->request->buyers = array_filter($this->request->buyers) ?? [];
        $this->request->offset = $this->request->offset ?? $this->offset;
        $this->request->limit = $this->request->limit ?? $this->limit;
        $this->request->spaces = [];
        return $this->request;
    }

    private function loadExcelData(Request $request){
        $request['disable_offset'] = true;
        $response = $this->getData($request);
        $file = $this->generateXLSFile($response, $request->all()['include_column_filter']);

        $file_info = [
          'folder' => '/management_information/',
          'file_name' => 'ManagementInformationReport_'.time().'.xls',
          's3_url' => config('constants.s3.url'),
          'file_content' => $file->string('xls')
        ];

        $uploaded_file_url = uploadFileOnS3($file_info);

        $otp = OTP::create([
          'method' => 'GET',
          'app_url' => $file_info['folder'].$file_info['file_name'],
          'metadata' => ['user_id'=>$request->loggedIn_user->id]
        ]);

        $this->sendEmail([
          'to' => env('EXCEL_REPORT_USER', $request->loggedIn_user->email),
          'cc' => env('EXCEL_REPORT_USER_CC',''),
          'subject' => 'Management Information report',
          'file_path' => '/download_attachment/'.$otp->id.'?via_email=true',
          'user' => $request->loggedIn_user
        ]);
        return;
    }

    private function getShareData(){
        $filters = objectToArray($this->request);
        $this->request->data['spaces'] = Space::getSpacesData($filters);
        if(empty($this->request->spaces)) {
            $this->request->spaces = array_keys($this->request->data['spaces']);
        }
        return $this;
    }

    private function getCommunityData() {
        $this->request->data['community'] = MIModel::community($this->request);
        if(isset(MIModel::COMMUNITY_SORT[$this->request->sort])) {
            $this->request->spaces = array_keys($this->request->data['community']);
        }
        return $this;
    }

    private function getCsiData() {
        $this->request->data['csi'] = MIModel::getCsiData([
                    'suppliers' => $this->request->suppliers,
                    'buyers' => $this->request->buyers,
                    'date_value' => $this->request->date_value,
                    'sort' => $this->request->sort,
                    'sort_order' => $this->request->sort_order,
                    'offset' => $this->request->offset,
                    'limit' => $this->request->limit,
                    'spaces' => $this->request->spaces,
                    'disable_offset' => $this->request->disable_offset,
                    'RAG_filter' => $this->request->RAG_filter,
                    'status_filter' => $this->request->status_filter
        ]);
        if(isset(MIModel::CSI_SORT[$this->request->sort])){
            $this->request->spaces = array_keys($this->request->data['csi']);
        }
        return $this;
    }

    private function getPostsData() {
        $this->request->data['posts'] = [];
        $filters = (array) $this->request;

        $filters['date'] = date('Y-m-d', strtotime($filters['date_value'])).' 23:59:59';
        $filters['old_date'] = date('Y-m', strtotime($filters['date_value'])).'-01 00:00:00';

        $filters['limit'] = $this->limit;
        $this->request->data['posts'] = MIModel::getTotalPostsBySpaces($filters);
        if((sizeOfCustom($this->request->suppliers) || sizeOfCustom($this->request->buyers)) && isset(MIModel::POST_SORT[$this->request->sort])) {
            $this->request->spaces = array_keys($this->request->data['posts']);
        }else if(empty($this->request->spaces) && isset(MIModel::POST_SORT[$this->request->sort])) {
            $this->request->spaces = array_keys($this->request->data['posts']);
        } 
        return $this;
    }

    private function getPostInteractionData() {
        $this->request->data['posts_intractions'] = [];
        $filters = (array) $this->request;
        
        $filters['date'] = date('Y-m-d', strtotime($filters['date_value'])).' 23:59:59';
        $filters['old_date'] = date('Y-m', strtotime($filters['date_value'])).'-01 00:00:00';
        
        $filters['limit'] = $this->limit;
        $this->request->data['posts_intractions'] = MIModel::getPostInteractionData($filters);
        if((!empty($this->request->suppliers) || !empty($this->request->buyers) || empty($this->request->spaces)) && isset(MIModel::POST_INTERACTIONS[$this->request->sort])) {
            $this->request->spaces = array_keys($this->request->data['posts_intractions']);
        }
        return $this;
    }

    private function getPendingInvitesData() {
        $space_array = (!empty($this->request->suppliers))?implode("','",$this->request->suppliers):[];
        $buyer_array = (!empty($this->request->buyers))?implode("','",$this->request->buyers):[];
        $supplier_id = $space_array??[];
        $buyer_id = $buyer_array??[]; 
        $filters = (array) $this->request;
            $this->request->data['pending_invites'] = SpaceUser::getAllSharePendingInvitations($supplier_id, $buyer_id, $filters);
        if((sizeOfCustom($this->request->suppliers) || sizeOfCustom($this->request->buyers)) && $filters['sort'] == 'pending') {
            $this->request->spaces = array_keys($this->request->data['pending_invites']);
        }else if(empty($this->request->spaces) && $filters['sort'] == 'pending') {
            $this->request->spaces = array_keys($this->request->data['pending_invites']);
        }

        return $this;
    }

    private function getNpsData() {
        $space_array = (!empty($this->request->suppliers))?implode("','",$this->request->suppliers):[];
        $buyer_array = (!empty($this->request->buyers))?implode("','",$this->request->buyers):[];
        $supplier_id = $space_array??[];
        $buyer_id = $buyer_array??[]; 
        $filters = (array) $this->request;
            $this->request->data['nps'] = MIModel::getAllShareNps($supplier_id, $buyer_id, $filters);
        if((sizeOfCustom($this->request->suppliers) || sizeOfCustom($this->request->buyers)) && $filters['sort'] == 'nps') {
            $this->request->spaces = array_keys($this->request->data['nps']);
        }else if(empty($this->request->spaces) && $filters['sort'] == 'nps') {
            $this->request->spaces = array_keys($this->request->data['nps']);
        } 
        return $this;
    }

    private function getProgressBarData() {
        $space_array = (!empty($this->request->suppliers))?implode("','",$this->request->suppliers):[];
        $buyer_array = (!empty($this->request->buyers))?implode("','",$this->request->buyers):[];
        $supplier_id = $space_array??[];
        $buyer_id = $buyer_array??[]; 
        $filters = (array) $this->request;
        $this->request->data['progress_bar'] = (new MIModel)->getAllShareProgressBar($supplier_id, $buyer_id, $filters);
        if((sizeOfCustom($this->request->suppliers) || sizeOfCustom($this->request->buyers)) && $filters['sort'] == 'progress_bar')
            $this->request->spaces = array_keys($this->request->data['progress_bar']);
        else if(empty($this->request->spaces) && $filters['sort'] == config('constants.PROGRESS_BAR'))
            $this->request->spaces = array_keys($this->request->data['progress_bar']);
        
        return $this;
    }

    private function sendMIEmail($data){
        $mail_data = $data;
        $mail_data['template'] = 'email.performance_email';
        if(!isset($data['performance_email'])){
            $mail_data['space_info'] = Space::spaceById($mail_data['space_id'],'get')[0];
            $mail_data['template'] = 'email.mi_email';
        }
        $mail_data['link'] = env('APP_URL');
        $mail_data['path'] = env('APP_URL');
        $mail_data['space_name'] = 'Test';
        $mail_data['sender_name'] = env("SENDER_FROM_EMAIL");
        $mail_data['unsubscribe_share'] = env('APP_URL') . "/setting/" . $mail_data['space_id'] . "?email=".base64_encode($mail_data['email_to']). '&alert=true&via_email=1&notification=1&tab_name=notifications-tab';
       $sent = Mail::send($mail_data['template'], ['mail_data'=>$mail_data], function ($message) use ($mail_data) {
          $message->from(env("SENDER_FROM_EMAIL"), env("SENDER_NAME"));
          $message->to(explode(';', $mail_data['email_to']));
          if(!empty($mail_data['email_cc']))
            $message->cc(explode(';', $mail_data['email_cc']));
          if(!empty($mail_data['email_bcc']))
            $message->bcc(explode(';', $mail_data['email_bcc']));
          $message->subject($mail_data['email_subject']);
          $message->replyTo(env("SENDER_FROM_EMAIL"));
          $message->getSwiftMessage()->getHeaders()->addTextHeader('space_id', $mail_data['space_id']);
          $message->getSwiftMessage()->getHeaders()->addTextHeader('X-PM-Tag', 'performance-email');
        }); 
        return true;
    }

}