<?php
	@$adres_ip = $_POST["adres_ip"];
	(int)$aip_parts = explode('.', $adres_ip);
	$err_adres_ip;
	//--------
	@$maska = $_POST["maska"];
	(int)$maska_parts = explode('.', $maska);
	//--------
	@$podsieci_ilosc = $_POST["podsieci"];
	@$podsieci2_ilosc = $podsieci_ilosc - 1;
	$podsieci_ilosc_bin = decbin($podsieci2_ilosc);
	$podsieci; //tablica wszystkich podsieci
	//--------
	//detekcja błędów - adres ip - start
	try{
	if(isset($aip_parts[4])){
		$err_adres_ip = "Błędny adres IP - za długi adres";
	} else if (!isset($aip_parts[0]) || !isset($aip_parts[1]) || !isset($aip_parts[2]) || !isset($aip_parts[3])) {
		$err_adres_ip = "Błędny adres IP - za krótki adres";
	} else if ($aip_parts[0] > 255 || $aip_parts[1] > 255 || $aip_parts[2] > 255 || $aip_parts[3] > 255) {
		$err_adres_ip = "Błędny adres IP - Zbyt duża wartość";
	} else if ($aip_parts[0] < 0 || $aip_parts[1] < 0 || $aip_parts[2] < 0 || $aip_parts[3] < 0) {
		$err_adres_ip = "Błędny adres IP - Zbyt mała wartość";
	} else if (!is_numeric($aip_parts[0]) || !is_numeric($aip_parts[1]) || !is_numeric($aip_parts[2]) || !is_numeric($aip_parts[3])) {
		$err_adres_ip = "Błędny adres IP - Jedna z wartości jest nie poprawna";
	}}
	catch(Exception $err) {
			echo "Info o błędzie: ".$err;
	}
	//detekcja błędów - adres ip - koniec
	//konwersja na binarne - start
	@$aip_bin = str_pad(decbin($aip_parts[0]), 8, "0", STR_PAD_LEFT).str_pad(decbin($aip_parts[1]), 8, "0", STR_PAD_LEFT).str_pad(decbin($aip_parts[2]), 8, "0", STR_PAD_LEFT).str_pad(decbin($aip_parts[3]), 8, "0", STR_PAD_LEFT);
	@$maska_bin = str_pad(decbin($maska_parts[0]), 8, "0", STR_PAD_RIGHT).str_pad(decbin($maska_parts[1]), 8, "0", STR_PAD_RIGHT).str_pad(decbin($maska_parts[2]), 8, "0", STR_PAD_RIGHT).str_pad(decbin($maska_parts[3]), 8, "0", STR_PAD_RIGHT);
	//konwersja na binarne - koniec
	//tworzenie nowej (przedłużonej) maski - start
	$nowa_maska_bin = $maska_bin;
	for ($i=0; $i<strlen((string)$podsieci_ilosc_bin); $i++) {
		@$nowa_maska_bin[strpos($nowa_maska_bin, "0")] = 1;
	}
	//tworzenie nowej (przedłużonej) maski - koniec
	for ($i=0; $i<$podsieci_ilosc; $i++) {
		$podsieci[$i] = $aip_bin;
		$podsieci[$i] = substr_replace($podsieci[$i], str_pad(decbin($i), strlen((string)$podsieci_ilosc_bin), "0", STR_PAD_LEFT), strpos($maska_bin, "0"), strlen((string)$podsieci_ilosc_bin));
		$podsieci_dec[$i] = bindec((int)substr($podsieci[$i], 0, 8)).".".bindec((int)substr($podsieci[$i], 8, 8)).".".bindec((int)substr($podsieci[$i], 16, 8)).".".bindec((int)substr($podsieci[$i], 24, 8));
	}
	for ($i=0; $i<=31; $i++) {
		if ($nowa_maska_bin[$i] == 0) {
			$odw_maska_bin[$i] = 1;
		} else if ($nowa_maska_bin[$i] == 1) {
			$odw_maska_bin[$i] = 0;
		}
	}
	$odw_maska_bin_str = implode($odw_maska_bin);
	
	for ($i=0; $i<$podsieci_ilosc; $i++) {
		for ($j=0; $j<=31; $j++) {
			$broadcast_bin[$i][$j] = $podsieci[$i][$j]+$odw_maska_bin[$j];
		if ($broadcast_bin[$i][$j] == 2) {
			$broadcast_bin[$i][$j] = 1;
		}
		}
		$broadcast_bin_str[$i] = implode($broadcast_bin[$i]);
		$broadcast_dec[$i] = bindec((int)substr($broadcast_bin_str[$i], 0, 8)).".".bindec((int)substr($broadcast_bin_str[$i], 8, 8)).".".bindec((int)substr($broadcast_bin_str[$i], 16, 8)).".".bindec((int)substr($broadcast_bin_str[$i], 24, 8));

		$podsieci_part_4_p1[$i] = bindec((int)substr($podsieci[$i], 24, 8))+1;
		$podsieci_part_4_m1[$i] = bindec((int)substr($broadcast_bin_str[$i], 24, 8))-1;
		$host_min[$i] = bindec((int)substr($podsieci[$i], 0, 8)).".".bindec((int)substr($podsieci[$i], 8, 8)).".".bindec((int)substr($podsieci[$i], 16, 8)).".".$podsieci_part_4_p1[$i];
		$host_max[$i] = bindec((int)substr($broadcast_bin_str[$i], 0, 8)).".".bindec((int)substr($broadcast_bin_str[$i], 8, 8)).".".bindec((int)substr($broadcast_bin_str[$i], 16, 8)).".".$podsieci_part_4_m1[$i];
	}
	
	$ilosc_hostow = pow(2, 32-strpos($nowa_maska_bin, "0"))-2;
	
	for ($i=0; $i<$podsieci_ilosc; $i++) {
		$podsieci_bin_tojs[$i] = wordwrap($podsieci[$i], 8, ".", true);
		$maska_bin_tojs = wordwrap($nowa_maska_bin, 8, ".", true);
		$odw_maska_bin_tojs = wordwrap($odw_maska_bin_str, 8, ".", true);
		$broadcast_bin_tojs[$i] = wordwrap($broadcast_bin_str[$i], 8, ".", true);
	}
	//----Efekt końcowy----//
	/*
		ip podsieci - decymalnie = $podsieci
		ip podsieci - binarnie = $podsieci_bin
		maska - decymalnie = $nowa_maska
		maska - binarnie = $nowa_maska_bin
		odwrócona maska - binarnie = $odw_maska_bin
		broadcast - decymalnie = $broadcast
		broadcast - binarnie = $broadcast_bin
		host-min = $host_min
		host-max = $host_max
		host-ilość = $host_ile
	*/
?>

<!doctype html>
<html>
<head>
	<meta charset="utf-8">
    <title>podsieci</title>
    <link href="calcs-style.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,600,700&display=swap&subset=latin-ext" rel="stylesheet">
    <link href="css/fontello.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="container">
        <div id="header">
            <div id="logo">
                <center>
                    <span style="color: black;">Kalkulator Podsieci</span><br />
					<a href="index.php"><input style="width: 200px; height: 30px; font-size: 20px; font-weight: bold; background-color: #00ff00;" type="button" class="ipcalc" value="Powrót do wyboru"></a>
                </center>
                <img src="" style="float: left;"/>
                
            </div>
        </div>
        <div id="content" style="margin-bottom: 50px;">
            <h4>
			<center>
				<form method="post">
                    <label style="margin-left:80px;" for="adres_ip">Adres IP</label>
                    <label style="margin-left:50px;" for="adres_ip">Ilość podsieci</label><br />
                    <input type="text" name="adres_ip" id="adres_ip" />
					<input style="margin-left:50px;" type="number" name="podsieci" id="podsieci" size="3" min="2" max="512"><br />
					<label for="maska">Maska</label><br />
					<select name="maska">
						<option value="0.0.0.0">/0 aka 0.0.0.0</option>
						<option value="128.0.0.0">/1 aka 128.0.0.0</option>
						<option value="192.0.0.0">/2 aka 192.0.0.0</option>
						<option value="224.0.0.0">/3 aka 224.0.0.0</option>
						<option value="240.0.0.0">/4 aka 240.0.0.0</option>
						<option value="248.0.0.0">/5 aka 248.0.0.0</option>
						<option value="252.0.0.0">/6 aka 252.0.0.0</option>
						<option value="254.0.0.0">/7 aka 254.0.0.0</option>
						<option value="255.0.0.0">/8 aka 255.0.0.0</option>
						<option value="255.128.0.0">/9 aka 255.128.0.0</option>
						<option value="255.192.0.0">/10 aka 255.192.0.0</option>
						<option value="255.224.0.0">/11 aka 255.224.0.0</option>
						<option value="255.240.0.0">/12 aka 255.240.0.0</option>
						<option value="255.248.0.0">/13 aka 255.248.0.0</option>
						<option value="255.252.0.0">/14 aka 255.252.0.0</option>
						<option value="255.254.0.0">/15 aka 255.254.0.0</option>
						<option value="255.255.0.0">/16 aka 255.255.0.0</option>
						<option value="255.255.128.0">/17 aka 255.255.128.0</option>
						<option value="255.255.192.0">/18 aka 255.255.192.0</option>
						<option value="255.255.224.0">/19 aka 255.255.224.0</option>
						<option value="255.255.240.0">/20 aka 255.255.240.0</option>
						<option value="255.255.248.0">/21 aka 255.255.248.0</option>
						<option value="255.255.252.0">/22 aka 255.255.252.0</option>
						<option value="255.255.254.0">/23 aka 255.255.254.0</option>
						<option value="255.255.255.0" selected="">/24 aka 255.255.255.0</option>
						<option value="255.255.255.128">/25 aka 255.255.255.128</option>
						<option value="255.255.255.192">/26 aka 255.255.255.192</option>
						<option value="255.255.255.224">/27 aka 255.255.255.224</option>
						<option value="255.255.255.240">/28 aka 255.255.255.240</option>
						<option value="255.255.255.248">/29 aka 255.255.255.248</option>
						<option value="255.255.255.252">/30 aka 255.255.255.252</option>
						<option value="255.255.255.254">/31 aka 255.255.255.254</option>
						<option value="255.255.255.255">/32 aka 255.255.255.255  </option>
					</select><br /><br />
					
                    <input type="submit" value="Oblicz">
                </form>
				<?php
				if(isset($err_adres_ip)){
					echo '<div class="error">'.$err_adres_ip.'</div>';
					unset($err_adres_ip);
				} else {
					echo "<br />Obliczono<br />Wybierz sieć, której informacje chcesz znać<br />";
				}
				?>
				<select>
				<?php
					for ($i=0; $i<$podsieci_ilosc; $i++) {
						$j=$i+1;
						echo "<option value=".$podsieci_dec[$i].">"."Podsieć #".$j.": ".$podsieci_dec[$i]."</option>";
					}
				?>
				</select>
			</center>
			<div style="background-color:#282828; width:35%; height: 50%; z-index: -1; margin-top: -16%; margin-left: 70%; padding: 10px; box-sizing: border-box; color:#fff;">
				<script>
					<?php
						$js_podsieci_bin = json_encode($podsieci_bin_tojs);
						$js_maska = json_encode($maska);
						$js_maska_bin = json_encode($maska_bin_tojs);
						$js_odw_maska_bin = json_encode($odw_maska_bin_tojs);
						$js_broadcast = json_encode($broadcast_dec);
						$js_broadcast_bin = json_encode($broadcast_bin_tojs);
						$js_host_min = json_encode($host_min);
						$js_host_max = json_encode($host_max);
						
						$js_ilosc_hostow = json_encode($ilosc_hostow);
						
						$js_maska_dlugosc = strpos($nowa_maska_bin, "0");
						
						echo "var js_podsieci_bin = ". $js_podsieci_bin . ";\n";
						echo "var js_maska = ". $js_maska . ";\n";
						echo "var js_maska_bin = ". $js_maska_bin . ";\n";
						echo "var js_odw_maska_bin = ". $js_odw_maska_bin . ";\n";
						echo "var js_broadcast = ". $js_broadcast . ";\n";
						echo "var js_broadcast_bin = ". $js_broadcast_bin . ";\n";
						echo "var js_host_min = ". $js_host_min . ";\n";
						echo "var js_host_max = ". $js_host_max . ";\n";
						
						echo "var js_ilosc_hostow = ". $js_ilosc_hostow . ";\n";
						
						echo "var js_maska_dlugosc = ". $js_maska_dlugosc . ";\n";

					?>
					document.getElementsByTagName('select')[1].onchange = function showStats() {
						var index = this.selectedIndex;
						var inputText = this.children[index].value;
						//console.log(inputText);
						document.getElementById("adres_sieci").innerHTML = inputText;
						document.getElementById("adres_sieci_bin").innerHTML = js_podsieci_bin[index];
						document.getElementById("maska").innerHTML = js_maska;
						document.getElementById("maska_tytul").innerHTML = "Maska: /"+js_maska_dlugosc;
						document.getElementById("maska_bin").innerHTML = js_maska_bin;
						document.getElementById("odw_maska_bin").innerHTML = js_odw_maska_bin;
						document.getElementById("broadcast").innerHTML = js_broadcast[index];
						document.getElementById("broadcast_bin").innerHTML = js_broadcast_bin[index];
						document.getElementById("zakres_hostow").innerHTML = js_host_min[index]+" - "+js_host_max[index];
						document.getElementById("ilosc_hostow").innerHTML = js_ilosc_hostow;
					}
				</script>
				<div></div>
				<div id="tablica_sieci">
					<div>
						<div>Adres sieci:</div>
						<div id="adres_sieci"></div>
						<div>Adres sieci binarnie:</div>
						<div id="adres_sieci_bin"></div>
					</div><br />
					<div>
						<div id="maska_tytul">Maska:</div>
						<div id="maska"></div>
						<div>Maska binarnie:</div>
						<div id="maska_bin"></div>
						<div>Odwrócona maska binarnie:</div>
						<div id="odw_maska_bin"></div>
					</div><br />
					<div>
						<div>Broadcast:</div>
						<div id="broadcast"></div>
						<div>Broadcast binarnie:</div>
						<div id="broadcast_bin"></div>
					</div><br />
					<div>
						<div>Zakres hostów:</div>
						<div id="zakres_hostow"></div>
						<div>Ilość hostów: </div>
						<div id="ilosc_hostow"></div>
					</div>
				</div>
			</div>
            </h4>
        </div>
    </div>
</body>
</html>