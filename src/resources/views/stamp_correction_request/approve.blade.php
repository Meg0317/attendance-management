@extends('layouts.admin')

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
        <div class="value">{{ $attendance->user->name }}</div>
    </div>

    {{-- 日付 --}}
    <div class="row">
        <div class="label">日付</div>
        <div class="value date">
            <span>{{ $attendance->date->format('Y年') }}</span>
            <span>{{ $attendance->date->format('n月j日') }}</span>
        </div>
    </div>

    {{-- 出勤・退勤 --}}
    <div class="row">
        <div class="label">出勤・退勤</div>
        <div class="value time">
            <span>{{ optional($attendance->clock_in)->format('H:i') }}</span>
            <span>〜</span>
            <span>{{ optional($attendance->clock_out)->format('H:i') }}</span>
        </div>
    </div>

    {{-- 休憩 --}}
    <div class="row">
        <div class="label">休憩</div>
        <div class="value time">
            <span>{{ optional($rest1?->rest_start)->format('H:i') }}</span>
            <span>〜</span>
            <span>{{ optional($rest1?->rest_end)->format('H:i') }}</span>
        </div>
    </div>

    {{-- 休憩2 --}}
    @if ($rest2 && ($rest2->rest_start || $rest2->rest_end))
        <div class="row">
            <div class="label">休憩2</div>
            <div class="value time">
                <span>{{ optional($rest2?->rest_start)->format('H:i') }}</span>
                <span>〜</span>
                <span>{{ optional($rest2?->rest_end)->format('H:i') }}</span>
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
@if ($stampCorrectionRequest->status === 0)
    <div class="actions">
        <form method="POST"
              action="{{ route(
                  'admin.stamp_correction_request.approve.store',
                  $stampCorrectionRequest
              ) }}">
            @csrf
            <button type="submit" class="btn approve">承認</button>
        </form>
    </div>
@else
    <div class="actions">
        <span class="btn approved">承認済み</span>
    </div>
@endif

</div>
@endsection
