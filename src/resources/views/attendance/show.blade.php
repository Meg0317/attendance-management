@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')

<div class="attendance-show">
    <h2 class="attendance__heading">勤怠詳細</h2>
    @if ($attendance)
        <form method="POST" action="{{ route('attendance.request', $attendance) }}">
            @csrf

            <div class="attendance-detail">

                {{-- 名前 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">名前</div>
                    <div class="attendance-detail__value value-align">
                        <div class="align-left">
                            {{ $attendance->user->name }}
                        </div>
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">日付</div>
                    <div class="attendance-detail__value value-align">
                        <div class="align-left">
                            {{ $attendance->date->format('Y年') }}
                        </div>
                        <div class="align-separator"></div>
                        <div class="align-right">
                            {{ $attendance->date->format('n月j日') }}
                        </div>
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">出勤・退勤</div>
                    <div class="attendance-detail__value value-time">

                        {{-- 修正可能 --}}
                        @if($attendance->status !== 'pending')
                            <input
                                type="time"
                                name="clock_in"
                                value="{{ optional($attendance->clock_in)->format('H:i') }}"
                                class="{{ $attendance->clock_in ? 'has-value' : '' }}"
                            >

                            <span class="time-separator">〜</span>

                            <input
                                type="time"
                                name="clock_out"
                                value="{{ optional($attendance->clock_out)->format('H:i') }}"
                                class="{{ $attendance->clock_out ? 'has-value' : '' }}"
                            >

                        @else
                            {{-- 承認待ち（枠なし） --}}
                            <span>
                                {{ $attendance->clock_in?->format('H:i') ?? '' }}
                            </span>
                            <span class="time-separator">〜</span>
                            <span>
                                {{ $attendance->clock_out?->format('H:i') ?? '' }}
                            </span>
                        @endif
                    </div>
                    @error('clock_in')
                    <p class="attendance-detail__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- 休憩 --}}
                @php $rest1 = $attendance->restTimes[0] ?? null; @endphp
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">休憩</div>
                    <div class="attendance-detail__value value-time">

                        @if($attendance->status !== 'pending')
                            <input
                                type="time"
                                name="rests[0][start]"
                                value="{{ optional($rest1?->rest_start)->format('H:i') }}"
                                class="{{ $rest1?->rest_start ? 'has-value' : '' }}"
                            >

                            <span class="time-separator">〜</span>

                            <input
                                type="time"
                                name="rests[0][end]"
                                value="{{ optional($rest1?->rest_end)->format('H:i') }}"
                                class="{{ $rest1?->rest_end ? 'has-value' : '' }}"
                            >
                        @else
                            <span>{{ $rest1?->rest_start?->format('H:i') ?? '' }}</span>
                            <span class="time-separator">〜</span>
                            <span>{{ $rest1?->rest_end?->format('H:i') ?? '' }}</span>
                        @endif
                    </div>
                </div>

                {{-- 休憩2 --}}
                @php $rest2 = $attendance->restTimes[1] ?? null; @endphp

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">休憩2</div>
                    <div class="attendance-detail__value value-time">

                        @if($attendance->status !== 'pending')
                            <input
                                type="time"
                                name="rests[1][start]"
                                value="{{ optional($rest2?->rest_start)->format('H:i') }}"
                                class="{{ $rest2?->rest_start ? 'has-value' : '' }}"
                            >

                            <span class="time-separator">〜</span>

                            <input
                                type="time"
                                name="rests[1][end]"
                                value="{{ optional($rest2?->rest_end)->format('H:i') }}"
                                class="{{ $rest2?->rest_end ? 'has-value' : '' }}"
                            >
                        @else
                            <span>{{ $rest2?->rest_start?->format('H:i') ?? '' }}</span>
                            <span class="time-separator">〜</span>
                            <span>{{ $rest2?->rest_end?->format('H:i') ?? '' }}</span>
                        @endif

                    </div>

                    @error('rests')
                    <p class="attendance-detail__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- 備考 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">備考</div>
                    <div class="attendance-detail__value value-note">

                        @if($attendance->status !== 'pending')
                            <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                        @else
                            <p>{{ $attendance->note }}</p>
                        @endif
                    </div>
                    @error('note')
                    <p class="attendance-detail__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

            </div>

            <div class="attendance-detail__actions">
                @if($attendance->status !== 'pending')
                    <button type="submit" class="attendance-detail__button">
                        修正
                    </button>
                @else
                    <p class="attendance-detail__message">
                        ※ 承認待ちのため修正はできません
                    </p>
                @endif
            </div>

        </form>
    @else
        <p class="text-muted">
            この日は勤怠データがありません。
        </p>
    @endif
</div>

@endsection
