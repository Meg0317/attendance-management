<?php

namespace App\Http\Requests;
use Carbon\Carbon;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in'          => ['nullable', 'date_format:H:i'],
            'clock_out'         => ['nullable', 'date_format:H:i'],
            'rests.*.start'     => ['nullable', 'date_format:H:i'],
            'rests.*.end'       => ['nullable', 'date_format:H:i'],
            'note'              => ['required'],
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

            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            /*
            |--------------------------------------------------------------------------
            | ① 出勤・退勤の前後チェック
            |--------------------------------------------------------------------------
            */
            if ($clockIn && $clockOut) {
                $in  = Carbon::createFromFormat('H:i', $clockIn);
                $out = Carbon::createFromFormat('H:i', $clockOut);

                if ($in->gt($out)) {
                    $validator->errors()->add(
                        'clock_in',
                        '出勤時間もしくは退勤時間が不適切な値です'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ② ③ 休憩時間チェック
            |--------------------------------------------------------------------------
            */
            foreach ($this->input('rests', []) as $index => $rest) {

                $start = $rest['start'] ?? null;
                $end   = $rest['end'] ?? null;

                // 両方空ならスキップ
                if (!$start && !$end) {
                    continue;
                }

                if ($start) {
                    $restStart = Carbon::createFromFormat('H:i', $start);

                    // ② 休憩開始が出勤前 or 退勤後
                    if (
                        ($clockIn && $restStart->lt(Carbon::createFromFormat('H:i', $clockIn))) ||
                        ($clockOut && $restStart->gt(Carbon::createFromFormat('H:i', $clockOut)))
                    ) {
                        $validator->errors()->add(
                            "rests.$index.start",
                            '休憩時間が不適切な値です'
                        );
                    }
                }

                if ($end && $clockOut) {
                    $restEnd = Carbon::createFromFormat('H:i', $end);

                    // ③ 休憩終了が退勤後
                    if ($restEnd->gt(Carbon::createFromFormat('H:i', $clockOut))) {
                        $validator->errors()->add(
                            "rests.$index.end",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }
        });
    }
}
