<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @yield('head')
    @stack('style')
</head>

<body>
    @yield('content')
    <script defer src="{{ mix('js/app.js') }}"></script>
    @stack('js')
</body>

</html>