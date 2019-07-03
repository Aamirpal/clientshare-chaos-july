<?php

namespace App\Http\Controllers;

use App\ManagementInformation as MIModel;
use Illuminate\Http\Request;
use App\{Space, SpaceUser, Post, ManagementInformationEmailLog, User};
use App\Traits\ManagementInformation as MITrait;
use App\Jobs\{ManagementInformationReport, EmailHeaderImage, CreateJointShareLogos};
use Validator;

class ManagementInformation extends Controller {

    use MITrait;
    public $request;

    public function index() {
        $spaces = (new Space)->getActiveSpacesWithSellerBuyer();
        $spaces_list = (new Space)->spacesList(['id', 'share_name'], 'get');
        
        return view('management_information/index', [
            'spaces'=>$spaces,
            'spaces_list'=>$spaces_list->toArray()
        ]);
    }

    public function show(Request $request) {
        $request['disable_offset'] = false;
        return $this->getData($request);
    }

    public function downloadExcel(Request $request) {
        $request['loggedIn_user'] = \Auth::user();
        dispatch(new ManagementInformationReport($request));
        return;
    }

    public function spaceUser(Request $request) {
        $data = $request->all(); 
        $spaces = ['result'=>false];
        if(isset($data['space_id'])){
          $spaces['result'] = Space::spaceWithUser($data['space_id']);
          if(!empty($spaces))
          return (array) $spaces;
        }
        return $spaces;
    }

  function performanceEmail(Request $request, $space_id=null){
    $data = $request->all();
    $data['space_id'] = $data['space_id']??$space_id;
    $data['email_to'] = $data['email_to']??'';
    dispatch(new EmailHeaderImage($data['space_id']));
    dispatch(new CreateJointShareLogos($data['space_id']));
    $user = User::getUserByEmail($data['email_to']);
    $data['receiver'] =  !empty($user['first_name']) ? $user['first_name'] : ( !empty($data['email_to']) ? explode('@', $data['email_to'])[0] : 'Name');

    if ($request->isMethod('post')) 
    {
       Validator::extend('emails', function($attribute, $value, $parameters, $validator) 
       {
            $value = str_replace(' ','',$value);
            $array = explode(';', $value);
            foreach($array as $email) //loop over values
            {
                $email_to_validate['alert_email'][]=$email;
            }
            $rules = array('alert_email.*'=>'email');
            $messages = array(
                 'alert_email.*'=>trans('validation.email_array')
            );
            $validator = Validator::make($email_to_validate,$rules,$messages);
            if ($validator->passes()) {
                return true;
            } else {
                return false;
            }
        });
        $validator = Validator::make($data, [
            'email_to' => array('required', 'emails'),
            'email_cc' => array('emails'),
            'email_bcc' => array('emails'),
            'email_subject' => array('required'),
            'email_body' => array('required'),
            'community_buyers' => array('required', 'numeric'),
            'community_sellers' => array('required', 'numeric'),
            'month_posts' => array('required', 'numeric'),
            'csi_score' => array('required', 'numeric'),
        ], [
            'required' => 'This field is required',
            'emails' => 'Invalid email address.',
            'user.email.not_in' => 'Email cannot be used for share.',
            'numeric' => 'Only numbers are allowed.'
            ]
        );

        if ($validator->fails()) {
            return $request->ajax() ? response()->json(['result'=>false, 'error'=>$validator->messages()]) : back()->withErrors($validator)->withInput();
        }else{
            $this->sendMIEmail($data);
            if(!empty($data['space_id'])){
                ManagementInformationEmailLog::create(['space_id'=>$data['space_id'], 'metadata'=>$data]);
            }
            return $request->ajax() ? response()->json(['result'=>true]) : back()->with('success', 'Email sent successfully!');
        }
    }
    $data['email_subject'] = $data['email_subject'] ?? '';
    $data['email_body'] = $data['email_body'] ?? '';
    return view('management_information/performance_email',  compact('data'));
  }

}
