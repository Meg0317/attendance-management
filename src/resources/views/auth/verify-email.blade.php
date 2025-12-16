@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')

<div class="verify-container">
    <div class="text">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください。</p>
    </div>

    <div class="authentication-area">
        <a href="http://localhost:8025" target="_blank" class="auth-button">
            認証はこちらから
        </a>
    </div>

    <div class="resend-area">
        <form action="{{ route('verification.send') }}"  method="post">
        @csrf
            <button class="form__button-submit" type="submit">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection