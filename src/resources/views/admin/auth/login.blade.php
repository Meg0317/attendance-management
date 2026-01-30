@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
<div class="login-form__content">
    <div class="login-form__heading">
        <h2>管理者ログイン</h2>
    </div>
    <div class="login-form__inner">
        <form class="login-form__form" action="{{ url('/login') }}" method="post">
            @csrf
            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__label--item">メールアドレス</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="email" name="email" value="{{ old('email') }}" />
                    </div>
                    <p class="login-form__error-message">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </p>
                </div>
            </div>

            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__label--item">パスワード</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="password" name="password" />
                    </div>
                    <p class="login-form__error-message">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </p>
                </div>
            </div>

            <div class="form__button">
                <input type="hidden" name="login_type" value="admin">
                <button class="form__button-submit" type="submit">管理者ログインする</button>
            </div>
        </form>
    </div>
</div>
@endsection
