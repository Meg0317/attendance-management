@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-container">

    <h1 class="attendance-status {{ $status }}">
        @if ($status === 'before')
            勤務外
        @elseif ($status === 'working')
            出勤中
        @elseif ($status === 'resting')
            休憩中
        @elseif ($status === 'finished')
            退勤済み
        @endif
    </h1>

    <div class="attendance-datetime">
        <p class="attendance-date">
            {{ now()->locale('ja')->isoFormat('YYYY年MM月DD日（ddd）') }}
        </p>
        <p class="attendance-time">
            {{ now()->format('H:i') }}
        </p>
    </div>

    <div class="attendance-actions">
        @if ($status === 'before')
            <form method="POST" action="{{ route('attendance.start') }}">
                @csrf
                <button type="submit" class="btn btn-attendance">出勤</button>
            </form>

        @elseif ($status === 'working')
            <div class="attendance-actions-row">
                <form method="POST" action="{{ route('attendance.clockout') }}">
                    @csrf
                    <button type="submit" class="btn btn-attendance">退勤</button>
                </form>

                <form method="POST" action="{{ route('attendance.rest.start') }}">
                    @csrf
                    <button type="submit" class="btn btn-rest">休憩入</button>
                </form>
            </div>

        @elseif ($status === 'resting')
            <form method="POST" action="{{ route('attendance.rest.end') }}">
                @csrf
                <button type="submit" class="btn btn-rest">休憩戻</button>
            </form>

        @elseif ($status === 'finished')
            <p class="attendance-finished-message">
                お疲れ様でした。
            </p>
        @endif
    </div>
</div>
@endsection
