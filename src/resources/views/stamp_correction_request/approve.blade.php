@extends('layouts.admin_attendance')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">

@php
    /** @var \App\Models\StampCorrectionRequest $stampCorrectionRequest */
    $attendance = $stampCorrectionRequest->attendance;

    $rests = $attendance->restTimes->keyBy('order');
    $rest1 = $rests->get(1);
    $rest2 = $rests->get(2);
@endphp

<h2 class="page-title">勤怠詳細</h2>

<div class="card">

    {{-- 名前 --}}
    <div class="row">
        <div class="label">名前</div>
        <div class="value align-time">
            <div class="left">{{ $attendance->user->name }}</div>
        </div>
    </div>

    {{-- 日付 --}}
    <div class="row">
        <div class="label">日付</div>
        <div class="value align-time">
            <div class="left">{{ $attendance->date->format('Y年') }}</div>
            <div class="right">{{ $attendance->date->format('n月j日') }}</div>
        </div>
    </div>

    {{-- 出勤・退勤 --}}
    <div class="row">
        <div class="label">出勤・退勤</div>
        <div class="value time">

            @if($attendance->clock_in)
                <span class="time-text">{{ $attendance->clock_in->format('H:i') }}</span>
            @endif

            @if($attendance->clock_in && $attendance->clock_out)
                <span class="tilde">〜</span>
            @endif

            @if($attendance->clock_out)
                <span class="time-text">{{ $attendance->clock_out->format('H:i') }}</span>
            @endif

        </div>
    </div>

    {{-- 休憩 --}}
    <div class="row">
        <div class="label">休憩</div>
        <div class="value time">

            @if($rest1?->rest_start)
                <span class="time-text">{{ $rest1->rest_start->format('H:i') }}</span>
            @endif

            @if($rest1?->rest_start && $rest1?->rest_end)
                <span class="tilde">〜</span>
            @endif

            @if($rest1?->rest_end)
                <span class="time-text">{{ $rest1->rest_end->format('H:i') }}</span>
            @endif

        </div>
    </div>

    {{-- 休憩2（値がある時だけ表示） --}}
    @if($rest2 && ($rest2->rest_start || $rest2->rest_end))
    <div class="row">
        <div class="label">休憩2</div>
        <div class="value time">

            @if($rest2?->rest_start)
                <span class="time-text">{{ $rest2->rest_start->format('H:i') }}</span>
            @endif

            @if($rest2?->rest_start && $rest2?->rest_end)
                <span class="tilde">〜</span>
            @endif

            @if($rest2?->rest_end)
                <span class="time-text">{{ $rest2->rest_end->format('H:i') }}</span>
            @endif

        </div>
    </div>
    @endif

    {{-- 備考（申請理由） --}}
    <div class="row">
        <div class="label">備考</div>
        <div class="value">
            {{ $attendance->note }}
        </div>
    </div>

</div>

{{-- 承認ボタン --}}
<div class="actions">
    @if($stampCorrectionRequest->status === 0)
        <form method="POST"
              action="{{ route(
                  'admin.stamp_correction_request.approve.store',
                  $stampCorrectionRequest
              ) }}">
            @csrf
            <button type="submit" class="btn">承認</button>
        </form>
    @else
        <span class="btn approved">承認済み</span>
    @endif
</div>

</div>
@endsection
