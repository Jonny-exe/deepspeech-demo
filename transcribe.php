<?php
if(empty($_SERVER['CONTENT_TYPE']))
{
 $_SERVER['CONTENT_TYPE'] = "application/x-www-form-urlencoded";
}

header("Access-Control-Allow-Origin: *");

function transcribe($language) {
  $CONDA_ENV_NAME = "deepspeech";
  if ($language == "es") {
    $path_model = "output_graph.pb";
    $path_scorer = "kenlm1.scorer";
  } else {
    $path_model = "deepspeech-0.9.3-models.pbmm";
    $path_scorer = "deepspeech-0.9.3-models.scorer";
  }
  $path_audio = "test.wav";

  $command = "conda run -n $CONDA_ENV_NAME deepspeech --model $path_model --scorer $path_scorer --audio $path_audio --json";
  $json_transcribe = system($command);
  $map_transcribe = json_decode($json_transcribe);
  return $map_transcribe;
}
function return_error($message) {
    echo json_encode(array("transcribed" => $message));
}

function write_file($file_contents, $filename) {
  $file = fopen($filename, "wb");
  if ($file === false) {
    return_error("Error opening audio file");
  }

  fwrite($file, $file_contents);
  fclose($file);
}

if ($_POST['file'] && $_POST['language']) {
  $file_contents = $_POST['file']; 
  $language = $_POST['language'];
} else {
  return_error("Parameters are missing");
  exit();
}


$FILENAME = "test.wav";
write_file($file_contents, $FILENAME);

//$transcribed = transcribe($language);
$transcribed = array("text" => "Hello");
$json_response = json_encode($transcribed);
//echo $json_response;
header("Location: http://localhost:3000/?transcribed=".$transcribed["text"]);
exit();

?>
