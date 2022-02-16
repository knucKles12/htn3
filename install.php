<?php
/**
* Install Script
* Installs the game and starts a new round.
* Massively refactored for v3.
* @author R. Stück
* @version 3
**/

include('config.php');
#include('startup.php');
include('lang/'.$_POST['lang'].'.php');

if (file_exists('HTN.INSTALLED')) {
  echo "<br>Wenn du HTN 3 neu installieren willst, lösche bitte die Datei <i>HTN.INSTALLED</i> aus deinem root Verzeichnis.<br>";
  exit;
}

$step = $_GET['step'];
if ($step == "") { $step = 1; }

function chmod_R($path, $filemode) {
  if (!is_dir($path))    return chmod($path, $filemode);

  $dh = opendir($path);
  while ($file = readdir($dh)) {
    if($file != '.' && $file != '..') {
      $fullpath = $path.'/'.$file;
      if(!is_dir($fullpath)) {
        if (!chmod($fullpath, $filemode))
        return FALSE;
      } else {
        if (!chmod_R($fullpath, $filemode))
        return FALSE;
      }
    }
  }

  closedir($dh);

  if(chmod($path, $filemode))
  return TRUE;
  else
  return FALSE;
}

?>
<html>
<head>
  <title>HackTheNet 3 Install</title>
  <meta name="author" content="R. Stück">
  <link rel="stylesheet" href="styles/crystal/style.css" type="text/css">
  <link rel="SHORTCUT ICON" href="favicon.ico" />
</head>
<body text="#000000" bgcolor="#FFFFFF" link="#000080" alink="#000080" vlink="#000080">
  <!-- Start Head -->
  <div class="header">
    <h1>HTN 3</h1>
    <!-- Start Navi -->
    <ul class="navigation">
      <!-- Schritt 1 -->
      <li>
        <a href="#" title="">
          <?
          if ($step==1) {
            echo '<font color="red">';
          }
          ?>
          <strong>Schritt 1</strong>
          <?
          if ($step==1) {
            echo '</font>';
          }
          ?>
          <br />
          <em>Sprache wählen</em>
        </a>
        <div class="help">In diesem Schritt wird die Standardsprache festgelegt.<div>
        </li>
        <!-- Schritt 2 -->
        <li>
          <a href="#" title="">
            <? if ($step==2) { echo '<font color="red">'; } ?><strong>Schritt 2</strong><? if ($step==2) { echo '</font>'; } ?>
            <br />
            <em>Prüfen der Einstellungen</em>
          </a>
          <div class="help">In diesem Schritt werden verschiedene Einstellungen geprüft.<div>
          </li>
          <!-- Schritt 3 -->
          <li>
            <a href="#" title="">
              <? if ($step==3) { echo '<font color="red">'; } ?><strong>Schritt 3</strong><? if ($step==3) { echo '</font>'; } ?>
              <br />
              <em>Eingabe Ihrer Daten</em>
            </a>
            <div class="help">In diesem Schritt <i>müssen</i> die Daten angeben, mit denen HTN 3 später laufen wird.</div>
          </li>
          <!-- Schritt 4 -->
          <li>
            <a href="#" title="">
              <? if ($step==4) { echo '<font color="red">'; } ?><strong>Schritt 4</strong><? if ($step==4) { echo '</font>'; } ?>
              <br />
              <em>Konfigurieren von HTN 3.</em>
            </a>
            <div class="help">In diesem Schritt wird HTN 3 konfiguriert.</div>
          </li>
        </ul>
        <!-- End Navi -->
      </div>
      <!-- End Head -->
      <!-- Start Body -->
      <div id="abook-selpage">
        <?php

        switch ($step) {

          default:
            ## Choose language ##
            echo '   <h3>Choose your language</h3>'."\n";
            echo '    <form method="post" action="?step=2">'."\n";
            echo '     <table>'."\n";
            echo '      <tr>'."\n";
            echo '       <th>Language</th>'."\n";
            echo '       <td><select name="lang">';
            echo '        <option value="en_US">English (US)</option>';
            echo '        <option value="de_DE">Deutsch</option>';
            echo '       </select></td>'."\n";
            echo '      </tr>'."\n";
            echo '      <tr>'."\n";
            echo '       <td><input type="submit" value="   Submit   "></input></td>'."\n";
            echo '      </tr>'."\n";
            echo '     </table>'."\n";
            echo '    </form>'."\n";
            break;


          case "2":
            ## Check settings, file and folder permissions ##
            $failure = 0;

            echo '   <h3>'.TXT_SETUP_HEADER_CHECKPERMISSIONS.'</h3>'."\n";
            echo '    <br><p>PHP Version: '.phpversion();

            if (get_magic_quotes_gpc() == 1) $color = "red";
            else $color = "green";
            echo '    <br>Magic Quotes: <font color="'.$color.'">•</font>'."\n";

            if (is_writable('.')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>root folder (777): <font color="'.$color.'">•</font>'."\n";

            if (is_writable('config.php')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>config.php (777): <font color="'.$color.'">•</font>'."\n";

            if (is_writable('data/_server1/verifyimgs')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>data/_server1/verifyimgs (777): <font color="'.$color.'">•</font>'."\n";

            if (is_writable('data')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>data (777): <font color="'.$color.'">•</font>'."\n";

            if (is_writable('data/regtmp')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>data/regtmp (777): <font color="'.$color.'">•</font>'."\n";

            if (is_writable('data/_server1')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>data/_server1 (777): <font color="'.$color.'">•</font>'."\n";

            if (is_writable('data/_server1/usrimgs')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>data/_server1/usrimgs (777): <font color="'.$color.'">•</font>'."\n";

            if (is_writable('data/_server1/tmp')) $color = "green";
            else {
              $color = "red";
              $failure++;
            }
            echo '    <br>data/_server1/tmp (777): <font color="'.$color.'">•</font>'."</p>\n";

            if ($failure == 0) {
              echo '    <form method="post" action="?step=3">'."\n";
              echo '    <p><input type="submit" value="Weiter"></input> mit Eingabe der Daten</p>';
            } else {
              echo '    <p>Bitte ändern Sie die aktuellen Ordnerrechte-Einstellungen, bei den rot markierten Ordnern/Dateien</p>';
            }
            echo '     <input type="hidden" name="lang" value="'.$_POST['lang'].'">';
            echo '    </form>';
            break;


          case "3":
            echo '   <h3>'.TXT_SETUP_HEADER_DBCREDENTIALS.'</h3>'."\n";
            echo '    <form method="post" action="?step=4">'."\n";
            echo '     <table>'."\n";
            echo '      <tr>'."\n";
            echo '       <th>MySQL Server</th>'."\n";
            echo '       <td><input type="text" size="20" name="dbserver"></input></td>'."\n";
            echo '      </tr>'."\n";
            echo '      <tr>'."\n";
            echo '       <th>MySQL Datenbank</th>'."\n";
            echo '       <td><input type="text" size="20" name="dbname"></input></td>'."\n";
            echo '      </tr>'."\n";
            echo '      <tr>'."\n";
            echo '       <th>MySQL Benutzername</th>'."\n";
            echo '       <td><input type="text" size="20" name="dbuser"></input></td>'."\n";
            echo '      </tr>'."\n";
            echo '      <tr>'."\n";
            echo '       <th>MySQL Passwort</th>'."\n";
            echo '       <td><input type="password" size="20" name="dbpassword"></input></td>'."\n";
            echo '      </tr>'."\n";
            echo '      <tr>'."\n";
            echo '       <td><input type="submit" value="   Submit   "></input></td>'."\n";
            echo '       <td><input type="reset"  value="   Cancel   "></input></td>'."\n";
            echo '      </tr>'."\n";
            echo '     </table>'."\n";
            echo '     <input type="hidden" name="lang" value="'.$_POST['lang'].'">';
            echo '    </form>'."\n";
            break;


          case "4":
            $dbtest = new mysqli($_POST['dbserver'], $_POST['dbuser'], $_POST['dbpassword'], $_POST['dbname']);
            if ($dbtest -> connect_errno) {
              echo "<p><h5>Could not connect to database. Please check your credentials.</h5>";
              echo '<form method="post" action="?step=3"><input type="submit" value="Back"><input type="hidden" name="lang" value="'.$_POST['lang'].'"></form></p>';
            } else {
              $t_persistence=file_get_contents('./include/persistence.php');
              $t_persistence = str_replace('%dbserver%', $_POST['dbserver'], $t_persistence);
              $t_persistence = str_replace('%dbname%', $_POST['dbname'], $t_persistence);
              $t_persistence = str_replace('%dbuser%', $_POST['dbuser'], $t_persistence);
              $t_persistence = str_replace('%dbpassword%', $_POST['dbpassword'], $t_persistence);
              file_put_contents('./include/persistence.php', $t_persistence);

              $database = new persistence();
              $database->db_import_dump('persistence/DATABASE.DUMP.SQL');
              if(mysqli_error($database->get_db())) {
                echo '<p><h5>Error during database setup.</h5>';
                echo mysqli_error($database->get_db());
                echo '<form method="post" action="?step=3"><input type="submit" value="Back"><input type="hidden" name="lang" value="'.$_POST['lang'].'"></input></form></p>';
              } else {
                echo '<h5>Database successfully set up.</h5>';
                echo '<form method="post" action="?step=5">';
                echo '<input type="submit" value="OK"></input>';
                echo '<input type="hidden" name="lang" value="'.$_POST['lang'].'"></input></form>';
              }
            }
            break;


            case "5":
              echo '   <h3>Bitte geben Sie Ihre Daten ein</h3>'."\n";
              echo '    <form method="post" action="?step=6">'."\n";
              echo '     <table>'."\n";
              echo '      <tr>'."\n";
              echo '       <th colspan="2" style="background-color=none;height:5;border:1px soild black;">&nbsp;</th>'."\n";
              echo '      </tr>'."\n";
              echo '      <tr>'."\n";
              echo '       <th>Admin Benutzername</th>'."\n";
              echo '       <td><input type="text" size="20" name="admin_name"></input></td>'."\n";
              echo '      </tr>'."\n";
              echo '      <tr>'."\n";
              echo '       <th>Admin Passwort</th>'."\n";
              echo '       <td><input type="password" size="20" name="admin_pass"></input></td>'."\n";
              echo '      </tr>'."\n";
              echo '      <tr>'."\n";
              echo '       <th>Admin EMail</th>'."\n";
              echo '       <td><input type="text" size="20" name="admin_mail"></input></td>'."\n";
              echo '      </tr>'."\n";
              echo '      <tr>'."\n";
              echo '       <td><input type="submit" value="   Weiter   "></input></td>'."\n";
              echo '       <td><input type="reset"  value="   Löschen   "></input></td>'."\n";
              echo '      </tr>'."\n";
              echo '     </table>'."\n";
              echo '     <input type="hidden" name="lang" value="'.$_POST['lang'].'">';
              echo '    </form>'."\n";
              break;


            case "6":
              $persistence = new persistence();
              $database = $persistence->get_db();

              $query_users = array("id"=>1,
                                  "name"=>"'".mysqli_real_escape_string($database, $_POST['admin_name'])."'",
                                  "email"=>"'".mysqli_real_escape_string($database, $_POST['admin_mail'])."'",
                                  "password"=>"'".mysqli_real_escape_string($database, md5($_POST['admin_pass']))."'",
                                  "pcs"=>"'1'",
                                  "gender"=>"'x'",
                                  "birthday"=>"'0.0.0'",
                                  "stat"=>1,
                                  "liu"=>"'1107786776'",
                                  "lic"=>"'1218800319'",
                                  "clusterstat"=>1000,
                                  "homepage"=>"''",
                                  "infotext"=>"''",
                                  "wohnort"=>"''",
                                  "la"=>"''",
                                  "ads"=>"'no'",
                                  "bigacc"=>"'yes'",
                                  "usessl"=>"'no'",
                                  "enable_usrimg"=>"'no'",
                                  "usrimg_fmt"=>"'cluster points ranking'",
                                  "noipcheck"=>"'no'",
                                  "newmail"=>0,
                                  "lastmail"=>"'1107786776'",
                                  "points"=>33,
                                  "sig_mails"=>"''",
                                  "sig_board"=>"''",
                                  "cluster"=>1,
                                  "cm"=>"'15.08.'",
                                  "login_time"=>1218800159,
                                  "sid"=>"'1af728cc48'",
                                  "sid_ip"=>"'127.0.0.1'",
                                  "sid_pc"=>1,
                                  "sid_lastcall"=>1218800789,
                                  "locked"=>"'no'",
                                  "locked_till"=>0,
                                  "locked_by"=>"''",
                                  "locked_reason"=>"''",
                                  "stylesheet"=>"'anti-ie'",
                                  "inbox_full"=>"''",
                                  "avatar"=>"''",
                                  "rank"=>0,
                                  "da_avail"=>"'no'",
                                  "acode"=>"''",
                                  "tcode"=>"''",
                                  "pcview_ext"=>"'yes'",
                                  "pcview_sorttype"=>"''",
                                  "calcrank"=>"'yes'",
                                  "last_verified"=>0,
                                  "verifyimg"=>0,
                                  "extacc_id"=>"''",
                                  "level"=>5);

              $persistence->db_insert_into('users', $query_users);

              // Create file to mark HTN as installed.
              fopen('HTN.INSTALLED', 'w');

              echo '   <h3>HTN 2.1 wurde erfolgreich konfiguriert.</h3>'."\n";
              echo '    <p><a href="pub.php">Weiter</a> zu HTN 2.1</p>'."\n";
              break;

            //mysql_query("INSERT INTO `users` ( `id`, `name`, `email`, `password`, `pcs`, `gender`, `birthday`, `stat`, `liu`, `lic`, `clusterstat`, `homepage`, `infotext`, `wohnort`, `la`, `ads`, `bigacc`, `usessl`, `enable_usrimg`, `usrimg_fmt`, `noipcheck`, `newmail`, `lastmail`, `points`, `sig_mails`, `sig_board`, `cluster`, `cm`, `login_time`, `sid`, `sid_ip`, `sid_pc`, `sid_lastcall`, `locked`, `locked_till`, `locked_by`, `locked_reason`, `stylesheet`, `inbox_full`, `avatar`, `rank`, `da_avail`, `acode`, `tcode`, `pcview_ext`, `pcview_sorttype`, `calcrank`, `last_verified`, `verifyimg`, `extacc_id`, `level` )
            //VALUES (
              //'1', '".mysql_escape_string($_POST['admin_name'])."', '".mysql_escape_string($_POST['admin_mail'])."', '".md5($_POST['admin_pass'])."', '1', 'x', '0.0.0', 1, '1107786776', '1218800319', 1000, '', '', '', '', 'no', 'yes', 'no', 'no', 'cluster points ranking', 'no', 0, '1107786776', 33, '', '', 1, '15.08.', 1218800159, '1af728cc48', '127.0.0.1', 1, 1218800789, 'no', 0, '', '', 'anti-ie', '', '', 0, 'no', '', '', 'yes', '', 'yes', 0, 0, '', 5
            //)");

        //     mysql_query("INSERT INTO `pcs` ( `id`, `name`, `ip`, `owner`, `owner_name`, `owner_points`, `owner_cluster`, `owner_cluster_code`, `cpu`, `ram`, `lan`, `mm`, `bb`, `ads`, `dialer`, `auctions`, `bankhack`, `fw`, `mk`, `av`, `ids`, `ips`, `rh`, `sdk`, `trojan`, `credits`, `lmupd`, `country`, `points`, `la`, `buildstat`, `di`, `dt`, `lrh`, `blocked`, `upgrcode` )
        //     VALUES (
        //       1, 'NoName', '92.1', 1, '".$_POST['admin_name']."', 0, 0, '', '2', 1, '1', '2.5', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 13130, '1218800524', 'afghanistan', 33, '', NULL, '', '', '', NULL, '8192be0cb55c5696'
        //     );",$handler
        //   );
        //   mysql_query("INSERT INTO `clusters` ( `id`, `name`, `code`, `events`, `tax`, `money`, `infotext`, `points`, `logofile`, `homepage`, `box1`, `box2`, `box3`, `acceptnew`, `rank`, `notice`, `srate_total_cnt`, `srate_success_cnt`, `srate_noticed_cnt` )
        //   VALUES (
        //     1, 'Administration', '=ADM!N=', ' 19:31 Der Cluster wird durch Administrator gegründet!', 1, 8, NULL, 33, NULL, NULL, 'Wichtig', 'Allgemein', 'Alte Beiträge', 'yes', 0, NULL, 0, 0, 0
        //   );",$handler
        // );

        }
      ?>
      </div>
      </body>
      </html>
