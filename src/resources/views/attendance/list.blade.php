@extends($isAdmin ? 'layouts.admin' : 'layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')

@php
    // 表示専用（月切り替え事故防止）
    $displayMonth = $month->copy();
@endphp

<div class="attendance-list">
    <h2 class="attendance__heading">
        @if ($isAdmin)
            {{ $user->name }}さんの勤怠
        @else
            勤怠一覧
        @endif
    </h2>

    {{-- 月切り替え --}}
    <div class="attendance-month">
        @if ($isAdmin)
            <a href="{{ route('admin.attendance.staff', [
                'user'  => $user->id,
                'month' => $month->copy()->subMonth()->format('Y-m')
            ]) }}">
                ← 前月
            </a>
        @else
            <a href="{{ route('attendance.list', [
                'month' => $month->copy()->subMonth()->format('Y-m')
            ]) }}">
                ← 前月
            </a>
        @endif

        <div class="attendance-month__current">
            📅 {{ $displayMonth->format('Y / m') }}
        </div>

        @if ($isAdmin)
            <a href="{{ route('admin.attendance.staff', [
                'user'  => $user->id,
                'month' => $month->copy()->addMonth()->format('Y-m')
            ]) }}">
                翌月 →
            </a>
        @else
            <a href="{{ route('attendance.list', [
                'month' => $month->copy()->addMonth()->format('Y-m')
            ]) }}">
                翌月 →
            </a>
        @endif
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
                        {{-- 日付 --}}
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
                            @if ($attendance)
                                <a href="{{ route('attendance.detail', $attendance->id) }}">
                                    詳細
                                </a>
                            @else
                                <a href="{{ route('attendance.detail', 'empty-' . $date->format('Ymd')) }}">
                                    詳細
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
