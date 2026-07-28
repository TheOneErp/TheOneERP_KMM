@extends('layouts.html')

@section('title') 登入 @endsection

@section('css')
<style>

</style>
@endsection

@section('body')
<div class="ts fluid vertically slate" style="height: 100%;background-image: url('20211108-燈箱底圖-雲量.jpg');background-size:auto 100%   ; background-position:center bottom ; background-repeat: no-repeat;">
    <!-- Title -->
    {{-- <h1 class="ts center aligned header">{{ env("APP_NAME") }}</h1> --}}
    <h2 class="ts center aligned header" id="loginLabel">登入</h2>
    {{-- <h5 class="sub header">請輸入您的帳號以及密碼</h5> --}}

    <!-- Login Form Card -->
    <div class="ts positive card">
        <div class="content">
            <div class="description">
                <form class="ts form" method="POST" action="{{ route("system.auth.login.post") }}">
                    @csrf

                    @if ($errors->any())
                    <div class="ts icon inverted negative message">
                        <i class="caution sign icon"></i>
                        @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                        @endforeach
                    </div>
                    @endif

                    <div class="field">
                        <label id="usernameLabel">帳號</label>
                        <input type="text" name="username" value="{{ old("username") }}">
                    </div>

                    <div class="field">
                        <label id="passwordLabel">密碼</label>
                        <input type="password" name="password" value="{{ old("password") }}">
                    </div>

                    <div class="field">
                        <label {{-- class="spanClass" --}} id="languagesLabel" for="username">語言</label>
                        <select id="languages" name="languages" disabled>
                        </select>
                    </div>

                    <div class="field">
                        <div class="ts checkbox">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember" id="rememberLabel">記住我</label>
                        </div>
                    </div>

                    <button id="login" class="ts button" disabled>登入</button>
                </form>
            </div>
        </div>
    </div>
    <img src="雲量LOGO1.png" style="width: 275px;">
</div>
@endsection

@section("footer")
<script>
    $.ajax({
        url:"{{route('system.auth.translation.get')}}",
        method: 'GET',
        success: (data) => {
            // data = JSON.parse(data)
            let languages = data.languages
            let languagesData = data.translation

            let languageSelect = document.getElementById("languages")
            let loginLabel = document.getElementById("loginLabel")
            let usernameLabel = document.getElementById("usernameLabel")
            let passwordLabel = document.getElementById("passwordLabel")
            let languageLabel = document.getElementById("languagesLabel")
            let remrmberMeLabel = document.getElementById("rememberLabel")
            let loginButton = document.getElementById("login")

            for(let language of languages){
                let option = document.createElement("option")
                option.value = language.language_id
                option.text = language.language_name
                languageSelect.add(option)
            }

            languageSelect.addEventListener('change', () => {
                let newLanguageID = languageSelect.value
                let newTranslation = languagesData[newLanguageID]
                loginLabel.innerText = newTranslation.login
                usernameLabel.innerText = newTranslation.username
                passwordLabel.innerText = newTranslation.password
                languageLabel.innerText = newTranslation.language
                remrmberMeLabel.innerText = newTranslation.rememberme
                loginButton.innerText =  newTranslation.login
            })

            languageSelect.value = "{{ old('languages','1')}}"
            languageSelect.dispatchEvent(events.Change)

            languageSelect.disabled = false
            loginButton.disabled = false
        }
    })

</script>
@endsection
