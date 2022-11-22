
    <!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
         
        <link rel="icon" type="image/x-icon" href="https://i.ibb.co/hfLLWjZ/ezgif-1-62518d2ae2.png"> 
        <link href="https://fonts.bunny.net/css?family=space-grotesk:300,400,500,600,700" rel="stylesheet" />
        <style>
            *{
            transition: all 0.6s;
            }

            html {
                height: 100%;
                background-color: white;
            }

            body{
                font-family: 'Space Grotesk', sans-serif;
                color: black;
                margin: 0;
            }

            #main{
                display: table;
                width: 100%;
                height: 100vh;
                text-align: center;
            }

            .fof{
                display: table-cell;
                vertical-align: middle;
            }

            .fof h1{
                font-size: 50px;
                display: inline-block;
                padding-right: 12px;
                animation: type .5s alternate infinite;
            }

            .fof h2{
                font-size: 30px;
                display: inline-block;
                padding-right: 12px;
                animation: type .5s alternate infinite;
            }

            @keyframes type{
                from{box-shadow: inset -3px 0px 0px black;}
                to{box-shadow: inset -3px 0px 0px transparent;}
            }

            button {
                display: inline-block;
                outline: 0;
                border: 0;
                cursor: pointer;
                background: #000000;
                color: #FFFFFF;
                border-radius: 8px;
                padding: 14px 24px 16px;
                font-size: 18px;
                font-weight: 700;
                line-height: 1;
                transition: transform 200ms,background 200ms;
                
                &:hover {
                    transform: translateY(-2px);
                }
            }

.form .button, .form .message, .customSelect, .form .select, .form .textarea, .form .text-input, .form .option-input + label, .form .checkbox-input + label, .form .label {
  padding: 0.75em 1em;
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  outline: none;
  line-height: normal;
  border-radius: 0;
  border: none;
  background: none;
  display: block;
}

.form .label {
  font-weight: bold;
  padding-top: 0;
  padding-left: 0;
  letter-spacing: 0.025em;
  font-size: 1.125em;
  line-height: 1.25;
  position: relative;
  z-index: 100;
}
.required .form .label:after, .form .required .label:after {
  content: " *";
  color: #E8474C;
  font-weight: normal;
  font-size: 0.75em;
  vertical-align: top;
}

.customSelect, .form .select, .form .textarea, .form .text-input, .form .option-input + label, .form .checkbox-input + label {
  font: inherit;
  line-height: normal;
  width: 100%;
  box-sizing: border-box;
  background: #222222;
  margin-top: 7px;
  margin-bottom:  25px;
  color: white;
  position: relative;
}
.customSelect:placeholder, .form .select:placeholder, .form .textarea:placeholder, .form .text-input:placeholder, .form .option-input + label:placeholder, .form .checkbox-input + label:placeholder {
  color: white;
}
.customSelect:-webkit-autofill, .form .select:-webkit-autofill, .form .textarea:-webkit-autofill, .form .text-input:-webkit-autofill, .form .option-input + label:-webkit-autofill, .form .checkbox-input + label:-webkit-autofill {
  box-shadow: 0 0 0px 1000px #111111 inset;
  -webkit-text-fill-color: white;
  border-top-color: #111111;
  border-left-color: #111111;
  border-right-color: #111111;
}
.customSelect:not(:focus):not(:active).error, .form .select:not(:focus):not(:active).error, .form .textarea:not(:focus):not(:active).error, .form .text-input:not(:focus):not(:active).error, .form .option-input + label:not(:focus):not(:active).error, .form .checkbox-input + label:not(:focus):not(:active).error, .error .customSelect:not(:focus):not(:active), .error .form .select:not(:focus):not(:active), .form .error .select:not(:focus):not(:active), .error .form .textarea:not(:focus):not(:active), .form .error .textarea:not(:focus):not(:active), .error .form .text-input:not(:focus):not(:active), .form .error .text-input:not(:focus):not(:active), .error .form .option-input + label:not(:focus):not(:active), .form .error .option-input + label:not(:focus):not(:active), .error .form .checkbox-input + label:not(:focus):not(:active), .form .error .checkbox-input + label:not(:focus):not(:active) {
  background-size: 8px 8px;
  background-image: linear-gradient(135deg, rgba(232, 71, 76, 0.5), rgba(232, 71, 76, 0.5) 25%, transparent 25%, transparent 50%, rgba(232, 71, 76, 0.5) 50%, rgba(232, 71, 76, 0.5) 75%, transparent 75%, transparent);
  background-repeat: repeat;
}
.form:not(.has-magic-focus) .customSelect.customSelectFocus, .form:not(.has-magic-focus) .customSelect:active, .form:not(.has-magic-focus) .select:active, .form:not(.has-magic-focus) .textarea:active, .form:not(.has-magic-focus) .text-input:active, .form:not(.has-magic-focus) .option-input + label:active, .form:not(.has-magic-focus) .checkbox-input + label:active, .form:not(.has-magic-focus) .customSelect:focus, .form:not(.has-magic-focus) .select:focus, .form:not(.has-magic-focus) .textarea:focus, .form:not(.has-magic-focus) .text-input:focus, .form:not(.has-magic-focus) .option-input + label:focus, .form:not(.has-magic-focus) .checkbox-input + label:focus {
  background: #4E4E4E;
}

.form .message {
  position: absolute;
  bottom: 0;
  right: 0;
  z-index: 100;
  font-size: 0.625em;
  color: white;
}

.form .option-input, .form .checkbox-input {
  border: 0;
  clip: rect(0 0 0 0);
  height: 1px;
  margin: -1px;
  overflow: hidden;
  padding: 0;
  position: absolute;
  width: 1px;
}
.form .option-input + label, .form .checkbox-input + label {
  display: inline-block;
  width: auto;
  color: #4E4E4E;
  position: relative;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
  cursor: pointer;
}
.form .option-input:focus + label, .form .checkbox-input:focus + label, .form .option-input:active + label, .form .checkbox-input:active + label {
  color: #4E4E4E;
}
.form .option-input:checked + label, .form .checkbox-input:checked + label {
  color: white;
}

.form .button {
  font: inherit;
  line-height: normal;
  cursor: pointer;
  background: #E8474C;
  color: white;
  font-weight: bold;
  width: auto;
  margin-left: auto;
  font-weight: bold;
  padding-left: 2em;
  padding-right: 2em;
}
.form .button:hover, .form .button:focus, .form .button:active {
  color: white;
  border-color: white;
}
.form .button:active {
  position: relative;
  top: 1px;
  left: 1px;
}

body {
  padding: 2em;
}

.form {
  max-width: 40em;
  margin: 0 auto;
  position: relative;
  display: flex;
  flex-flow: row wrap;
  justify-content: space-between;
  align-items: flex-end;
}
.form .field {
  width: 100%;
  margin: 0 0 1.5em 0;
}
@media screen and (min-width: 40em) {
  .form .field.half {
    width: calc(50% - 1px);
  }
}
.form .field.last {
  margin-left: auto;
}
.form .textarea {
  max-width: 100%;
}
.form .select {
  text-indent: 0.01px;
  text-overflow: "" !important;
}
.form .select::-ms-expand {
  display: none;
}
.form .checkboxes, .form .options {
  padding: 0;
  margin: 0;
  list-style-type: none;
  overflow: hidden;
}
.form .checkbox, .form .option {
  float: left;
  margin: 1px;
}
        </style>

    </head>
    <body>
    <div id="main">
      <div class="fof">
        <form class='form' method="post" action="/install/submit">
<!--     'server_ip' => env('WAINWRIGHT_CASINODOG_SERVER_IP', '127.0.0.1'),
    'securitysalt' => env('WAINWRIGHT_CASINODOG_SECURITY_SALT', 'AA61BED99602F187DA5D033D74D1A556'), // salt used for general signing of entry sessions and so on
    'domain' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')),
    'hostname' => env('WAINWRIGHT_CASINODOG_HOSTNAME', '777.dog'),
    'master_ip' => env('WAINWRIGHT_CASINODOG_MASTER_IP', '127.0.0.1'), // this IP should be your personal or whatever your testing on, this IP will surpass the Operator IP check
    'testing' => env('WAINWRIGHT_CASINODOG_TESTINGCONTROLLER', true), //set to false to hard override disable all tests through TestingController. When set to 1 and APP_DEBUG is set to true in .env, you can make use of TestingController
    'cors_anywhere' => env('WAINWRIGHT_CASINODOG_CORSPROXY', 'https://wainwrighted.herokuapp.com/'), //corsproxy, should end with slash, download cors proxy: https://gitlab.com/casinoman/static-assets/cors-proxy

    'wainwright_proxy' => [
      'get_demolink' => env('WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK', true), // set to 1 if wanting to use proxy through cors_anywhere url on game import jobs
      'get_gamelist' => env('WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST', true), // set to 1 if wanting to use proxy through cors_anywhere url on game import jobs
    ],

    'panel_ip_restrict' => env('WAINWRIGHT_CASINODOG_PANEL_IP_RESTRICT', true), //restrict panel access based on ip, you can add allowed ip's in panel_allowed_ips
    'panel_allowed_ips' => explode(',', env('WAINWRIGHT_CASINODOG_PANEL_ALLOWED_IP_LIST', '127.0.0.1')),

    !-->
    @php
      try {
      $ip = file_get_contents('https://api.ipify.org');
      } catch(\Exception $e) {
        $ip = "127.0.0.1";
      }

    @endphp

          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_SERVER_IP</label>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_SERVER_IP' name='WAINWRIGHT_CASINODOG_SERVER_IP' required type='text' value='{{ $ip }}'>
          </p>

          <hr>
          <hr>

          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_SECURITY_SALT</label>
            <small><i>
              salt used to randomize crypto hashes used for signatures, like game entry tokens, your initial admin password and so on
            </i></small>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_SECURITY_SALT' name='WAINWRIGHT_CASINODOG_SECURITY_SALT' required type='text' value='{{ md5(now().rand(100, 200)) }}'>
          </p>

          <hr>
          <hr>

          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_DOMAIN</label>
            <small><i>
              also will be set to your APP_URL, used for example in building API links for game communication
            </i></small>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_DOMAIN' name='WAINWRIGHT_CASINODOG_DOMAIN' required type='text' value='https://www.domain.com'>
          </p>

          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_HOSTNAME</label>
            <small><i>
              hostname should correspond with domain entered above  
            </i></small>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_HOSTNAME' name='WAINWRIGHT_CASINODOG_HOSTNAME' required type='text' value='domain.com'>
          </p>

          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_WILDCARD</label>
            <small><i>
              can enter seperate domain if you wish to make use of wildcard session domain, so a player can enter using "{entrytoken}.domain.com". You can just enter your domain or leave it as it is if you are not going to use this.
            </i></small>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_WILDCARD' name='WAINWRIGHT_CASINODOG_WILDCARD' required type='text' value='.domain.com'>
          </p>


          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_MASTER_IP</label>
            <small><i>
              master IP bypasses the IP check that is done when creating sessions, should probably set to your own IP so it's easier to check operators session creation within the admin panel. Only applicable if you enabled IP restriction setting.
            </i></small>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_MASTER_IP' name='WAINWRIGHT_CASINODOG_MASTER_IP' required type='text' value='{{ request()->DogGetIP() }}'>
          </p>

          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_CORSPROXY</label>
            <small><i>
              This makes us able to bypass cors origin security and functions as a proxy. F.e. used when importing game html from providers. You can find corsproxy setup (nodejs) in github or you can use public proxies:
              'https://cors-4.herokuapp.com/', 'https://wainwrighted.herokuapp.com/', 'https://cors.app-0.casinoman.app/'

              - You should check if it functions, you can check by for example going to 'https://cors-4.herokuapp.com/https://api.ipify.org'
            </i></small>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_CORSPROXY' name='WAINWRIGHT_CASINODOG_CORSPROXY' required type='text' value='https://cors-4.herokuapp.com/'>
          </p


          <p class='field required'>
            <label class='label required' for='name'>WAINWRIGHT_CASINODOG_PANEL_ALLOWED_IP_LIST</label>
            <small><i>
              list of ip's that can enter admin panel, only applicable if WAINWRIGHT_CASINODOG_PANEL_IP_RESTRICT is enabled. IP's should be split with comma.
            </i></small>
            <input class='text-input' id='WAINWRIGHT_CASINODOG_PANEL_ALLOWED_IP_LIST' name='WAINWRIGHT_CASINODOG_PANEL_ALLOWED_IP_LIST' required type='text' value='{{ request()->DogGetIP(), }}, 1.1.1.1'>
          </p>

        <div class='field'>
          <label class='label required'>WAINWRIGHT_CASINODOG_TESTINGCONTROLLER</label>
          <ul class='checkboxes'>
            <li class='checkbox'>
              <input class='option-input' id='WAINWRIGHT_CASINODOG_TESTINGCONTROLLER-0' name='WAINWRIGHT_CASINODOG_TESTINGCONTROLLER' type='radio' value='0'>
              <label class='option-label' for='WAINWRIGHT_CASINODOG_TESTINGCONTROLLER-0'>Disabled</label>
            </li>
            <li class='checkbox'>
              <input class='option-input' id='WAINWRIGHT_CASINODOG_TESTINGCONTROLLER-1' name='WAINWRIGHT_CASINODOG_TESTINGCONTROLLER' type='radio' value='1'>
              <label class='option-label' for='WAINWRIGHT_CASINODOG_TESTINGCONTROLLER-1'>Enabled</label>
            </li>
          </ul>
        </div>



        <div class='field'>
          <label class='label required'>WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK</label>
          <ul class='checkboxes'>
            <li class='checkbox'>
              <input class='option-input' id='WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK-0' name='WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK' type='radio' value='0'>
              <label class='option-label' for='WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK-0'>Disabled</label>
            </li>
            <li class='checkbox'>
              <input class='option-input' id='WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK-1' name='WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK' type='radio' value='1'>
              <label class='option-label' for='WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK-1'>Enabled</label>
            </li>
          </ul>
        </div>


        <div class='field'>
          <label class='label required'>WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST</label>
          <ul class='checkboxes'>
            <li class='checkbox'>
              <input class='option-input' id='WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST-0' name='WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST' type='radio' value='0'>
              <label class='option-label' for='WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST-0'>Disabled</label>
            </li>
            <li class='checkbox'>
              <input class='option-input' id='WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST-1' name='WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST' type='radio' value='1'>
              <label class='option-label' for='WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST-1'>Enabled</label>
            </li>
          </ul>
        </div>
        <p class='field half'>
          <input class='button' type='submit' value='Send'>
        </p>
      </form>
      </div>
    </div>
    </body>
</html>

