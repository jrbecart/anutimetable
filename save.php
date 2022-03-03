<?php

try
{
  $data = $_POST['jsonString'];
  $term = $_GET['term'];
  if (!empty($data) && !empty($term))
  {
    $term = $_GET['term'];

    $file = '/data/configTableFall.json';
    if ($term == "Winter")
        $file = '/data/configTableWinter.json';

    //set mode of file to writable.
    chmod(dirname(__FILE__). $file,0777);
    $f = fopen(dirname(__FILE__). $file, "w+") or die("fopen failed");
    fwrite($f, $data);
    fclose($f);

    echo json_encode(array(
      'status' => 'success',
      'message'=> 'success message'
    ));
  }
  else{
    echo json_encode(array(
        'status' => 'error',
        'message'=> 'error data empty'
    ));
  }
}
catch ( Exception $e ) {
    // send error message if you can
    echo json_encode(array(
        'status' => 'error',
        'message'=> 'error message'
    ));
}
