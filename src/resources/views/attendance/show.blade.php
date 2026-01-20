@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')

<div class="attendance-show">
    <h2 class="attendance__heading">勤怠詳細</h2>

    {{-- =========================================
        attendance が存在しない日（休日など）
    ========================================= --}}
    @if (!$attendance)

        <div class="attendance-detail">

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">名前</div>
                <div class="attendance-detail__value">{{ $user->name }}</div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">日付</div>
                <div class="attendance-detail__value">{{ $date->format('Y年 n月j日') }}</div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">出勤・退勤</div>
                <div class="attendance-detail__value"></div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">休憩1</div>
                <div class="attendance-detail__value"></div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">休憩2</div>
                <div class="attendance-detail__value"></div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">備考</div>
                <div class="attendance-detail__value"></div>
            </div>

        </div>

    {{-- =========================================
        修正可能フォーム（status !== pending）
    ========================================= --}}
    @elseif ($attendance->status !== 'pending')

        <form method="POST" action="{{ route('attendance.request', $attendance->date->format('Y-m-d')) }}">
            @csrf

            <div class="attendance-detail">

                {{-- 名前 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">名前</div>
                    <div class="attendance-detail__value value-align">
                        <div class="align-left">{{ $attendance->user->name }}</div>
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">日付</div>
                    <div class="attendance-detail__value value-align">
                        <div class="align-left">{{ $attendance->date->format('Y年') }}</div>
                        <div class="align-separator"></div>
                        <div class="align-right">{{ $attendance->date->format('n月j日') }}</div>
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">出勤・退勤</div>
                    <div class="attendance-detail__value value-time">
                        <input type="time" name="clock_in"
                               value="{{ optional($attendance->clock_in)->format('H:i') }}"
                               class="{{ $attendance->clock_in ? 'has-value' : '' }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="clock_out"
                               value="{{ optional($attendance->clock_out)->format('H:i') }}"
                               class="{{ $attendance->clock_out ? 'has-value' : '' }}">
                    </div>
                </div>

                {{-- 休憩1 --}}
                @php $rest1 = $attendance->restTimes[0] ?? null; @endphp
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">休憩1</div>
                    <div class="attendance-detail__value value-time">
                        <input type="time" name="rests[0][start]"
                               value="{{ optional($rest1?->rest_start)->format('H:i') }}"
                               class="{{ $rest1?->rest_start ? 'has-value' : '' }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="rests[0][end]"
                               value="{{ optional($rest1?->rest_end)->format('H:i') }}"
                               class="{{ $rest1?->rest_end ? 'has-value' : '' }}">
                    </div>
                </div>

                {{-- 休憩2（値がある場合のみ表示） --}}
                @php $rest2 = $attendance->restTimes[1] ?? null; @endphp
                @if ($rest2 && ($rest2->rest_start || $rest2->rest_end))
                    <div class="attendance-detail__row">
                        <div class="attendance-detail__label">休憩2</div>
                        <div class="attendance-detail__value value-time">
                            <input type="time" name="rests[1][start]"
                                   value="{{ optional($rest2?->rest_start)->format('H:i') }}"
                                   class="{{ $rest2?->rest_start ? 'has-value' : '' }}">
                            <span class="time-separator">〜</span>
                            <input type="time" name="rests[1][end]"
                                   value="{{ optional($rest2?->rest_end)->format('H:i') }}"
                                   class="{{ $rest2?->rest_end ? 'has-value' : '' }}">
                        </div>
                    </div>
                @endif

                {{-- 備考 --}}
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">備考</div>
                    <div class="attendance-detail__value value-note">
                        <textarea name="note">{{ $attendance->note }}</textarea>
                    </div>
                </div>

            </div>

            <div class="attendance-detail__actions">
                <button type="submit" class="attendance-detail__button">修正</button>
            </div>
        </form>

    {{-- =========================================
        承認待ち画面（status === pending）
    ========================================= --}}
    @else

        <div class="attendance-detail">

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">名前</div>
                <div class="attendance-detail__value">{{ $attendance->user->name }}</div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">日付</div>
                <div class="attendance-detail__value">{{ $attendance->date->format('Y年 n月j日') }}</div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">出勤・退勤</div>
                <div class="attendance-detail__value">
                    {{ $attendance->clock_in?->format('H:i') }} 〜 {{ $attendance->clock_out?->format('H:i') }}
                </div>
            </div>

            {{-- 休憩1 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">休憩1</div>
                <div class="attendance-detail__value value-time">
                    <span class="time-box">{{ $attendance->restTimes[0]?->rest_start?->format('H:i') ?? '' }}</span>
                    <span class="time-separator">〜</span>
                    <span class="time-box">{{ $attendance->restTimes[0]?->rest_end?->format('H:i') ?? '' }}</span>
                </div>
            </div>

            {{-- 休憩2 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">休憩2</div>
                <div class="attendance-detail__value value-time">
                    <span class="time-box">{{ $attendance->restTimes[1]?->rest_start?->format('H:i') ?? '' }}</span>
                    <span class="time-separator">〜</span>
                    <span class="time-box">{{ $attendance->restTimes[1]?->rest_end?->format('H:i') ?? '' }}</span>
                </div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">備考</div>
                <div class="attendance-detail__value">{{ $attendance->note }}</div>
            </div>

        </div>

        <p class="attendance-detail__message">※ 承認待ちのため修正はできません</p>

    @endif
</div>

@endsection
