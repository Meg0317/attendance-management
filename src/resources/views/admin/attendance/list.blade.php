@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')

<div class="attendance-list">
    <h2 class="attendance__heading">{{ $date instanceof \Carbon\Carbon ? $date->format('Y年n月j日') : $date }}の勤怠</h2>

    {{-- 日付切り替え --}}
    <div class="attendance-month">

        {{-- 左リンク --}}
        <a href="{{ route('admin.attendance.list', ['date'=>($date instanceof \Carbon\Carbon ? $date->copy()->subDay()->format('Y-m-d') : $date)]) }}">
            ← 前日
        </a>

        {{-- 中央：カレンダーアイコン + 日付 --}}
        <div class="attendance-month__center">
            <form method="GET" action="{{ route('admin.attendance.list') }}" class="attendance-date__picker">
                <label class="calendar-label">
                    <img src="{{ asset('images/calendar-icon.png') }}" alt="カレンダー" class="calendar-icon">
                    <input type="date" name="date" value="{{ $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date }}" onchange="this.form.submit()">
                </label>
            </form>
            <span class="attendance-month__current">{{ $date instanceof \Carbon\Carbon ? $date->format('Y年m月d日') : $date }}</span>
        </div>

        {{-- 右リンク --}}
        <a href="{{ route('admin.attendance.list', ['date'=>($date instanceof \Carbon\Carbon ? $date->copy()->addDay()->format('Y-m-d') : $date)]) }}">
            翌日 →
        </a>

    </div>

    <div class="attendance-space"></div>

    {{-- 勤怠テーブル --}}
    <div class="attendance-table-wrapper">
        <table class="attendance__table">
            <thead class="attendance-label">
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ optional($attendance->clock_in)->format('H:i') ?? '' }}</td>
                    <td>{{ optional($attendance->clock_out)->format('H:i') ?? '' }}</td>
                    <td>
                        @if($attendance->restTimes->isNotEmpty())
                            @foreach($attendance->restTimes as $rest)
                                {{ optional($rest->rest_start)->format('H:i') ?? '-' }}〜{{ optional($rest->rest_end)->format('H:i') ?? '-' }}<br>
                            @endforeach
                        @endif
                    </td>
                    <td>{{ $attendance->work_time ? gmdate('G:i', $attendance->work_time) : '' }}</td>
                    <td>
                        <a class="detail-link" href="{{ route('admin.attendance.show', [
                            'user' => $attendance->user_id,
                            'date' => $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : $attendance->date,
                        ]) }}">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
