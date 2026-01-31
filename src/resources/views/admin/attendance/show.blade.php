@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">

@isset($attendance)

{{-- ✅ 成功メッセージ --}}
@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
@csrf

@php
    $readonly = $attendance->status === 'pending';
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
        @if(!$readonly)
            <input type="time" name="clock_in"
                value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
            <span>〜</span>
            <input type="time" name="clock_out"
                value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
        @else
            <span>{{ optional($attendance->clock_in)->format('H:i') }}</span>
            <span>〜</span>
            <span>{{ optional($attendance->clock_out)->format('H:i') }}</span>
        @endif
    </div>
</div>
@error('clock_in') <div class="error">{{ $message }}</div> @enderror
@error('clock_out') <div class="error">{{ $message }}</div> @enderror

{{-- 休憩 --}}
<div class="row">
    <div class="label">休憩</div>
    <div class="value time">
        @if(!$readonly)
            <input type="time" name="rests[0][start]"
                value="{{ old('rests.0.start', optional($rest1?->rest_start)->format('H:i')) }}">
            <span>〜</span>
            <input type="time" name="rests[0][end]"
                value="{{ old('rests.0.end', optional($rest1?->rest_end)->format('H:i')) }}">
        @else
            <span>{{ optional($rest1?->rest_start)->format('H:i') }}</span>
            <span>〜</span>
            <span>{{ optional($rest1?->rest_end)->format('H:i') }}</span>
        @endif
    </div>
</div>
@error('rests.0.start') <div class="error">{{ $message }}</div> @enderror
@error('rests.0.end') <div class="error">{{ $message }}</div> @enderror

{{-- 休憩2 --}}
@if(!$readonly || ($rest2 && ($rest2->rest_start || $rest2->rest_end)))
<div class="row">
    <div class="label">休憩2</div>
    <div class="value time">
        @if(!$readonly)
            <input type="time" name="rests[1][start]"
                value="{{ old('rests.1.start', optional($rest2?->rest_start)->format('H:i')) }}">
            <span>〜</span>
            <input type="time" name="rests[1][end]"
                value="{{ old('rests.1.end', optional($rest2?->rest_end)->format('H:i')) }}">
        @else
            <span>{{ optional($rest2?->rest_start)->format('H:i') }}</span>
            <span>〜</span>
            <span>{{ optional($rest2?->rest_end)->format('H:i') }}</span>
        @endif
    </div>
</div>
@error('rests.1.start') <div class="error">{{ $message }}</div> @enderror
@error('rests.1.end') <div class="error">{{ $message }}</div> @enderror
@endif

{{-- 備考 --}}
<div class="row">
    <div class="label">備考</div>
    <div class="value">
        @if(!$readonly)
            <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
        @else
            {{ $attendance->note }}
        @endif
    </div>
</div>
@error('note') <div class="error">{{ $message }}</div> @enderror

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

@else
{{-- attendance が存在しない場合 --}}
<h2 class="page-title">勤怠詳細</h2>
<div class="card empty">
    勤怠データが存在しません。
</div>
@endisset

</div>
@endsection
