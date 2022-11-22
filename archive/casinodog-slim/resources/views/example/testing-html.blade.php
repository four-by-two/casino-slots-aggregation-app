<!DOCTYPE html>
   <?php
   $url = 'https://google.nl';
   ?>
<html>
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="text-align:center">
   <h1 style="color: rgba(15, 15, 151, 0.839)">
      Tutorialspoint
   </h1>
   <b>
      Once you click the button you will see the document/page title will be changed dynamically.
   </b>
   <p>
      Click on the button: "Tutorialspoint is changed title!"
   </p>
<div id="main-content">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <iframe name="cstage" src="https://gamebeat.com" width="100%" height="100%" id="main-content-iframe" frameborder="0"></iframe>
<script>
    $( "iframe" ).on('load',function() {
        document.title = document.getElementById("main-content-iframe").contentDocument.title;
    });
</script>

   <script defer="" type="text/javascript">
      function switchTitle() {
          document.title = document.getElementById("main-content-iframe").contentDocument.title;

      }
   </script>
   <button onclick="switchTitle()">
      Change Page Title
   </button>
</body>
</html>