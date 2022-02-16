<div class="content" id="public"><h2>Welcome to HackTheNet 3</h2>

<?php

include_once('./config.php');
$langl = new lang;

$langl->includeLang('de', 'data/pubtxt/startseite');

$s1='checked="checked" '; $s2='';
if(substr_count(($_COOKIE['htnLoginData4']),'|')==2) {
  list($server,$usrname,$pwd) = explode('|',$_COOKIE['htnLoginData4']);
  $server = (int)$server;
  $usrname = htmlentities($usrname);
  $pwd = htmlentities($pwd);
  eval("\$s$server='checked=\"checked\" ';");
	$usrname="value=\"$usrname\" ";	$pwd='value="[xpwd]" ';
	$sv='checked="checked" ';
}
echo $notif;

?>

<div id="public-login"><h3><?=$lang['login']?></h3>
<form action="login.php?a=login" method="post">
<input type="hidden" name="server" value="1" />
<table>
<!-- #(in php < qm) =$usrname, =$pwd, =$sv -->
<tr><th>Username</th><td><input name="nick" maxlength="20" /></td></tr>
<tr><th>Password</th><td><input type="password" name="pwd" /></td></tr>
<th></th><td><input type="checkbox" name="save" value="yes" /> Remember me<br />
<input type="checkbox" name="noipcheck" value="yes" /> No IP check<br />(not recommended)</td></tr>
<tr><th colspan="2" style="text-align:right;"><input type="submit" value="  Login  " /></th></tr>
</table>
</form>
</div>

<div id="public-statistic"><h3><?=$lang['stat']?></h3><p>
<?php

$get = new get();

$cnt1=$get->Get_OnlineUserCnt(1);
echo $lang['stati1'].$cnt1.'<br />'.LF;
?></p><p><a href="pub.php?d=stats"><?=$lang['astat']?></a></p></div>

<div class="info"><h3><?=$lang['anews']?></h3><p>
<?php

$get = new get();
$game = new game();

$c = $get->get_news();
echo $game->infobox(nl2br($c['titel']), 'info', nl2br($c['news']), 'id');
?>
</p>
</div>

<div id="team-kings">
<h3><?=$lang['imp']?></h3>
<p><?=$lang['qc']?><a href="http://www.hackthenet.org/">www.hackthenet.org</a><?=$lang['qc2']?></p>
</div>
