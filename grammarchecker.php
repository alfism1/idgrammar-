<?php
function grammar($teks){
	$wordToken = explode(" ",$teks);
	return
		"	<span class='error' data-status='Error' data-correction='satu,dua,tiga'>".$wordToken[0]." ".$wordToken[1]."</span>".
		" <span class='error' data-status='Error' data-correction='salah,tidak benar'>".$wordToken[2]."</span>".
		" <span class='warning' data-status='Warning' data-text='$wordToken[1]'>".$wordToken[1]."</span>".
		" <span class='warning' data-status='Warning' data-text='$wordToken[3]'>".$wordToken[3]."</span>";
}
function posTagging($teks){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://localhost/idgrammar/tagger/tag.php",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"text\"\r\n\r\n$teks\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
	  CURLOPT_HTTPHEADER => array(
	    "Cache-Control: no-cache",
	    "Postman-Token: 9a51a412-c4db-451d-8cbd-f1dde949bb31",
	    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  $response = json_decode($response);
		return $response;
		// echo "<pre>";
		// print_r($response);
	}
}
function oneTag($kata){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://localhost/idgrammar/tagger/onetag.php?kata=".$kata,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "Cache-Control: no-cache",
	    "Postman-Token: f688a5c6-93c9-48c9-a8da-507c9516cd95"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
		$response = json_decode($response);
		return $response[1];	// 0 : kata, 1 : tag
	}
}
function spellchecker($kata){
	$file = 'spelling-kata.txt';
  file_put_contents($file, $kata);
  $tag = shell_exec('python3 spelling.py');

	return $tag;

}


function idgrammar($teks){
	include "rules.php";
	include "rule-sp.php";
	include "katabaku.php";

	// tokenizing menjadi per-kalimat
	// https://stackoverflow.com/questions/11758465/preg-split-how-to-include-the-split-delimiter-in-results
	// $kalimat = preg_split('/([^.!?]+[.!?]+)/', $teks, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	// // https://stackoverflow.com/questions/6769464/how-can-i-trim-all-strings-in-an-array
	// $kalimat = array_map('trim', $kalimat);
	// for ($i=0; $i < count($kalimat); $i++) {
	// 	$tags[$i] = posTagging($kalimat[$i]);
	// }

	// postagging
	$tagging = posTagging($teks);

	// $basictext = $tagging->text;

	// $word = explode(" ", $fulltext);
	// $tag = explode("+", $tagging->tag); // struktur kalimat dari input
	// $tag = array_map('trim', $tag);	// menghilangkan spasi awal dan akhir setiap tag
	$myfile = fopen("spelling-katadasar.txt", "r") or die("Unable to open file!");
	$katadasar = explode(PHP_EOL,fread($myfile,filesize("spelling-katadasar.txt")));
	// echo "<pre>";
	// print_r($katadasar);
	// echo "<pre>";
	fclose($myfile);

	$totalerror = 0;
	$fulltag = $tagging->fulltag;

	// tokenizing menjadi per-kalimat
	// https://stackoverflow.com/questions/11758465/preg-split-how-to-include-the-split-delimiter-in-results
	$kalimat = preg_split('/([^.!?]+[.!?]+)/', $fulltag, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	// https://stackoverflow.com/questions/6769464/how-can-i-trim-all-strings-in-an-array
	$kalimat = array_map('trim', $kalimat);
	$klmt = $kalimat;	// cuma backup niilai awal kalimat
	// echo "<pre>";
	// print_r($kalimat);
	// echo "</pre>";
	$idrule = [];

	// variabel untuk menampung semua hasil
	$finaltext = "";

	// looping semua kalimat
	for ($i=0; $i < count($kalimat); $i++) {

		// ========================================= rule tata bahasa =========================================
		foreach ($ruleTataBahasa as $r) {

			if (preg_match($r["pattern"], $kalimat[$i])) {
				preg_match_all($r["pattern"], $kalimat[$i], $matches, PREG_OFFSET_CAPTURE);

				foreach($matches[0] as $match){ // We cycle through the matches
					$tatab[$i][] = $match[0]." <-> "." Rule: ".$r["pattern"];	// untuk tampil pengujian
					// $idrule[$i][] = $r["id"];
					if(!in_array($r["id"], $idrule, true)){
			        array_push($idrule, $r["id"]);
			    }

					if ($r["kode"] != "") {
						if ($r["kode"]=="RTB001") {	// pemisahan kata di dan kata benda/tempat : dikantor,didapur
							$kata = cleanWord($match[0]); // dari ~wd(sangat)wd~~tgRBtg~ : sangat

							// if (!preg_match($r["pengecualian"][0],$kata)) {
							if (!in_array($kata,$katadasar) && !preg_match($r["pengecualian"][0],$kata)) {

								$afterDi = substr($kata, 2);	// mengambil kata setelah di...
								$tagDi = oneTag($afterDi);		// mengambil tag setelah kata di...

								if ($tagDi != "VB") {	// jika setelah kata di bukan kata kerja
									$totalerror += 1;
									$koreksi = "di ".$afterDi;
									$kalimat[$i] = addHtml2($kalimat[$i], $match[0], $koreksi, $r["pesan"], $r["status"]);
								}

							}
						}
						if ($r["kode"]=="RTB002") {	// typo

							// echo "<pre>";
							// print_r($baku);
							// echo "</pre>";

							$kata = cleanWord($match[0]); // dari ~wd(sangat)wd~~tgRBtg~ : sangat

							if (!preg_grep( "/^($kata)$/i", $tidakbaku) && !preg_grep( "/^($kata)$/i", $baku)) {
								$totalerror += 1;
								$koreksi = trim(spellchecker($kata));
								$kalimat[$i] = addHtml2($kalimat[$i], $match[0], $koreksi, $r["pesan"], $r["status"]);
							}
						}
						if ($r["kode"]=="RTB003") {	// akhiran lah
							$totalerror += 1;
							$kata = cleanWord($match[0]); // dari ~wd(sangat)wd~~tgRBtg~ : sangat


							$koreksi = str_replace(" ","",$kata);
							$kalimat[$i] = autoCorrect($kalimat[$i], $match[0], $koreksi);
						}
						if ($r["kode"]=="RTB004") {	// akhiran pun
							$kata = cleanWord($match[0]); // dari ~wd(sangat)wd~~tgRBtg~ : sangat


							if (!preg_match($r["pengecualian"][0],$kata)) {
								// $totalerror += 1;
								$kataAsli = str_replace("pun","",$kata);
								$koreksi = $kataAsli." pun";
								$kalimat[$i] = addHtml2($kalimat[$i], $match[0], $koreksi, $r["pesan"], $r["status"]);

							}
						}
						if ($r["kode"]=="RTB005") {	// pemisahan kata ke dan kata benda/tempat : kekantor
							$kata = cleanWord($match[0]); // dari ~wd(sangat)wd~~tgRBtg~ : sangat

							if (!preg_grep( "/^($kata)$/i", $katadasar) && !preg_match($r["pengecualian"][0],$kata)) {
								$totalerror += 1;
								$afterKe = substr($kata, 2);	// mengambil kata setelah ke...
								$koreksi = "ke ".$afterKe;
								$kalimat[$i] = addHtml2($kalimat[$i], $match[0], $koreksi, $r["pesan"], $r["status"]);
							}
						}
					}
					else{

						if(count($r["pengecualian"]) > 0){	// jika ada pengecualian dalam sebuah rule
							$deteksiPengecualian = false;
							// $matchPengecualian = $match[0];	//
							for ($iPengecualian=0; $iPengecualian < count($r["pengecualian"]); $iPengecualian++) {	// loop semua pengecualian
								// preg_match($r["pengecualian"][$i], $match[0], $m);
								if(preg_match($r["pengecualian"][$iPengecualian], $match[0])){	// jika mendeteksi pengecualian
									$deteksiPengecualian = true;	// ubah status menjadi true
									break;												// berhenti dari  looping
								}
							}

							// echo $deteksiPengecualian;
							if (!$deteksiPengecualian) {	// status akan berupa jadi true jika masuk kondisi if diatas
								goto prosesKesalahan;			// jika status true, maka tidak masuk prosesKesalahan
							}

						} else{
							prosesKesalahan:
							$totalerror += 1;
							// $kata = cleanWord($match[0]);

							// $pattKoreksi =

							$string = $match[0];
							$pattern = $r["pattern"];
							$replacement = $r["koreksi"];

							// $koreksi = koreksi($kata, $r["koreksi"]);
							$koreksi = koreksi2($string, $pattern, $replacement);

							// echo $koreksi;
							$kalimat[$i] = addHtml2($kalimat[$i], $match[0], $koreksi, $r["pesan"], $r["status"]);
						}

						// $koreksi = koreksi($kata, $r["koreksi"]);
						// $addHtml = addHtml($kata,$koreksi,$r["pesan"],$r["status"]);	//$kata, $koreksi, $pesan
						// $fulltext = str_replace($kata, $addHtml, $fulltext);

					}

			  }
			}

		}
		$fulltext = cleanWord($kalimat[$i]);
		// ========================================= /rule tata bahasa =========================================




		// ========================================= kata baku =========================================
		$fulltext = test($fulltext);
		$token[] = $fulltext;		// untuk menampilkan ~token~ saat pengujian
		// echo "<pre>";
		// print_r($fulltext);
		// echo "</pre>";
		foreach ($kataBaku as $t) {
			if (preg_match($t["pattern"], $fulltext)) {
				$textLen = strlen($fulltext);
				preg_match_all($t["pattern"], $fulltext, $matches, PREG_OFFSET_CAPTURE);

				foreach($matches[0] as $match){ // We cycle through the matches
					// echo $match[0];
					$totalerror += 1;

					$pos = (strlen($fulltext)-$textLen)+$match[1];
					$fulltext = trim(addHtmlTT2($fulltext, $match[0], $pos, $t["koreksi"], "Kata tidak baku.", "error4"));

					// $fulltext = addHtml2($fulltext, $match[0], $t["koreksi"], "Kata tidak baku.", "error4");
				}
			}

		}
		$fulltext = cleanToken($fulltext);
		// ========================================= /kata baku =========================================




		// ========================================= rule tata tulis =========================================
		foreach ($ruleTataTulis as $r) {

			if (preg_match($r["pattern"], $fulltext)) {
				$textLen = strlen($fulltext);
				preg_match_all($r["pattern"], $fulltext, $matches, PREG_OFFSET_CAPTURE);

			  $totalerror += count($matches[0]);
				// $pos = 0;
				foreach($matches[0] as $match){ // We cycle through the matches
					$tatat[$i][] = $match[0]." <-> "." Rule: ".$r["pattern"];	// untuk tampil pengujian
					// $idrule[$i][] = $r["id"];
					if(!in_array($r["id"], $idrule, true)){
			        array_push($idrule, $r["id"]);
			    }

					if ($r["kode"] != "") {
						if ($r["kode"]=="RTT001") {	// tanda titik diakhir karakter

							// hanya berlaku pada satu kalimat.
							// $word = explode(" ",$basictext);

							// if ($word[0] == "Apa" || $word[0] == "Siapa" || $word[0] == "Di mana" || $word[0] == "Kapan" || $word[0] == "Bagaimana") {
							// 	$koreksi = "?";
							// }
							// else{
							// 	$koreksi = ".";
							// }
							$koreksi = ".";

							// $addHtml = "<span class='error' data-status='Error' data-correction='".$koreksi."'>&nbsp;</span>";
							$addHtml = addHtml("&nbsp;",$koreksi,$r["pesan"],$r["status"]);	//$kata, $koreksi, $pesan

							$fulltext .= " ";
							$fulltext = substr_replace($fulltext, $addHtml, strlen($fulltext)-1, 0);
						}

						else if ($r["kode"]=="RTT002") {	// koma sebelum tanda "

							$koreksi = ", ";

							// $addHtml = "<span class='error' data-status='Error' data-correction='".$koreksi."'>$match[0]</span>";
							$addHtml = addHtml($match[0],$koreksi,$r["pesan"],$r["status"]);	//$kata, $koreksi, $pesan
							$fulltext = substr_replace($fulltext, $addHtml, $match[1], 1);
						}

						else if ($r["kode"]=="RTT003") {	// tanda titik diakhir karakter "
							// print_r($match);

							$koreksi = strtoupper($match[0]);
							// $addHtml = "<span class='error' data-status='Error' data-correction='".$koreksi."'>$match[0]</span>";
							$addHtml = addHtml($match[0],$koreksi,$r["pesan"],$r["status"]);	//$kata, $koreksi, $pesan

							$fulltext = substr_replace($fulltext, $addHtml, $match[1], 1);
						}
					}
					else{
						if (count($r["pengecualian"]) < 1) {	// jika tdk ada pengecualian dalam sebuah rule
							$kata = cleanWord($match[0]); // dari ~wd(sangat)wd~~tgRBtg~ : sangat

						} else if(count($r["pengecualian"]) > 0){	// jika ada pengecualian dalam sebuah rule
							if(!preg_match($r["pengecualian"][0], $match[0])){	// jika tidak sesuai dengan pengecualian
								$kata = cleanWord($match[0]);
								// $deteksiSalah = true;
							}
						}

						$koreksi = koreksiTataTulis($kata, $r["pola_salah"], $r["koreksi"]);

						// $addHtml = "<span class='error' data-status='Error' data-correction='".$koreksi."'>".$kata."</span>";
						// $addHtml = addHtml($kata,$koreksi,$r["pesan"],$r["status"]);	//$kata, $koreksi, $pesan
						// $fulltext = str_replace($kata, $addHtml, $fulltext);

						$pos = (strlen($fulltext)-$textLen)+$match[1];

						$fulltext = trim(addHtmlTT2($fulltext, $match[0], $pos, $koreksi, $r["pesan"], $r["status"]));


					}

			  }
			}

		}
		// check jika pengecekan diatas tidak mengandung kesalahan
		if (strpos($fulltext, '<span') === false) {
			// mencari subjek dalam kalimat
			$subjek = false;
			foreach ($ruleSubject as $r) {
				if (preg_match($r, $klmt[$i])) {
					preg_match_all($r, $klmt[$i], $matches, PREG_OFFSET_CAPTURE);
					$subjek = true;	// subjek ada
					$subjekPosisiAwal = $matches[0][0][1];	// posisi awal string subjek
					$subjekPosisiAkhir = strlen($matches[0][0][0]);	// posisi akhir string subjek
				}
			}

			// mencari predikat
			$predikat = false;
			foreach($rulePredikat as $p){
				if (preg_match($p, $klmt[$i])) {
					preg_match_all($p, $klmt[$i], $matches, PREG_OFFSET_CAPTURE);
					$predikat = true;	// predikat ada
					$predikatPosisiAwal = $matches[0][0][1];	// posisi awal string predikat
					$predikatPosisiAkhir = strlen($matches[0][0][0]);	// posisi akhir string predikat
				}
			}

			if(!$subjek || !$predikat){
				$fulltext = trim( addHtml2($fulltext, $fulltext, null, "Perhatikan struktur subjek dan predikat", "error1") );
			}


		}

		$finaltext .= $fulltext." ";
		// ========================================= /rule tata tulis =========================================
	}

	// echo "<pre>";
	// print_r(($tagging->fulltag));
	// echo "</pre>";

	// return $fulltext;
	
	$finalresult = [
		"fulltext" => $finaltext,
		"totalerror" => $totalerror,
		"tag" => $tagging,
		"kalimat" => $klmt,
		"tatab" => $tatab,
		"tatat" => $tatat,
		"token" => $token,
		"id" => $idrule,
	];
	return $finalresult;
}

function test($text){
	$test = explode(" ",$text);

	$error = 0;
	for ($i=0; $i < count($test); $i++) {

		if (strpos($test[$i], '<span') !== false) {
			$error = 1;
		}
		else if(strpos($test[$i], '</span>') !== false){
			$error = 0;
			continue;
		}

		if ($error==0) {
			$test[$i] = "~token".$test[$i]."token~";
		}
		// $test[$i] = $test[$i]." ==== ".$error;


	}

	$test = implode(" ",$test);

	return $test;
}

function cleanToken($string){

	$string = str_replace("~token","",$string);	// hapus karakter ~token
	$string = str_replace("token~","",$string);	// hapus karakter token~

	return $string;
}

function cleanWord($string){
	$kata = preg_replace("/~tg(.+?)tg~/i", "", $string);	// hapus karakter ~tg
	$kata = str_ireplace("~wd","",$kata);	// hapus karakter ~wd
	$kata = str_ireplace("wd~","",$kata);	// hapus karakter wd~

	$kata = preg_replace("/\s+(\.)/", ".", $kata);	// menghilangkan spasi di string (spasi)(titik)
	$kata = preg_replace("/\s+(\,)/", ",", $kata);	// menghilangkan spasi di string (spasi)(koma)
	$kata = preg_replace("/\s+(\!)/", "!", $kata);	// menghilangkan spasi di string (spasi)(seru)
	$kata = preg_replace("/\s+(\?)/", "?", $kata);	// menghilangkan spasi di string (spasi)(tanya)

	$kata = preg_replace("/\"\s(?=[A-z])/", "\"", $kata);	// menghilangkan spasi di string (petik dua)(spasi)

	return $kata;
}

function koreksi($kata, $koreksi, $pola_salah = ""){

	if (strpos($koreksi, '%') !== false) {
		// di(0) makan(1)
		// %s0%s1
		// dimakan

		$patternNumber = preg_replace('/[^0-9\%]/', '', $koreksi);	// ambil karakter angka dan % saja
		$patternNumber = trim(str_replace("%"," ",$patternNumber));	//  ambil angka saja dan ubah % ke spasi sebagai pembatas

		$k = explode(" ",$kata);
		$p = explode(" ",$patternNumber);

		for ($i=0; $i < count($k); $i++) {
			$w[$i] = $k[$p[$i]];
		}

		$koreksi = (preg_replace('/[0-9]/', '', $koreksi));		// hapus karakter angka
		return vsprintf($koreksi, $w);
	}

}

function koreksi2($string, $pattern, $replacement){	// koreksi versi 2
	if (gettype($replacement)=="object") {
		preg_match($pattern, $string, $m);
		$m = cleanWord($m[0]);
		// http://php.net/manual/en/function.call-user-func.php
		return call_user_func($replacement, $m);
		// echo (preg_replace_callback($pattern, $replacement, $string));
	} else{
		return cleanWord(preg_replace($pattern, $replacement, $string));
	}

}

function koreksiTataTulis($kata, $pola_salah, $koreksi){
	if (gettype($koreksi)=="object") {
		//http://php.net/manual/en/function.preg-replace-callback.php
		return preg_replace_callback($pola_salah, $koreksi, $kata);
	} else{
		return preg_replace($pola_salah, $koreksi, $kata);
	}

}

function addHtml($kata, $koreksi, $pesan, $status){
	return "<span class='$status' data-id='".rand()."' data-status='Error' data-correction='".$koreksi."' data-message='".$pesan."'>".$kata."</span>";
}

function addHtml2($fulltag, $match, $koreksi, $pesan, $status){	// versi 2
	$start = strpos($fulltag, $match);
	$kata = cleanWord($match);
	$addHtml = "<span class='".$status."' data-id='".rand()."' data-status='Error' data-correction='".$koreksi."' data-message='".$pesan."'>".$kata."</span>";
	$fulltag = substr_replace($fulltag, $addHtml, $start, strlen($match));

	return $fulltag;
}

function autoCorrect($fulltag, $match, $koreksi){	// versi 2
	$start = strpos($fulltag, $match);
	$kata = cleanWord($match);
	$fulltag = substr_replace($fulltag, $koreksi, $start, strlen($match));

	return $fulltag;
}

function addHtmlTT2($fulltag, $match, $pos, $koreksi, $pesan, $status){	// versi 2 untuk tata tulis
	// $start = strpos($fulltag, $match);
	$addHtml = "<span class='".$status."' data-id='".rand()."' data-status='Error' data-correction='".$koreksi."' data-message='".$pesan."'>".$match."</span>";
	$fulltag = substr_replace($fulltag, $addHtml, $pos, strlen($match));

	return $fulltag;


}

?>
