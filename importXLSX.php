<?php

try
{
    $term = $_POST['term'];

    if($term === "fall")
      $term = "fall";
    else
      $term = "winter";

    move_uploaded_file($_FILES['file']['tmp_name'], "./data/xlsx-$term.xlsx" );

    $term=escapeshellarg($term);
    $output = shell_exec("php ./data/simplexml.php $term");

    if($output == 1)
      echo json_encode(array(
        'status' => 'success',
        'message'=> 'success message'
      ));
    else
      echo json_encode(array(
          'status' => 'error',
          'message'=> "error message <pre>$output</pre>"
      ));

} catch ( Exception $e ) {
    // send error message
    echo json_encode(array(
        'status' => 'error',
        'message'=> "error message: " . $e->getMessage()
    ));
}
