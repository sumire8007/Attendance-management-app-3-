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
            'clock_in_change_at' => [
                            'required',
                            'before:clock_out_change_at'
                        ],
            'clock_out_change_at' => [
                            'required',
                            'after:clock_in_change_at'
                        ],
            // 'rest_in_at.*' => [
            //                 'before:clock_out_change_at',
            //                 'after:clock_in_change_at',
            //                 'before:rest_out_change_at'
            //             ],
            // 'rest_out_at.*' => [
            //                 'before:clock_out_change_at',
            //                 'after:clock_in_change_at',
            //                 'before:rest_in_change_at'
            //             ],
            'remark_change' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'clock_in_change_at.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out_change_at.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in_change_at.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out_change_at.after' => '出勤時間もしくは退勤時間が不適切な値です',
            // 'rest_in_at.*.before' => '休憩時間が勤務時間外です',
            // 'rest_in_at.*.after' => '休憩時間が勤務時間外です',
            // 'rest_out_at.*.before' => '休憩時間が勤務時間外です',
            // 'rest_out_at.*.after' => '休憩時間が勤務時間外です',
            'remark_change.required' => '備考を記入してください',
        ];
    }
}
