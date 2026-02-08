@extends('layouts.admin_attendance')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">

    @if (session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.attendance.storeOrUpdate') }}">
        @csrf

        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">

        @php
            $rests = $attendance?->restTimes->keyBy('order') ?? collect();
            $rest1 = $rests->get(1);
            $rest2 = $rests->get(2);
        @endphp

        <h2 class="page-title">勤怠詳細</h2>

        <div class="card">

            {{-- 名前 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value align-time">
                    <div class="left">{{ $user->name }}</div>
                </div>
            </div>

            {{-- 日付 --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="value align-time">
                    <div class="left">{{ $date->format('Y年') }}</div>
                    <div class="right">{{ $date->format('n月j日') }}</div>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value time">
                    <input type="time" name="clock_in"
                        value="{{ old('clock_in', optional($attendance?->clock_in)->format('H:i')) }}">
                    <span class="tilde">〜</span>
                    <input type="time" name="clock_out"
                        value="{{ old('clock_out', optional($attendance?->clock_out)->format('H:i')) }}">
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
                    <input type="time" name="rests[0][start]"
                        value="{{ old('rests.0.start', optional($rest1?->rest_start)->format('H:i')) }}">
                    <span class="tilde">〜</span>
                    <input type="time" name="rests[0][end]"
                        value="{{ old('rests.0.end', optional($rest1?->rest_end)->format('H:i')) }}">
                </div>
            </div>

            @error('rests.0.start')
            <div class="error">{{ $message }}</div>
            @enderror
            @error('rests.0.end')
            <div class="error">{{ $message }}</div>
            @enderror

            {{-- 休憩2 --}}
            <div class="row">
                <div class="label">休憩2</div>
                <div class="value time">
                    <input type="time" name="rests[1][start]"
                        value="{{ old('rests.1.start', optional($rest2?->rest_start)->format('H:i')) }}">
                    <span class="tilde">〜</span>
                    <input type="time" name="rests[1][end]"
                        value="{{ old('rests.1.end', optional($rest2?->rest_end)->format('H:i')) }}">
                </div>
            </div>

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
                    <textarea name="note">{{ old('note', $attendance?->note) }}</textarea>
                </div>
            </div>

            @error('note')
            <div class="error">{{ $message }}</div>
            @enderror

        </div>

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
