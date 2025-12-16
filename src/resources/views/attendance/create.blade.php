@extends('layouts.app') {{-- or layouts.auth どちらでもOK --}}

@section('content')
<div style="padding: 40px;">
    <h1>出勤打刻画面</h1>

    <p>ここはログイン＆メール認証後のみ表示されます。</p>

    <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <button type="submit">出勤</button>
    </form>
</div>
@endsection