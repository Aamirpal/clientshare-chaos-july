<?php

namespace App\Http\Controllers;
use App\User;
use Socialite;
use Illuminate\Http\Request;
use Session;
use Auth;
use Redirect;
use URL;
use App\SpaceUser;


class SocialAuthController extends Controller {

     //
    /**
     * Redirect the user to the OAuth Provider.
     *
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        try{
            return Socialite::driver($provider)->redirect();
        }catch(\Exception $e){
            return redirect('/');
        }
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that 
     * redirect them to the authenticated users homepage.
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        try {
            if (Auth::check()) {
                $linkedin_data = Socialite::driver($provider)->stateless()->user();
                $space_id = Session::get('spaceid')??Session::get('space_info')['id'];
                $space_user = SpaceUser::getSpaceUserInfo($space_id, Auth::user()->id, 'first');
                $user = User::findOrFail( Auth::User()->id); 
                $checkBuyer = (new FeedbackController)->checkBuyer($space_id,Auth::user()->id);
                $account_data = json_encode(array($provider => wrapLinkedinJson($linkedin_data)));
                if($user){
                    $user->social_accounts = $account_data;
                    $user->save();
                }
                if(!isset($space_user->metadata['user_profile'])){
                    return redirect('clientshare/'.$space_id.'/?linkedin=yes');
                }elseif($checkBuyer == "buyer") {
                    return redirect('/')->with('buyer','yes');
                } else {
                    return redirect('/')->with('linked','yes');
                }
            } else {  
                return redirect('/');
            }
        } catch (\Exception $e) {
            return redirect('/');
        }
    }

    public function setLinkedinSession(Request $request){
        $data = $request->all();
        Session::put('linkedin_phoneno', $data['phone_no']);
        Session::put('linkedin_company', $data['company']);
        Session::put('linkedin_sub_company', $data['sub_company']);
        Session::put('linkedin_job_title', $data['job_title']);
        Session::put('linkedin_bio', $data['biotext']);
        Session::put('linkedin_link', $data['linkedin_link']);
        Session::put('linkedin_company_status',$data['company_status']);
        sleep(2);
        return 1;
    }
}
