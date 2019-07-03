<?php

namespace App\Http\Requests\SpaceUser;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShareUserProfileRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'user.first_name' => 'required|max:25',
            'user.last_name' => 'required|max:25',
            'job_title' => 'required',
            'company_name' => 'required',
            'sub_company' => 'sometimes|required',
            'user.contact.contact_number' => 'max:24'
        ];
    }

    public function messages() {
        return [
            'required' => 'This field is required.',
            'user.first_name.max' => 'First name cannot be greater than 25 characters',
            'user.last_name.max' => 'Last name cannot be greater than 25 characters',
            'user.contact.contact_number.max' => 'Phone number cannot be greater than 24 characters'
        ];
    }

}
