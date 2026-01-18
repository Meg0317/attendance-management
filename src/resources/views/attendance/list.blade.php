@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')

<div class="attendance-list">
    <h2 class="attendance__heading">勤怠一覧</h2>
    {{-- 月切り替え --}}
    <div class="attendance-month">
        <a href="{{ route('attendance.list', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">
            ← 前月
        </a>

        <div class="attendance-month__current">
            📅 {{ $month->format('Y / m') }}
        </div>

        <a href="{{ route('attendance.list', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">
            翌月 →
        </a>
    </div>

    {{-- 余白 --}}
    <div class="attendance-space"></div>

    {{-- 勤怠テーブル --}}
    <div class="attendance-table-wrapper">
        <table class="attendance__table">
            <thead>
                <tr class="attendance__row">
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

        <tbody>
            @foreach ($dates as $date)
                @php
                    $attendance = $attendances[$date->toDateString()] ?? null;
                @endphp

                <tr>
                    {{-- 日付（必ず表示） --}}
                    <td>{{ $date->isoFormat('MM/DD(ddd)') }}</td>

                    {{-- 出勤 --}}
                    <td>
                        {{ $attendance?->clock_in?->format('H:i') ?? '' }}
                    </td>

                    {{-- 退勤 --}}
                    <td>
                        {{ $attendance?->clock_out?->format('H:i') ?? '' }}
                    </td>

                    {{-- 休憩 --}}
                    <td>
                        {{ $attendance?->rest_time ? gmdate('G:i', $attendance->rest_time) : '' }}
                    </td>

                    {{-- 合計 --}}
                    <td>
                        {{ $attendance?->work_time ? gmdate('G:i', $attendance->work_time) : '' }}
                    </td>

                    {{-- 詳細 --}}
                    <td>
                        <a href="{{ route('attendance.show', $date->toDateString()) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
