@extends('layouts.attendance')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">

<form method="POST" action="{{ route('attendance.storeOrUpdate') }}">
@csrf

<input type="hidden" name="date" value="{{ $attendance->date->format('Y-m-d') }}">

@php
    // 承認待ち表示用データ
    $after = $latestRequest->after_data ?? [];

    $afterRests = collect($after['rests'] ?? []);
    $afterRest1 = $afterRests->get(0);
    $afterRest2 = $afterRests->get(1);

    // 通常表示用データ
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
            @if(!$readonly)
                <input type="time" name="clock_in"
                    value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                <span class="tilde">〜</span>
                <input type="time" name="clock_out"
                    value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
            @else
                @if(!empty($after['clock_in']))
                    <span class="time-text">{{ $after['clock_in'] }}</span>
                @endif

                @if(!empty($after['clock_in']) && !empty($after['clock_out']))
                    <span class="tilde">〜</span>
                @endif

                @if(!empty($after['clock_out']))
                    <span class="time-text">{{ $after['clock_out'] }}</span>
                @endif
            @endif
        </div>
    </div>

    @error('clock_in')
    <div class="error">{{ $message }}</div>
    @enderror
    @error('clock_out')
    <div class="error">{{ $message }}</div>
    @enderror


    {{-- 休憩 --}}
    <div class="row">
        <div class="label">休憩</div>
        <div class="value time">
            @if(!$readonly)
                <input type="time" name="rests[0][start]"
                    value="{{ old('rests.0.start', optional($rest1?->rest_start)->format('H:i')) }}">
                <span class="tilde">〜</span>
                <input type="time" name="rests[0][end]"
                    value="{{ old('rests.0.end', optional($rest1?->rest_end)->format('H:i')) }}">
            @else
                @if(!empty($afterRest1['start']))
                    <span class="time-text">{{ $afterRest1['start'] }}</span>
                @endif

                @if(!empty($afterRest1['start']) && !empty($afterRest1['end']))
                    <span class="tilde">〜</span>
                @endif

                @if(!empty($afterRest1['end']))
                    <span class="time-text">{{ $afterRest1['end'] }}</span>
                @endif
            @endif
        </div>
    </div>

    @error('rests.0.start')
    <div class="error">{{ $message }}</div>
    @enderror
    @error('rests.0.end')
    <div class="error">{{ $message }}</div>
    @enderror


    {{-- 休憩2（空なら非表示） --}}
    @if(!$readonly || (!empty($afterRest2['start']) || !empty($afterRest2['end'])))
    <div class="row">
        <div class="label">休憩2</div>
        <div class="value time">
            @if(!$readonly)
                <input type="time" name="rests[1][start]"
                    value="{{ old('rests.1.start', optional($rest2?->rest_start)->format('H:i')) }}">
                <span class="tilde">〜</span>
                <input type="time" name="rests[1][end]"
                    value="{{ old('rests.1.end', optional($rest2?->rest_end)->format('H:i')) }}">
            @else
                @if(!empty($afterRest2['start']))
                    <span class="time-text">{{ $afterRest2['start'] }}</span>
                @endif

                @if(!empty($afterRest2['start']) && !empty($afterRest2['end']))
                    <span class="tilde">〜</span>
                @endif

                @if(!empty($afterRest2['end']))
                    <span class="time-text">{{ $afterRest2['end'] }}</span>
                @endif
            @endif
        </div>
    </div>
    @endif

    @error('rests.1.start')
    <div class="error">{{ $message }}</div>
    @enderror
    @error('rests.1.end')
    <div class="error">{{ $message }}</div>
    @enderror


    {{-- 備考 --}}
    <div class="row">
        <div class="label">備考</div>
        <div class="value">
            @if(!$readonly)
                <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
            @else
                <div class="note-text">
                    {{ $latestRequest->reason ?? $attendance->note ?? '' }}
                </div>
            @endif
        </div>
    </div>

    @error('note')
    <div class="error">{{ $message }}</div>
    @enderror

</div>

{{-- ボタン --}}
@if(!$readonly)
<div class="actions">
    <button class="btn">修正</button>
</div>
@else
<div class="actions pending">
    ※ 承認待ちのため修正はできません
</div>
@endif

</form>
</div>
@endsection
