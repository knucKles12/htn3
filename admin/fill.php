<?php
if ( !defined('IN_HTN') )
{
  die('Hacking attempt');
}
//Einstellungen / Optionen
$error="";$ok="";
$dbpdo = new Dbpdo();
$db = $dbpdo->get_db();

/*
FILLCOUNTRY by Schnitzel & sofalord from fillcountry by erazorlll

Funktionen:
Dieses Script f�llt einfach eure L�nder mit herrenlosen PCs die ihre Werte (CPU, Ram, usw.) durch den Zufall erhalten.
Weiter k�nnen 0-Punkte oder 1024-Punkte PC's eingef�gt werden


History:
<0.4>
- implementierung der data/static/country_data.inc.php um alle l�nder mitreinzubekommen.
- $dateiname um nich jedem xbilibigen zugriff zu gew�hren :D

<0.3>
- alles in einem
- pc's l�schen rausgenommen
- schnitzel random pc script verbessrung (abh�ngigkeiten)

<0.2>
- yeah endlich ein gescheiter Auswahlbereich (Design, Aufbau)
- L�nder zu 25/50/75/100 Prozent f�llen
- PCs wieder l�schen
- Bugfix

- HiJack Abh�ngigkeits-Check bleibt noch ausgebaut (ist eh sehr sehr unwarscheinlich das ein PC alle Voraussetzungen erf�llt, vorher gewinn ich im Loto ;) )

<0.1.1>
- Punkte werden gleich mitberechnet
- HiJack Abh�ngigkeits-Check erstmal wieder ausgebaut (kommt in der n�chsten Version)
- 2 Variablen ver�ndert
- Kommentare hinzugef�gt

<0.1>
- IP-�berpr�fung
- HiJack-Pr�fung (MalwareKit ausgebaut? Cpu? Ram? usw.)
- 2 Extra-Dateien f�r 0-Punkte und 1024-Punkte PCs

<0.1 PreAlpha>
- der ganze Code
*/

?><div class="content" id="work"><?

include('../data/static/country_data.inc.php');


mysql_connect($db_host, $db_username, $db_password);
mysql_select_db($database_prefix.$database_suffix);
mt_srand();

$aip=null;
#<------------ den ganzen L�ndern die IPs zuweisen------------>
if(isset($_POST['name'])) {
$aip = $countrys[$_POST['name']]['subnet'];
}


if (isset($_POST['delja']) && $_POST['delja']== "1") {

mysql_query("DELETE FROM pcs WHERE owner = '0' AND owner_name = ''");

    $num3 = mysql_affected_rows();
      if ($num3>0)
         echo "<p><b>Alle herrenlosen PCs wurden gel�scht!</b><br /></p>";
      else
      {
      echo "<p>Leider konnten die PC's nicht gel�sch worden.<br><br>";	
      echo "Das kann daran liegen dass das Subnet bereits leer sind.<br />Sonst k�nnte es daran liegen dass MySQL nicht l�uft oder die Tabellen nicht existieren.<a href=\"$dateiname\">Zur�ck</a></p>";
      }
}
elseif ($aip>0) {
    $land = $_POST['name'];

    for ($cip=1; $cip<=$_POST['bip']; $cip++)
    {
      $lip = $aip.".".$cip;

      $cpurand =  mt_rand() % 21 + 1;#CPU-Wert Berechnung
      $ramrand = mt_rand() % 9 + 1;  #RAM-Wert Berechnung

      $lanrand = mt_rand() % 10 + 1;   #LAN-Wert Berechnung
      $moneyrand = mt_rand() % 10 + 1;  #MoneyMarket-Wert Berechnung
      $bucksbunkerrand = mt_rand() % 10 + 1;  #BucksBunker-Wert Berechnung
      if ($cpurand >= "6" AND $ramrand >= "2") { $firewallrand= mt_rand() % 10 + 1; #FireWall-Wert Berechnung
      } else { $firewallrand= "0"; }
      if ($cpurand >= "8" AND $ramrand >= "2") { $sdkrand= mt_rand() % 5 + 1; #SDK-Wert Berechnung
      } else { $sdkrand= "0"; }
      if ($cpurand >= "12" AND $sdkrand >= "3") { $malwarerand= mt_rand() % 10 + 1; #MalwareKit-Wert Berechnung
      } else { $malwarerand= "0"; }
      if ($cpurand >= "10" AND $ramrand >= "3") { $antivirusrand = mt_rand() % 10 + 1; #AntiVirus-Wert Berechnung
      } else { $antivirusrand = "0"; }
      if ($cpurand >= "15" AND $sdkrand >= "3") { $IDSrand = mt_rand() % 10 + 1; #IDS-Wert Berechnung
      } else { $IDSrand = "0"; }
      if ($cpurand >= "8" AND $sdkrand >= "2") { $ipspoofingrand = mt_rand() % 10 + 1; #IPS-Wert Berechnung
      } else { $ipspoofingrand = "0"; }
      if ($cpurand >= "18" AND $sdkrand >= "5" AND $malwarerand >= "10" AND $ramrand >= "7") { $hjrand = mt_rand() % 10 + 1; #HiJack-Wert Berechnung */
      } else { $hjrand = "0"; }

      $onlinewerbungrand = mt_rand() % 5 + 1; #OnlineWerbung-Wert Berechnung
      if ($moneyrand >= "4") { $dialerrand = mt_rand() % 5 + 1; #Dialer-Wert Berechnung
      } else { $dialerrand = "0"; }
      if ($moneyrand >= "8") { $auktionsbetrugrand = mt_rand() % 5 + 1; #AuktionsBetrug-Wert Berechnung
      } else { $auktionsbetrugrand = "0"; }
      if ($moneyrand >= "10") { $bankhackrand = mt_rand() % 5 + 1; #BankHacken-Wert Berechnung
      } else { $bankhackrand = "0"; }
      if ($malwarerand >= "4" AND $ramrand >= "4") { $trojanerrand = mt_rand() % 5 + 1; #Trojaner-Wert Berechnung
      } else { $trojanerrand="0"; }

      #<------------ Punkteberechnung ------------>
      $pcpu = $cpurand * 10;
      $pram = $ramrand * 10;
      $pmm =   3 * pow(1.408659,$moneyrand);
      $pbb = 3 * pow(1.408659,$bucksbunkerrand);
      $plan = 3 * pow(1.408659, $lanrand);
      $pfw = 3 * pow(1.408659,$firewallrand);
      $pmk = 3 * pow(1.408659,$malwarerand);
      $pav = 3 * pow(1.408659,$antivirusrand);
      $psdk = 3 * pow(1.408659,$sdkrand);
      $pips = 3 * pow(1.408659,$ipspoofingrand);
      $pids = 3 * pow(1.408659,$IDSrand);

      $ppc = ($pcpu + $pram + $pmm + $pbb + $plan + $pfw + $pmk + $pav + $psdk + $pips + $pids) - 31;
      $ppc = round($ppc,0);

      #<------------  und jetzt noch alles in die DatenBank schreiben ------------>
      $sqlab  = "select id from pcs WHERE ip = '$lip'";
      $res = @mysql_fetch_array(mysql_db_query($db_name1, $sqlab));

      if (strlen($res[0]) <= 0) 
      {
          if($_POST['pc_art'] == "0") {  ## 0 pkt pc
            $sql=mysql_query("INSERT INTO pcs(name, ip, owner, owner_name, owner_points, owner_cluster, owner_cluster_code, cpu, ram, lan, mm, bb, ads, dialer, auctions, bankhack, fw, mk, av, ids, ips, rh, sdk, trojan, credits, lmupd, country, points)values('NoName', '$lip', '', '', '0', '0', '', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1000', '', '$land', '0')");
          }
          if($_POST['pc_art'] == "512") { ## zufall pc
            $sql=mysql_query("INSERT INTO pcs(name, ip, owner, owner_name, owner_points, owner_cluster, owner_cluster_code, cpu, ram, lan, mm, bb, ads, dialer, auctions, bankhack, fw, mk, av, ids, ips, rh, sdk, trojan, credits, lmupd, country, points)
            values('NoName', '$lip', '', '', '0', '0', '', '$cpurand', '$ramrand', '$lanrand', '$moneyrand', '$bucksbunkerrand', '$onlinewerbungrand', '$dialerrand', '$auktionsbetrugrand', '$bankhackrand', '$firewallrand', '$malwarerand', '$antivirusrand', '$IDSrand', '$ipspoofingrand', '$hjrand', '$sdkrand', '$trojanerrand', '$crand', '1095192558', '$land', '$ppc')");
          }
          if($_POST['pc_art'] == "1024") { ## 1024 pkt pc
            $sql=mysql_query("INSERT INTO pcs(name, ip, owner, owner_name, owner_points, owner_cluster, owner_cluster_code, cpu, ram, lan, mm, bb, ads, dialer, auctions, bankhack, fw, mk, av, ids, ips, rh, sdk, trojan, credits, lmupd, country, points)
            values('NoName', '$lip', '', '', '0', '0', '', '21', '9', '10', '10', '10', '10', '5', '5', '5', '10', '10', '10', '10', '10', '10', '5', '5', '130000', '1095192558', '$land', '1024')");
          }
          if ($sql)
          {
          	$counter++;
          } else {
          	echo mysql_error;
          	$fehler_counter++;
          }
      }
    }
    if ($counter>0)
    {
    	if ($fehler_counter=='')
    	{
    		echo "<p><b>Das Land ".$countrys[$land]['name']." wurde mit ".$counter." PCs gef�llt!</b><br /></p>";
    	}
    	else
    	{
    		echo "<p><b>Das Land ".$countrys[$land]['name']." wurde mit ".$counter." PCs gef�llt!<br />".$fehler_counter++." PC's konnten nicht eingef�gt werden</b></p>";
    	}
    		
    }
    else
    {
      echo "<p>Leider konnte das Land nicht mit PCs gef�llt werden.<br><br>";	
      echo "Das kann daran liegen dass das Subnet bereits gef�llt wurde.<br />Sonst k�nnte es daran liegen dass MySQL nicht l�uft oder die Tabellen nicht existieren.<a href=\"$dateiname\">Zur�ck</a></p>";
    }
}
echo'
<div id="settings-settings"><h3>Subnetze f�llen</h3>
<p>
<form action="../user.php" method="POST">
<p><b>W�hlen sie ein Land aus:</b></p>';

  //print_r(array_keys ($countrys));

echo '<p><select size="1" name="name">';
while (list ($key) = each ($countrys)) {
   echo'<option value="'.$countrys[$key]['id'].'">'.$countrys[$key]['name'].'</option>';
}
echo '</select></p>';
echo'

    <p><b>Welche Art von PC\'s sollen aufgef�llt werden?</b></p>
    <p>
    <select size="1" name="pc_art">
    <option value="0">0 Pkt PCs</option>
    <option value="512">Zufalls PCs</option>
    <option value="1024">1024 Pkt PCs</option>
  </select></p>
  <p>
  <p><b>Wie viel Prozent sollen gef�llt werden?</b></p>
  <p><select size="1" name="bip">
  <option value="64">25%</option>
  <option value="127">50%</option>
  <option value="190">75%</option>
  <option value="254" selected>100%</option>
  </select></p>
<input type="hidden" name="a" value="adminaufgaben">
<input type="hidden" name="sid" value='.$sid.'>
  <p><input type="submit" value="Füllen" name="aschick"><input type="reset" value="Zur�cksetzen" name="B2"></p>
</form>
</p>
</div>
<div id="settings-settings"><h3>Subnetze leeren</h3>
<p>
<form action="../user.php" method="POST">
<input type="hidden" name="a" value="adminaufgaben">
<input type="hidden" name="sid" value='.$sid.'>
<p><b>Willst du alle herrenlosen PCs l�schen?</b></p>

<p><input type="radio" value="1" name="delja">Ja<br /></p>
<p><input type="submit" value="Löschen" name="baschick"><input type="reset" value="Zur�cksetzen" name="B3"></form></p></div>
';


mysql_close();

?>
</div>