@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')

<div class="attendance-list">
    <h2 class="attendance__heading">{{ $date->format('Y年m月d日') }}の勤怠</h2>

    {{-- 日付切り替え --}}
    <div class="attendance-month">
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}">
            ← 前日
        </a>

        <div class="attendance-month__current">
            📅 {{ $date->format('Y年m月d日') }}
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}">
            翌日 →
        </a>
    </div>

    {{-- 余白 --}}
    <div class="attendance-space"></div>

    {{-- 勤怠テーブル --}}
    <div class="attendance-table-wrapper">
        <table class="attendance__table">
            <thead>
                <tr class="attendance__row">
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
                        {{-- 名前 --}}
                        <td>{{ $attendance->user->name }}</td>

                        {{-- 出勤 --}}
                        <td>{{ $attendance->clock_in?->format('H:i') ?? '' }}</td>

                        {{-- 退勤 --}}
                        <td>{{ $attendance->clock_out?->format('H:i') ?? '' }}</td>

                        {{-- 休憩 --}}
                        <td>
                            {{ $attendance->rest_time ? gmdate('G:i', $attendance->rest_time) : '' }}
                        </td>

                        {{-- 合計 --}}
                        <td>
                            {{ $attendance->work_time ? gmdate('G:i', $attendance->work_time) : '' }}
                        </td>

                        {{-- 詳細 --}}
                        <td>
                            <a href="{{ route('admin.attendance.show', $attendance->id) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
