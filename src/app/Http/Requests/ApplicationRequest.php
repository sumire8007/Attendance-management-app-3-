<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
class ApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'remark_change' => 'required',
        ];
    }
    public function withValidator($validator){

        $validator->after(function ($validator) {

        //出退勤のバリデーション
            $clockIn = Carbon::parse($this->input('clock_in_change_at'));
            $clockOut = Carbon::parse($this->input('clock_out_change_at'));

            if(empty($clockIn) || empty($clockOut)) {
                $validator->errors()->add('clock_in_change_at', '出勤時間もしくは退勤時間が不適切な値です');
            }
            if ($clockIn > $clockOut) {
                $validator->errors()->add('clock_in_change_at', '出勤時間もしくは退勤時間が不適切な値です');
            }

        //休憩時間のバリデーション
            $restIns =$this->input('rest_in_at', []);
            $restOuts = $this->input('rest_out_at', []);

            foreach($restIns as $index => $restInTime){
                $restOutTime = $restOuts[$index] ?? null;

                $restIn = Carbon::parse($restInTime) ?? null;
                $restOut = Carbon::parse($restOutTime) ?? null;

                if(empty($restIn) && empty($restOut)){
                    continue;
                }
                if (isset($restIn) || isset($restOut)) {

                    if ($restIn > $restOut) {
                        $validator->errors()->add("rest_in_at.$index", '休憩時間が不適切な値です');
                    }
                    if ($restIn < $clockIn || $restOut > $clockOut) {
                        $validator->errors()->add("rest_in_at.$index", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'remark_change.required' => '備考を記入してください',
        ];
    }
}
