@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('content')
<div class="staff-list">
    <div class="staff-list__inner">

        <h2 class="content__heading">スタッフ一覧</h2>

        <div class="staff-list__content">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($staffs as $staff)
                        <tr>
                            <td class="staff-table__center">
                                {{ $staff->name }}
                            </td>

                            <td class="staff-table__center">
                                {{ $staff->email }}
                            </td>

                            <td class="staff-table__detail">
                                <div class="staff-table__detail-inner">
                                    <a href="{{ route('admin.attendance.staff', ['user' => $staff->id]) }}">
                                        詳細
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
