<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @section('head')@show
    @stack('style')
</head>

<body>
    <div class="bg-white">
        <x-alert />
        <x-header />
        <div class="sm:max-w-2xl md:container lg:max-w-4xl mx-auto px-4 sm:px-6 py-2">
            <div class="mt-2 sm:p-2">
                @section('content')
                @show
            </div>
        </div>
    </div>
    <script defer src="{{ mix('js/app.js') }}"></script>
    @stack('js')
</body>

</html>