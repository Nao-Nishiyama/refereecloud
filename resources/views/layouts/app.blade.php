<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Referee Cloud') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="/favicon.png?v=3">
    
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('logo.png') }}"
                        alt="Referee Cloud"
                        width="48"
                        height="48"
                        class="me-2"> {{ config('app.name', 'Referee Cloud') }}
                </a>

                <button class="navbar-toggler border-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars fs-3"></i>
                </button>
                    
                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('ログイン') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('新規登録') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" v-pre>
                                    @if (Auth::user()->referee)
                                        {{ strtoupper(Auth::user()->referee->surname) }} {{ Auth::user()->referee->name }}
                                    @else
                                        {{ Auth::user()->surname_kana }} {{ Auth::user()->name_kana }}
                                    @endif
                                        <i class="fa-solid fa-bars fs-5 ms-2"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    
                                    <a href="{{ route('competitions.show') }}" class="dropdown-item">
                                        大会一覧
                                    </a>

                                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                                        アカウント情報
                                    </a>

                                    <a href="{{ route('reports.show', Auth::user()->id) }}" class="dropdown-item">
                                        活動報告
                                    </a>                                        
                                    <hr>

                                    @canany(['admin', 'committee', 'chief'])
                                    
                                        <a href="{{ route('admin.competitions') }}" class="dropdown-item">
                                            大会情報
                                        </a>
                                        
                                        <a href="{{ route('admin.referees')}}" class="dropdown-item">
                                            審判員管理
                                        </a>
                                    
                                    @endcanany

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @guest
        @else
        @endguest
        <br>
        <main class="py-4">
            @yield('content')
        </main>
    </div>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('scripts') 

</body>
</html>
