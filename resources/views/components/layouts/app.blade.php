<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Distribusi MBG') }}</title>
    </head>
    <body>
        <main style="font-family: Arial, sans-serif; max-width: 960px; margin: 48px auto; padding: 0 24px;">
            @auth
                <nav style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                    <div>
                        <strong>{{ config('app.name', 'Distribusi MBG') }}</strong>
                        <span style="color: #64748b;">/ {{ auth()->user()->role?->display_name }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </nav>
            @endauth

            @if (session('status'))
                <div style="padding: 12px 16px; background: #dcfce7; border: 1px solid #16a34a; margin-bottom: 24px;">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
