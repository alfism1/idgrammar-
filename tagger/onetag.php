<?php
// header('Content-Type: application/json');

$kata = $_GET["kata"];

if ($kata=="") {
  echo json_encode(["result"=>"unknown method"]);
} else{
  /* start of : proses tagging seluruh text */
  $file = 'outputs/res-[001]-input.txt';

  file_put_contents($file, $kata);
  $tag = shell_exec('perl NER.pl -f=[001];cat ./outputs/res-[001]-resolved.txt');
  $result = array_filter( explode(PHP_EOL, $tag) );
  // hasil tagging untuk semua text yang dimasukan (array)
  // format setiap elemen : (kata\tTAG)
  /* end of : proses tagging seluruh text */

  $result = explode("\t",$result[0]);

  echo json_encode($result);


  // /* start of : memecah array $result ke bagian lebih detail */
  // $words = array();
  // $tags = "";
  // $fulltag = "";
  // for ($i=0; $i < count($result); $i++) {
  //
  //   $word = explode("\t",$result[$i]);
  //   if ($word[0] != "" && $word[1] != "") { // karena terkadang ada yang kosong
  //     $words[$i] = array("kata"=>$word[0], "tag"=>$word[1]);
  //     $tags .= trim($word[1])."+";
  //     $fulltag .= "~wd".trim($word[0])."wd~~tg".trim($word[1])."tg~ ";
  //   }
  //
  // }
  // $tags = rtrim($tags,"+");
  // /* end of : memecah array $result ke bagian lebih detail */
  //
  //
  //
  // /* start of : hasil akhir */
  // $tagresult = ["text" => $text,"result" => $words, "tag" => $tags, "fulltag" => trim($fulltag)];
  // // echo json_encode($tagresult);
  // /* end of : hasil akhir */
}
?>
