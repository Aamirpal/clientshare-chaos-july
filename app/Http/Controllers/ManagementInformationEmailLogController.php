<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\ManagementInformationEmailLog as MIEmailLogModel;

class ManagementInformationEmailLogController extends Controller{

    public function index(){
        return MIEmailLogModel::MIEmailLogs();
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'space_id' => 'required',
            'metadata' => 'required'
            ], [
            'required' => 'The `:attribute` is required.'
        ]);

        if ($validator->fails()) {
            return ['code'=>401, 'message'=>$validator->errors()->all()];
        }
        return MIEmailLogModel::create($request->all());
    }


    public function show(Request $request, $space_id){
        $validator = Validator::make(['space_id'=>$space_id], [
            'space_id' => 'required'
            ], [
            'required' => 'The `:attribute` is required.'
        ]);
        if ($validator->fails()) {
            return ['code'=>401, 'message'=>$validator->errors()->all()];
        }
        return MIEmailLogModel::where('space_id', $request->space_id)->get();
    }
}