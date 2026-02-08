<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance-management（管理者）</title>

    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a href="{{ route('admin.attendance.list') }}">
                <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH">
            </a>

            <nav class="header-nav">
                <ul class="header-nav__list">
                    <li>
                        <a href="{{ route('admin.attendance.list') }}">
                            勤怠一覧
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.staff.list') }}">
                            スタッフ一覧
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.stamp_correction_request.list') }}">
                            申請一覧
                        </a>
                    </li>
                </ul>

                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="header-nav__button">ログアウト</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>
</html>
