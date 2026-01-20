@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/index.css') }}">
@endsection

@section('content')

<div class="request-list">
    <h2 class="request-list__heading">申請一覧</h2>

    {{-- タブ --}}
    <div class="request-list__tabs">
        <a
            href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}"
            class="request-list__tab {{ $tab === 'pending' ? 'is-active' : '' }}"
        >
            承認待ち
        </a>
        <a
            href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}"
            class="request-list__tab {{ $tab === 'approved' ? 'is-active' : '' }}"
        >
            承認済み
        </a>
    </div>

    {{-- タブ下の線 --}}
    <div class="request-list__border"></div>

    {{-- 余白 --}}
    <div class="request-list__space"></div>

    {{-- テーブル --}}
    <div class="request-list__table-wrapper">
        <table class="request-list__table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr>
                        <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                        <td>{{ $request->note }}</td>
                        <td>{{ $request->created_at->format('Y/m/d H:i') }}</td>
                        <td>
                            <a
                                href="{{ route('attendance.detail', $request->attendance->date->format('Y-m-d')) }}"
                                class="request-list__detail-link"
                            >
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="request-list__empty">
                            データがありません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
