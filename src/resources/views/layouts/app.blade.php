<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance-management</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>


<body>
    <header class="header">
        <div class="header__inner">
            <a href="/">
                <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECK">
            </a>
            <nav class="header-nav">
                @auth
                    <ul class="header-nav__list">
                        <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                        <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('stamp_correction_request.list') }}">申請</a></li>
                    </ul>

                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="header-nav__button">ログアウト</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>
</body>

</html>