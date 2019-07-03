<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Space;
use App\Company;
use Acme\Repository\SpaceInterface;
use Acme\Repository\UserInterface;

class SpaceController extends Controller
{

    protected $space;
    protected $user;
    protected $space_user;

    public function __construct(SpaceInterface $space, UserInterface $user)
    {
        $this->space = $space;
        $this->user = $user;
    }

    public function editSinglePostTemplate($space_id)
    {
        $share_data = (new Space)->editSinglePostTemplate($space_id);
        $company_ids = array_unique(array_column($share_data->toArray()['space_users'], 'company_id'));

        $companies = Company::getAllCompaniesById($company_ids, ['id', 'company_name'])->toArray();
        return view('posts/post/edit_post_template', compact('companies', 'share_data'));
    }    

    public function saveCategories(Request $request)
    {
        parse_str($request->categories, $categories);
        return $this->space->saveCategories([
            'categories' => $categories,
            'space_id' => $request->space_id
        ]);
    }

    public function updateSpace(Request $request)
    {
        return $this->space->updateSpace($request->space_id, $request->data);
    }

    public function updateTourStep(Request $request){
        $share = Space::find($request->space_id); 
        if($share['share_setup_steps'] < $request->step)
            return $this->space->updateSpace($request->space_id, ['share_setup_steps'=>$request->step]);
    }
}
