<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
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
            'clock_in'  => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],

            'rests.*.start' => ['nullable', 'date_format:H:i'],
            'rests.*.end'   => ['nullable', 'date_format:H:i'],

            'note' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut =$this->input('clock_out');

            /*
            |--------------------------------------------------------------------------
            | ① 出勤・退勤の前後チェック
            |--------------------------------------------------------------------------
            */

            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ② ③ 休憩時間チェック
            |--------------------------------------------------------------------------
            */

            $rest = $this->input('rests', []);

            foreach ($rests as $rest) {
                $start =$rest['start'] ?? null;
                $end =$rest['end'] ?? null;

                // ② 休憩開始が出勤前 or 退勤後
                if ($start && (
                    ($clockIn && $start < $clockIn) ||
                    ($clockOut && $start > $clockOut)
                )) {
                    $validator->errors()->add(
                        'rests',
                        '休憩時間が不適切な値です',
                    );
                }

                // ② 休憩開始が出勤前 or 退勤後
                if ($end && $clockOut && $end > $clockOut) {
                    $validator->errors()->add(
                        'rests',
                        '休憩時間もしくは退勤時間が不適切な値です',
                    );
                }
            }
        });
    }
}
