<?php
	@$adres_ip = $_POST["adres_ip"];
	(int)$aip_parts = explode('.', $adres_ip);
	$err_adres_ip;
	//--------
	@$maska = $_POST["maska"];
	(int)$maska_parts = explode('.', $maska);
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
	@$aip_bin = str_pad(decbin($aip_parts[0]), 8, "0", STR_PAD_LEFT).str_pad(decbin($aip_parts[1]), 8, "0", STR_PAD_LEFT).str_pad(decbin($aip_parts[2]), 8, "0", STR_PAD_LEFT).str_pad(decbin($aip_parts[3]), 8, "0", STR_PAD_LEFT);
	@$maska_bin = str_pad(decbin($maska_parts[0]), 8, "0", STR_PAD_RIGHT).str_pad(decbin($maska_parts[1]), 8, "0", STR_PAD_RIGHT).str_pad(decbin($maska_parts[2]), 8, "0", STR_PAD_RIGHT).str_pad(decbin($maska_parts[3]), 8, "0", STR_PAD_RIGHT);
	
	for ($i=0; $i<=31; $i++) {
		(int)$as_bin[$i] = (int)$aip_bin[$i]*(int)$maska_bin[$i];
		
		if ($maska_bin[$i] == 0) {
			$odw_maska_bin[$i] = 1;
		} else if ($maska_bin[$i] == 1) {
			$odw_maska_bin[$i] = 0;
		}
		
		$broadcast_bin[$i] = $aip_bin[$i]+$odw_maska_bin[$i];
		if ($broadcast_bin[$i] == 2) {
			$broadcast_bin[$i] = 1;
		}
	}
	$as_bin_str = implode($as_bin);
	$odw_maska_bin_str = implode($odw_maska_bin);
	$broadcast_bin_str = implode($broadcast_bin);

	$as_dec = bindec((int)substr($as_bin_str, 0, 8)).".".bindec((int)substr($as_bin_str, 8, 8)).".".bindec((int)substr($as_bin_str, 16, 8)).".".bindec((int)substr($as_bin_str, 24, 8));
	
	$broadcast_dec = bindec((int)substr($broadcast_bin_str, 0, 8)).".".bindec((int)substr($broadcast_bin_str, 8, 8)).".".bindec((int)substr($broadcast_bin_str, 16, 8)).".".bindec((int)substr($broadcast_bin_str, 24, 8));
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
                    <span style="color: black;">Kalkulator IP</span><br />
					<a href="index.php"><input style="width: 200px; height: 30px; font-size: 20px; font-weight: bold; background-color: #00ff00;" type="button" class="ipcalc" value="Powrót do wyboru"></a>
                </center>
                <img src="" style="float: left;"/>
                
            </div>
        </div>
        <div id="content">
            <h4>
			<center>
				<form method="post">
                    <label for="adres_ip">Adres IP</label><br />
                    <input type="text" name="adres_ip" id="adres_ip" /><br />
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
					echo "adres ip: ".$adres_ip."<br />";
					echo "ip binarnie: ".$aip_bin."<br /><br />";
					echo "maska: ".$maska."<br />";
					echo "maska bin: ".$maska_bin."<br />";
					echo "odwrócona maska bin: ".$odw_maska_bin_str."<br /><br />";
					echo "adres sieciowy dec: ".$as_dec."<br />";
					echo "adres sieciowy bin: ".$as_bin_str."<br /><br />";
					echo "broadcast dec: ".$broadcast_dec."<br />";
					echo "broadcast bin: ".$broadcast_bin_str."<br />";
				}
				?>
			</center>
            </h4>
        </div>
    </div>
</body>
</html>