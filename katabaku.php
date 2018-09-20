<?php
$katabaku = file_get_contents("daftarkatabaku.txt");
$katabaku = explode(PHP_EOL, $katabaku);
$katabaku = array_filter($katabaku);

// echo "<pre>";
// print_r($katabaku);

$i = 0;
while ($i < count($katabaku)) {
  $k = explode("|", $katabaku[$i]);

    $kataBaku[] = [
      "pattern" => "/(?<=~token)$k[1](?=token~)|(?<=~token)$k[1](?=(\.)token~)/i",
      "koreksi" => " $k[0]",
    ];

    $baku[] = $k[0];
    $tidakbaku[] = $k[1];

  $i++;
}

// echo "<pre>";
// print_r($kataBaku);


// include "koneksi.php";
//
//
// // tampilkan data
// $result = mysqli_query($link, "SELECT DISTINCT(lower(kata_baku)) AS kata_baku, (lower(tidak_baku)) AS tidak_baku FROM kata_tidak_baku ORDER BY kata_baku");
// while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
//
//   echo trim($row["kata_baku"])."|".trim($row["tidak_baku"])."<br>";
//
//   // $kataBaku[] = [
//   //   "pattern" => "/(?<=~token)$row[pattern](?=token~)|(?<=~token)$row[pattern](?=(\.)token~)/i",
//   //   "koreksi" => " $row[koreksi]",
//   // ];
//
//   // $baku[] = $row["koreksi"];
//   // $tidakbaku[] = $row["pattern"];
// }
//
//
//
// // ecmysqli_close($link);
