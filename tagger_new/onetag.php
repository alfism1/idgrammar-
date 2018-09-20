<?php
// header('Content-Type: application/json');

$kata = $_GET["kata"];

if ($kata=="") {
  echo json_encode(["result"=>"unknown method"]);
} else{
  /* start of : proses tagging seluruh text */
  $file = 'inputText.txt';

  file_put_contents($file, $kata);
  // $command = escapeshellcmd('tagger.py');
  echo shell_exec('python3 tagger.py 2>&1');
  $tag = shell_exec('cat outputText.txt');
  $result = array_filter( explode(PHP_EOL, $tag) );
  // hasil tagging untuk semua text yang dimasukan (array)
  // format setiap elemen : (kata\tTAG)
  /* end of : proses tagging seluruh text */

  $result = explode("\t",$result[0]);

  echo json_encode($result);


}
?>
