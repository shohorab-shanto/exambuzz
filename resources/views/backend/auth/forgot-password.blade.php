<!DOCTYPE html>
<html lang="zxx">


<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Forgot password - {{ $company->name }}</title>



    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap1.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/font_awesome/css/all.min.css') }}" />


    <style>
        .add-position {
            width: 100%;
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        body {
            margin: 0;
            padding: 0;
            background: url(https://i.ibb.co/VQmtgjh/6845078.png) no-repeat;
            height: 100vh;
            font-family: sans-serif;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            overflow: hidden
        }

        @media screen and (max-width: 600px; ) {
            body {
                background-size: cover;
                : fixed
            }
        }

        #particles-js {
            height: 100%
        }

        .loginBox {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            min-height: 200px;
            background: #000000;
            border-radius: 10px;
            padding: 40px;
            box-sizing: border-box
        }

        .user {
            margin: 0 auto;
            display: block;
            margin-bottom: 20px
        }

        h3 {
            margin: 0;
            padding: 0 0 20px;
            color: #59238F;
            text-align: center
        }

        .loginBox input {
            width: 100%;
            margin-bottom: 20px
        }

        .loginBox input[type="text"],
        .loginBox input[type="password"] {
            border: none;
            border-bottom: 2px solid #262626;
            outline: none;
            height: 40px;
            color: #fff;
            background: transparent;
            font-size: 16px;
            padding-left: 20px;
            box-sizing: border-box
        }

        .loginBox input[type="text"]:hover,
        .loginBox input[type="password"]:hover {
            color: #42F3FA;
            border: 1px solid #42F3FA;
            box-shadow: 0 0 5px rgba(0, 255, 0, .3), 0 0 10px rgba(0, 255, 0, .2), 0 0 15px rgba(0, 255, 0, .1), 0 2px 0 black
        }

        .loginBox input[type="text"]:focus,
        .loginBox input[type="password"]:focus {
            border-bottom: 2px solid #42F3FA
        }

        .inputBox {
            position: relative
        }

        .inputBox span {
            position: absolute;
            top: 10px;
            color: #262626
        }

        .loginBox input[type="submit"] {
            border: none;
            outline: none;
            height: 40px;
            font-size: 16px;
            background: #59238F;
            color: #fff;
            border-radius: 20px;
            cursor: pointer
        }

        .loginBox a {
            color: #262626;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            display: block
        }

        a:hover {
            color: #00ffff
        }

        p {
            color: #0000ff
        }
    </style>
</head>

<body>
    @include('sweetalert::alert', ['cdn' => 'https://cdn.jsdelivr.net/npm/sweetalert2@9'])
    <div class="loginBox"> <img class="user" src="{{ asset($company->logo) }}" style="height: 40px; width: 180px;">
        <h3>Forgot Password!</h3>
        <form action="{{ route('storeForgotPassword') }}" method="post">
            @csrf
            <div class="inputBox">
                <input id="uname" type="text" name="email" placeholder="Email">
            </div>
            <input type="submit" name="" value="Send Reset Password Link">
        </form>
        <a href="{{ route('login') }}">Login with your account?<br> </a>


    </div>

</body>


</html>
