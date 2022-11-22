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
            @keyframes type{
                from{box-shadow: inset -3px 0px 0px black;}
                to{box-shadow: inset -3px 0px 0px transparent;}
            }
        </style>

    </head>
    <body>
    <div id="main">
    	<div class="fof">
                <p>@yield('message')</p>
    	</div>
    </div>
    </body>
</html>

