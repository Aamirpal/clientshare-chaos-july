<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model {
    
    protected $keyType = 'string';
    const COMPANY_TYPE = [
        'buyer' => 'buyer',
        'seller' => 'seller'
    ];

    protected $fillable = [
        'company_name', 'description'
    ];
    
    public function getIdAttribute($value){
    	return (string) $value;
    }
    public static function getCompanyById($company_id) {
        if(!strlen(($company_id)))
            return ['company_name'=>null];
        return static::where('id', $company_id)->first();
    }

    public static function CompanyByName($company_name) {
        return static::where('company_name', $company_name)->first();
    }

    public static function getCompanysById($companys_id) {
        $companies = static::whereIn('id', $companys_id)->get();
        foreach ($companies as $company) {
            $companies_info[$company['id']][] = $company;
        }
        return $companies_info??null;
    }
    public static function getAllCompanyNames() {
		return static::select(['id', 'company_name'])->get()->toArray();
    }
    public static function getCompanyByName($company_name, $selection_method) {
        return static::where('company_name', 'ilike', $company_name)->$selection_method();
    }

    public static function getAllCompaniesById($companies_id, $selection) {
        return static::select($selection)->whereIn('id', $companies_id)->pluck('company_name','id');
    }

    public function getCompanyBySellerOrbuyerName($company_name) {
        return $this->whereRaw("lower(company_name) = lower('" . str_replace("'", "", $company_name) . "')")->get();
    }

    public function createCompanyName($company_name) {
        return $this->create(['company_name' => $company_name]);
    }
}
