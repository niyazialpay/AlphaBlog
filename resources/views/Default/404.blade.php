
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <title>404</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <style>
        body{
            background: #000082;
            color:#ffffff;
            font-family: 'lucida-console','Lucida Console', arial, serif ;
        }
        @font-face {
            font-family: lucida-console;
            src: url('/themes/Default/fonts/lucida-console.ttf') format('truetype');
        }
        a{
            color:#ffffff;
            text-decoration: none;
        }
        a:hover{
            color:#8896ff;
        }
        .container{
            margin-top: 15px;
        }
    </style>
</head>
<body>
<audio id="bluescreen" autoplay>
    <source src="/themes/Default/bluescreen.mp3" type="audio/mpeg">
</audio>
<div class="container">
    <div class="row">
        <div class="col-12">
            <p>@lang('404-bluescreen.text_1')</p>
            <p>@lang('404-bluescreen.text_2')</p>
            <p>@lang('404-bluescreen.text_3')</p>
            <p>@lang('404-bluescreen.text_4')</p>
            <p></p>
            <p>@lang('404-bluescreen.text_5')</p>
            <ul>
                <li><a href="{{config('app.url')}}">@lang('home.home')</a></li>
            </ul>
            <p>@lang('404-bluescreen.text_6')</p>
        </div>
    </div>
</div>
</body>
</html>
