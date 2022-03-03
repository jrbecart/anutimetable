<?php

header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Pragma: no-cache');
?>

<!DOCTYPE html>

<html lang="en">

    <head>

        <title>uOttawa Science TimeTable Configuration Editor</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/jquery.contextMenu.min.css">
        <style>
        body { background-color: #fafafa; }
        #table_to_json_btn {
          right: 20px;
          width: 300px;
          bottom: 20px;    
          position: fixed;
        }

    </style>

    </head>
    <body>
    <div class="container">
<h1>TimeTable Configuration Editor (FALL)</h1>


    <div class="row mt-3">
       <div id='table_container' class='col-md-12'></div>
    </div>

    <div class="row mt-3">
        <div class='col-md-4'></div>
        <div id='button_container' class='col-md-3'>
                <button id='table_to_json_btn' type="button" class="btn btn-default btn-lg btn-block">Save Configuration</button>
                <button id='new_line' type="button" class="btn btn-default btn-lg btn-block">Add new line</button>
        </div>
    </div>

    <div class="row mt-3">
        <div id='table_container' class='col-md-12'></div>
    </div>
    
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/jquery.contextMenu.min.js"></script>
    <script src="js/jsoneditor.js"></script>
    <script>

        $(document).ready(function () {

            jsonEditorInit('table_container', 'Textarea1', 'result_container', 'json_to_table_btn', 'table_to_json_btn', 'new_line', 'Fall');

        });

    </script>

</div>

</body>
</html>
