@extends($isAdmin ? 'layouts.admin' : 'layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')

@php
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

        {{-- 左リンク --}}
        @if ($isAdmin)
            <a href="{{ route('admin.attendance.staff', [
                'user'  => $user->id,
                'month' => $month->copy()->subMonth()->format('Y-m')
            ]) }}">← 前月</a>
        @else
            <a href="{{ route('attendance.list', [
                'month' => $month->copy()->subMonth()->format('Y-m')
            ]) }}">← 前月</a>
        @endif

        {{-- 中央固定（カレンダーあり） --}}
        <div class="attendance-month__center">
            <form method="GET"
                  action="{{ $isAdmin
                        ? route('admin.attendance.staff', ['user' => $user->id])
                        : route('attendance.list') }}"
                  class="attendance-month__picker">
                <label class="calendar-label">
                    <img src="{{ asset('images/calendar-icon.png') }}"
                         alt="カレンダー"
                         class="calendar-icon">
                    <input type="month"
                           name="month"
                           value="{{ $displayMonth->format('Y-m') }}"
                           onchange="this.form.submit()">
                </label>
            </form>
            <span class="attendance-month__current">
                {{ $displayMonth->format('Y / m') }}
            </span>
        </div>

        {{-- 右リンク --}}
        @if ($isAdmin)
            <a href="{{ route('admin.attendance.staff', [
                'user'  => $user->id,
                'month' => $month->copy()->addMonth()->format('Y-m')
            ]) }}">翌月 →</a>
        @else
            <a href="{{ route('attendance.list', [
                'month' => $month->copy()->addMonth()->format('Y-m')
            ]) }}">翌月 →</a>
        @endif

    </div>

    <div class="attendance-space"></div>

    {{-- 勤怠テーブル --}}
    <div class="attendance-table-wrapper">
        <table class="attendance__table">
            <thead class="attendance-label">
                <tr>
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
                        <td>{{ $date->isoFormat('MM/DD(ddd)') }}</td>
                        <td>{{ $attendance?->clock_in?->format('H:i') ?? '' }}</td>
                        <td>{{ $attendance?->clock_out?->format('H:i') ?? '' }}</td>
                        <td>{{ $attendance?->rest_time ? gmdate('G:i', $attendance->rest_time) : '' }}</td>
                        <td>{{ $attendance?->work_time ? gmdate('G:i', $attendance->work_time) : '' }}</td>
                        <td>
                            {{-- ★ date基準の詳細リンク（空日OK） --}}
                            @if ($isAdmin)
                                <a class="detail-link"
                                   href="{{ route('admin.attendance.show', [
                                        'user' => $user->id,
                                        'date' => $date->format('Y-m-d'),
                                   ]) }}">
                                    詳細
                                </a>
                            @else
                                <a class="detail-link"
                                   href="{{ route('attendance.detail', $date->format('Y-m-d')) }}">
                                    詳細
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($isAdmin)
        <div class="export-form">
            <a href="{{ route('admin.attendance.staff.export', [
            'month' => request('month'),
            'user'  => request('user'),
            ]) }}" class="export__btn btn">
                CSV出力
            </a>
        </div>
    @endif

</div>

@endsection
