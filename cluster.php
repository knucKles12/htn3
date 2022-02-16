<?php
session_start();
define('IN_HTN', 1);
$FILE_REQUIRES_PC = false;
include ('ingame.php');


$action = $_REQUEST['page'];
# Die folgenden Variablen sollten nicht mehr verwendet werden
if ($action == '')
    $action = $_REQUEST['mode'];
if ($action == '')
    $action = $_REQUEST['action'];
if ($action == '')
    $action = $_REQUEST['a'];
if ($action == '')
    $action = $_REQUEST['m'];

$get = new get();
$dbc = new dbc();
$ingame = new ingame();
$layout = new layout();
$gres = new gres();
$clusterc = new cluster();

# Konstanten f�r Cluster-Vertr�ge:
define('CV_WAR', 1, false);
define('CV_BEISTAND', 2, false);
define('CV_PEACE', 3, false);
define('CV_NAP', 5, false);
define('CV_WING', 6, false);

# Cluster-Daten lesen:
$clusterid = $usr['cluster'];
$good_actions = 'start join found info listmembers request1 request2';
$cluster = $get->get_cluster($clusterid);
if ($cluster == false && eregi($action, $good_actions) === false)
{
    $gres->no_();
    exit;
}

switch ($action)
{
    case 'start': //------------------------- START -------------------------------

        if ($usr['da_avail'] == 'yes')
            $pc = $get->get_pc($pcid);

        $layout->createlayout_top('HackTheNet - Cluster');
        echo '<div class="content" id="cluster">' . "\n";
        echo '<h2>Cluster</h2>' . "\n";

        #  kein Cluster
        if ($cluster === false)
        {
            $clusterc->nocluster();
            $layout->createlayout_bottom();
            exit;
        }

        if (eregi('http://.*/.*', $cluster['logofile']))
        {
            #if($usr['sid_ip']!='noip') {
            $img = $cluster['logofile'];
            #$img=dereferurl($img);
            $img = '<tr>' . LF . '<td colspan="2"><img src="' . $img .
                '" alt="Cluster-Logo" /></td>' . LF . '</tr>' . "\n";
            #}
            #$img='<tr>'.LF.'<td colspan="2">Das Clusterlogo kann im Moment wegen einer noch nicht geschlossenen Sicherheitslücke nicht angezeigt werden.</td>'.LF.'</tr>'."\n";
        } else
            $img = '';

        $a = explode("\n", $cluster['events']);
        if (count($a) > 21)
        {
            $cluster['events'] = $gres->joinex(array_slice($a, 0, 20), "\n");
            $mod = true;
        }
        $list = str_replace("\n", '<br />', $cluster['events']);
        $gres->gFormatText($list);

        if ($mod == true)
        {
            $clusterc->savemycluster();
        }

        $reqs = @mysql_num_rows($dbc->db_query('SELECT user FROM cl_reqs WHERE cluster=' . $clusterid .
            ' AND dealed=\'no\''));
        $funcs = '';
        $stat = (int)$usr['clusterstat'];
        $settings = '<a href="cluster.php?page=config&amp;sid=' . $sid .
            '">Einstellungen</a><br />';
        $members = '<a href="cluster.php?page=members&amp;sid=' . $sid .
            '">Mitglieder-Verwaltung</a><br />';
        $finances = '<a href="cluster.php?page=finances&amp;sid=' . $sid .
            '">Cluster-Kasse</a><br />';
        $battles = '<a href="cluster.php?page=battles&amp;sid=' . $sid .
            '">Angriffsübersicht</a><br />';
        $konvents = '<a href="cluster.php?page=convents&amp;sid=' . $sid .
            '">Verträge</a><br />';
        $req_verw = '<a href="cluster.php?page=req_verw&amp;sid=' . $sid .
            '">Mitgliedsanträge</a> (' . $reqs . ')<br />';
        if ($stat == CS_ADMIN)
        {
            $funcs = $settings . $members . $finances . $battles . $konvents . $req_verw;
            $jobs = 'Den Cluster verwalten. Du kannst alles machen!';
        }
        if ($stat == CS_COADMIN)
        {
            $funcs = $settings . $finances . $battles . $konvents . $req_verw;
            $jobs = 'Den Cluster verwalten. Du kannst alles machen au&szlig;er den Status von Mitgliedern ändern.';
        }
        if ($stat == CS_WAECHTER)
        {
            $funcs = $battles;
            $jobs = 'Schlachten im Auge behalten.';
        }
        if ($stat == CS_WARLORD)
        {
            $funcs = $battles . $konvents . $finances;
            $jobs = 'Wie ein General den Cluster durch Kriege führen!';
        }
        if ($stat == CS_KONVENTIONIST)
        {
            $funcs = $konvents . $finances;
            $jobs = 'Durch Verhandlungen, Zahlungen und Verträge den politischen Status des Clusters bestimmen.';
        }
        if ($stat == CS_SUPPORTER)
        {
            $funcs = $finances;
            $jobs = 'Schwache Cluster-Mitglieder unterstützen.';
        }
        if ($stat == CS_MITGLIEDERMINISTER)
        {
            $funcs = $req_verw;
            $jobs = 'Aufname-Anträge prüfen.';
        }

        if ($stat > CS_MEMBER)
            $jobs = '<tr>' . LF . '<th>Aufgaben:</th>' . LF . '<td>' . $jobs . '</td>' . LF .
                '</tr>' . "\n";

        if ($funcs != "")
            $funcs = '<tr>' . LF . '<th>Funktionen:</th>' . LF . '<td>' . $funcs . '</td>' .
                LF . '</tr>' . "\n";

        $members = mysql_num_rows($dbc->db_query('SELECT id FROM users WHERE cluster=\'' . $clusterid .
            '\''));

        if ($members > 0 && $cluster['points'] > 0)
            $av = round($cluster['points'] / $members, 2);
        else
            $av = 0;

        $money = number_format((int)$cluster['money'], 0, ',', '.');

        $clusterstat = $ingame->cscodetostring($usr['clusterstat']);

        while (list($bez, $val) = each($cluster))
        {
            $cluster[$bez] = $gres->safeentities($val);
        }

        echo '<div id="cluster-overview">
<h3>' . $cluster['name'] . '</h3>
<table width="90%">
' . $img . '<tr id="cluster-overview-board1">
<td colspan="2"><a href="cboard.php?page=board&amp;sid=' . $sid . '">Zum Cluster-Board</a>|<a href="circ.php?sid=' . $sid . '">Zum Cluster-Chat</a></td>
</tr>
<tr>
<th>Name:</th>
<td>' . $cluster['name'] . '</td>
</tr>
<tr>
<th>Code:</th>
<td>' . $cluster['code'] . '</td>
</tr>
<tr>
<th>Mitglieder (<a href="cluster.php?page=listmembers&amp;cluster=' . $usr['cluster'] .
            '&amp;sid=' . $sid . '">anzeigen</a>):</th>
<td>' . $members . '
(<a href="cluster.php?page=leave&amp;sid=' . $sid . '">Austreten</a>)</td>
</tr>
<tr>
<th>Punkte</th>
<td>' . number_format($cluster['points'], 0, ',', ',') . '</td>
</tr>
<tr>
<th>Durchschnitt:</th>
<td>' . $av . ' Punkte pro User</td>
</tr>
<tr>
<th>Dein Status:</th>
<td>' . $clusterstat . '</td>
</tr>
' . $jobs . $funcs . '<tr>
<th>Vermögen:</th>
<td>' . $money . ' Credits</td>
</tr>
<tr>
<th>Mitgliedsbeitrag:</th>
<td>' . $cluster['tax'] . ' Credits pro Tag pro User</td>
</tr>
<tr id="cluster-overview-events">
<th>Ereignisse:</th>
<td><div>' . $list . '</div></td>
</tr>
<tr id="cluster-overview-board2">
<td colspan="2"><a href="cboard.php?page=board&amp;sid=' . $sid . '">Zum Cluster-Board</a>|<a href="circ.php?sid=' . $sid . '">Zum Cluster-Chat</a></td>
</tr>
</table>
</div>';

        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_COADMIN):
            $cluster['notice'] = html_entity_decode($cluster['notice']);
            echo '<div id="cluster-notice-create">
<h3>Aktuelle Notiz</h3>
<form action="cluster.php?sid=' . $sid . '&amp;page=savenotice" method="post">
<table>
<tr><th>Text:</th><td><textarea name="notice" rows="4" cols="30">' . $cluster['notice'] .
                '</textarea></td></tr>
<tr><th>Aktionen:</th><td><input type="submit" value="Speichern" />
<input type="button" onclick="this.form.notice.value=\'\';this.form.submit();" value="Löschen" />
</td></tr>
</table>
</form>
</div>';
        endif;

        echo '<div id="cluster-distributed-attacks">
<h3>Distributed Attacks</h3><br />';
        if ($usr['da_avail'] == 'yes')
        {
            $pc = $get->get_pc($pcid);
            if ($gres->isavailh('da', $pc) == true)
                echo '<p><a href="distrattack.php?sid=' . $sid .
                    '&amp;page=create">Neue Distributed Attack erstellen</a></p>' . "\n";
            else
                echo '<p>Von diesem PC aus kannst du keine DA erstellen!</p>' . "\n";
        }
        echo '<p><a href="distrattack.php?sid=' . $sid .
            '&amp;page=list">Vorhandene Distributed Attacks anzeigen</a></p>' . "\n";

        echo '</div>' . LF;

        echo '<div id="cluster-overview-infotext"><h3>Aktuelle Clusterbeschreibung</h3><p>' .
            nl2br($gres->safeentities($cluster['infotext'])) . '</p></div>' . "\n";

        $clusterc->ext_conventlist();

        echo '</div>';
        $layout->createlayout_bottom();
        break;

    case 'delconvent': //----------------- DELETE CONVENT -------------------------
        if ($usr['clusterstat'] != CS_ADMIN && $usr['clusterstat'] != CS_WARLORD && $usr['clusterstat'] !=
            CS_KONVENTIONIST && $usr['clusterstat'] != CS_COADMIN)
        {
            $gres->simple_message('Du hast dazu keine Rechte!');
        }


        $c = explode('-', $_REQUEST['convent']);
        $c[0] = (int)$c[0];
        $c[1] = (int)$c[1];

        $sql = 'FROM cl_pacts WHERE cluster=' . $clusterid . ' AND partner=' .
            mysql_escape_string($c[1]) . ' AND convent=' . mysql_escape_string($c[0]) .
            ' LIMIT 1';
        $r = $dbc->db_query('SELECT * ' . $sql . ';');
        if (@mysql_num_rows($r) == 1)
        {
            $dbc->db_query('DELETE ' . $sql . ';');

            $convent = $clusterc->cvcodetostring($c[0]);
            $dat = $get->get_cluster($c[1]);

            $dat[events] = $gres->nicetime4() . ' Der Cluster [cluster=' . $clusterid . ']' . $cluster['code'] .
                '[/cluster] hat <i>' . $convent . '</i> mit euch annulliert!' . LF . $dat['events'];
            $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($dat['events']) .
                '\' WHERE id=' . mysql_escape_string($dat['id']));

            $cluster['events'] = $gres->nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
                '[/usr] annulliert <i>' . $convent . '</i> mit dem Cluster [cluster=' . $dat['id'] .
                ']' . $dat['code'] . '[/cluster]!' . LF . $cluster['events'];

            $x = explode("\n", $cluster['events']);
            if (count($x) > 21)
                $cluster['events'] = $gres->joinex(array_slice($x, 0, 20), "\n");

            $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($cluster['events']) .
                '\' WHERE id=' . $clusterid);
        }

        header('Location: cluster.php?sid=' . $sid . '&page=convents&ok=' . urlencode('Der Vertrag wurde annulliert.'));

        break;


    case 'convents': //----------------- CONVENTS -------------------------
        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_WARLORD || $usr['clusterstat'] ==
            CS_KONVENTIONIST || $usr['clusterstat'] == CS_COADMIN)
        {

            #simple_message('Die Verträge-Verwaltung ist heute morgen nicht verfügbar. Probier es heute nachmittag nochmal.');
            #exit;

            $layout->createlayout_top('HackTheNet - Cluster - Verträge');
            echo '<div class="content" id="cluster">
<h2>Cluster</h2>
' . $notif . '<div id="cluster-create-convent">
<h3>Vertrag erstellen</h3>
' . $xxx . '
<form action="cluster.php?page=saveconvents&amp;sid=' . $sid . '" method="post">
<table>
<tr>
<th>Vertrags-Partner (Code):</th>
<td><input type="text" name="partner" maxlength="12" /></td>
</tr>
<tr>
<th>Vertrags-Art:</th>
<td><select name="type">
<option value="1">Kriegserklärung</option>
<option value="2">Beistandsvertrag</option>
<option value="3">Friedensvertrag</option>
<option value="5">Nicht-Angriffs-Pakt</option>
<option value="6">Wing-Treaty</option>
</select></td>
</tr>
<tr id="cluster-create-convent-confirm">
<td colspan="2"><input type="submit" value="Erstellen" /></td>
</tr>
</table>
</form>
</div>';

            $clusterc->ext_conventlist();

            echo '</div>' . "\n";
            $layout->createlayout_bottom();

        } else
            $gres->no_();
        break;

    case 'saveconvents': //------------------------- SAVE CONVENTS -------------------------------
        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_WARLORD || $usr['clusterstat'] ==
            CS_KONVENTIONIST || $usr['clusterstat'] == CS_COADMIN)
        {

            $dat = $get->get_cluster($_POST['partner'], 'code');
            if ($dat == false)
            {
                $error = 'Ein Cluster mit dem Code ' . $_POST['partner'] . ' existiert nicht!';
            } elseif ($dat['id'] == $clusterid)
            {
                $error = 'Du kannst keinen Vertrag mit dem eigenen Cluster abschlie&szlig;en!';
            } else
            {
                $type = (int)$_POST['type'];
                if ($type < 1 or $type > 6)
                {
                    $gres->no_();
                    exit;
                }
                $convent = $cluster->cvCodeToString($type);
                $cname = htmlspecialchars($dat['code']);
                $dat['events'] = $gres->nicetime4() . ' Der Cluster [cluster=' . $clusterid . ']' . $cluster['code'] .
                    '[/cluster] hat <i>' . $convent . '</i> mit euch eingetragen.' . LF . $dat['events'];
                $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($dat['events']) .
                    '\' WHERE id=' . mysql_escape_string($dat['id']));
                $dbc->db_query('INSERT INTO cl_pacts VALUES (' . $clusterid . ', ' .
                    mysql_escape_string($type) . ', ' . mysql_escape_string($dat['id']) . ');');
                $cluster[events] = $gres->nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
                    '[/usr] trägt <i>' . $convent . '</i> mit dem Cluster [cluster=' . $dat['id'] .
                    ']' . $cname . '[/cluster] ein.' . LF . $cluster['events'];
                $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($cluster['events']) .
                    '\' WHERE id=' . $clusterid);
                $ok = 'Der Vertrag wurde abgeschlossen.';
            }
            header('Location: cluster.php?page=convents&sid=' . $sid . '&' . ($ok != '' ?
                'ok=' . urlencode($ok) : 'error=' . urlencode($error)));

        } else
            no_();
        break;

    case 'savefincances': //------------------------- SAVE FINANCES -------------------------------
        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_COADMIN)
        {
            $tax = (int)$_REQUEST['tax'];
            if (time() < $transfer_ts && $server == $t_limit_server && $tax > 100)
            {
                $tax = 100;
            }
            if ($tax >= 0)
            {
                $cluster['events'] = $gres->nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
                    '[/usr] setzt Mitgliedsbeitrag auf ' . mysql_escape_string($tax) .
                    ' Credits pro Tag' . LF . $cluster['events'];
                $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($cluster['events']) .
                    '\',tax=' . mysql_escape_string($tax) . ' WHERE id=' . $clusterid);
                header('Location: cluster.php?page=finances&sid=' . $sid . '&ok=' . urlencode('Die änderungen wurden übernommen.'));
            } else
            {
                header('Location: cluster.php?page=finances&sid=' . $sid . '&error=' . urlencode
                    ('Bitte eine Zahl eingeben.'));
            }
        } else
            $gres->no_();
        break;

    case 'finances': //------------------------- FINANCES -------------------------------
        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_WARLORD || $usr['clusterstat'] ==
            CS_KONVENTIONIST || $usr['clusterstat'] == CS_SUPPORTER || $usr['clusterstat'] ==
            CS_COADMIN)
        {

            $cluster['money'] = (int)$cluster['money'];
            $cluster['tax'] = (int)$cluster['tax'];

            $javascript = '<script type="text/javascript">' . "\n";
            if ($usr['bigacc'] == 'yes')
            {
                $javascript .= 'function fill(s) { document.frm.pcip.value=s; }';
            }
            $javascript .= '
function autosel(obj) { var i = (obj.name==\'pcip\' ? 1 : 0);
  document.frm.reciptype[i].checked=true; }
</script>';

            $layout->createlayout_top('HackTheNet - Cluster - Finanzen');
            echo '<div class="content" id="cluster">
<h2>Cluster</h2>
' . $notif;
            if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_COADMIN)
            {
                $fm = number_format($cluster[money], 0, ',', '.');
                echo '<div id="cluster-money">
<h3>Verm�gen</h3>
<p>Aktuelles Vermögen des Clusters: ' . $fm . ' Credits.</p>
</div>
<div id="cluster-tax">
<h3>Mitgliedsbeitrag</h3>
<p>Mitgliedsbeitrag in Credits pro User pro Tag festlegen:</p>
<form action="cluster.php?page=savefincances&amp;sid=' . $sid .
                    '" method="post">
<table>
<tr>
<th>Cluster-Mitgliedsbeitrag:</th>
<td><input type="text" name="tax" maxlength="5" value="' . $cluster['tax'] .
                    '" /></td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Speichern" /></td>
</tr>
</table>
</form>
</div>
';
            }

            if ($usr['bigacc'] == 'yes')
                $bigacc = '&nbsp;<a href="javascript:show_abook(\'pc\')">Adressbuch</a>';
            echo '
<div id="cluster-transfers">
<h3>�berweisungen</h3>
<form action="cluster.php?page=transfer&amp;sid=' . $sid .
                '" method="post" name="frm">
<table>
<tr>
<th>Empfänger:</th>
<td><input type="radio" checked="checked" name="reciptype" value="cluster" /> Cluster &ndash; Code: <input type="text" name="clustercode" onchange="autosel(this)" maxlength="12" /><br />
<input type="radio" name="reciptype" value="user" /> Benutzer &ndash; IP: 10.47.<input type="text" name="pcip" onchange="autosel(this)" maxlength="7" />' .
                $bigacc . '</td>
</tr>
<tr>
<th>Betrag:</th>
<td><input type="text" name="credits" maxlength="5" value="0" /> Credits</td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Ausführen" /></td>
</tr>
</table>
</form>
</div>
<div id="cluster-tax-paid">
<h3>Wer hat bezahlt?</h3>
<table>
<tr>
<th>Name</th>
<th>letzte Bezahlung</th>
</tr>
';


            # Wer hat wann bezahlt...?
            $r = $dbc->db_query('SELECT id,name,cm FROM users WHERE cluster=\'' . $clusterid . '\' ORDER BY name ASC');
            while ($user = mysql_fetch_assoc($r))
            {
                if ($user['cm'] == strftime('%d.%m.'))
                    $user['cm'] = 'heute';
                elseif ($user['cm'] == strftime('%d.%m.', time() - 86400))
                    $user['cm'] = 'gestern';
                echo '<tr>' . LF . '<td><a href="user.php?page=info&amp;user=' . $user['id'] .
                    '&amp;sid=' . $sid . '">' . $user['name'] . '</a></td><td>' . $user['cm'] .
                    '</td></tr>';
            }
            echo '</table>' . LF . '</div>' . LF . '</div>' . LF;

            $layout->createlayout_bottom();

        } else
            no_();
        break;

    case 'members': //------------------------- MEMBERS -------------------------------
        if ($usr['clusterstat'] == CS_ADMIN)
        {

            $layout->createlayout_top('HackTheNet - Cluster');
            echo '<div class="content" id="cluster">
<h2>Cluster</h2>
' . $notif . '<div id="cluster-member-administration">
<h3>Mitglieder-Verwaltung</h3>
<form action="cluster.php?page=savemembers&amp;sid=' . $sid . '" method="post">
<table>
<tr>
<th>Name</th>
<th>Punkte</th>
<th>Status</th>
<th>Letztes Log In</th>
<th>Ausschlie&szlig;en?</th>
</tr>
';


            $r = $dbc->db_query('SELECT * FROM users WHERE cluster=\'' . $clusterid . '\' ORDER BY name ASC');

            while ($udat = mysql_fetch_assoc($r))
            {
                $uix = $udat['id'];
                if ($uix == $usrid)
                    continue;
                echo '<tr>' . LF . '<td><a href="user.php?page=info&amp;user=' . $uix .
                    '&amp;sid=' . $sid . '">' . $udat['name'] . '</a></td>' . LF . '<td>' .
                    number_format($udat['points'], 0, ',', '.') . '</td>' . LF . '<td>';
                echo '<select name="stat' . $uix . '">';
                $clusterc->stat_list_item(CS_MEMBER, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_ADMIN, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_COADMIN, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_WAECHTER, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_JACKASS, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_WARLORD, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_KONVENTIONIST, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_SUPPORTER, $udat['clusterstat']);
                $clusterc->stat_list_item(CS_MITGLIEDERMINISTER, $udat['clusterstat']);
                echo '</select></td>' . LF . '<td>' . $gres->nicetime3($udat['login_time']) . '</td>' .
                    LF . '<td><input type="checkbox" value="yes" name="kick' . $uix .
                    '" /></td></tr>';
            }

            echo '<tr>
<td colspan="5"><input type="submit" value="Speichern" /></td>
</tr>
</table>
</form>
</div>
</div>
';
            $layout->createlayout_bottom();

        } else
            $gres->no_();
        break;

    case 'savemembers': //-------------------- SAVE MEMBERS ------------------
        if ($usr['clusterstat'] == CS_ADMIN)
        {

            $r = $dbc->db_query('SELECT id,name,clusterstat FROM users WHERE cluster=\'' . $clusterid .
                '\' ORDER BY name ASC');

            while ($udat = mysql_fetch_assoc($r))
            {
                $uix = $udat['id'];
                if ($uix == $usrid)
                    continue;
                if ($_POST['kick' . $uix] == 'yes')
                { # User aus dem Cluster schmei&szlig;en?
                    $dbc->db_query('UPDATE users SET cluster=\'\',cm=\'\',clusterstat=0 WHERE id=' .
                        mysql_escape_string($uix));
                    $cluster['events'] = nicetime4() . ' [usr=' . $udat['id'] . ']' . $udat['name'] .
                        '[/usr] wird durch [usr=' . $usrid . ']' . $usr['name'] .
                        '[/usr] aus dem Cluster ausgeschlossen.' . LF . $cluster['events'];
                    $ingame->addsysmsg($udat['id'], 'Du wurdest durch [usr=' . $usrid . ']{' . $usr['name'] .
                        '[/usr] aus dem Cluster [cluster=' . $clusterid . ']' . $cluster['code'] .
                        '[/cluster] ausgeschlossen!');
                } else
                {
                    $stat = (int)$_REQUEST['stat' . $uix];
                    if ($udat['clusterstat'] != $stat)
                    {
                        $dbc->db_query('UPDATE users SET clusterstat=\'' . mysql_escape_string($stat) . '\' WHERE id=' .
                            mysql_escape_string($uix));
                        $cluster['events'] = $gres->nicetime4() . ' [usr=' . $udat['id'] . ']' . $udat['name'] .
                            '[/usr] erhält durch [usr=' . $usrid . ']' . $usr['name'] .
                            '[/usr] den Status ' . $ingame->cscodetostring($stat) . '.' . LF . $cluster['events'];
                    }
                }
            }

            $x = explode("\n", $cluster['events']);
            if (count($x) > 21)
                $cluster['events'] = joinex(array_slice($x, 0, 20), "\n");
            $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($cluster['events']) .
                '\' WHERE id=' . $clusterid);

            header('Location: cluster.php?page=members&sid=' . $sid . '&ok=' . urlencode('Die änderungen wurden übernommen!'));
        } else
            $gres->no_();
        break;

    case 'config': //------------------------- CONFIG -------------------------------
        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_COADMIN)
        {

            while (list($bez, $val) = each($cluster))
            {
                $cluster[$bez] = $gres->safeentities(html_entity_decode($val));
            }

            $anch = ($cluster['acceptnew'] == 'yes' ? ' checked="checked"' : '');

            $layout->createlayout_top('HackTheNet - Cluster');
            echo '<div class="content" id="cluster">
  <h2>Cluster</h2>
  ' . $notif . '<div id="cluster-settings">
  <h3>Cluster-Einstellungen</h3>
  <form action="cluster.php?page=savecfg&amp;sid=' . $sid . '" method="post">
  <table>
  <tr>
  <th>Cluster-Name:</th>
  <td><input type="text" name="name" maxlength="48" value="' . $cluster['name'] .
                '" /></td>
  </tr>
  <tr>
  <th>Cluster-Code:</th>
  <td><input type="text" name="code" maxlength="12" value="' . $cluster['code'] .
                '" /></td>
  </tr>
  <tr>
  <th>Neue Mitglieder?</th>
  <td><input name="acceptnew" value="yes" type="checkbox"' . $anch .
                ' /> Sollen Spieler Mitgliedsanträge stellen dürfen, um dem Cluster beizutreten?</td>
  </tr>
  <tr>
  <th>Beschreibung:</th>
  <td><textarea rows="10" cols="50" name="about">' . $cluster['infotext'] .
                '</textarea></td>
  </tr>
  <tr>
  <th>Namen der Ordner im Cluster-Board:</th>
  <td>Ordner 1:<br />
  <input type="text" name="box0" value="' . $cluster['box1'] .
                '" maxlength="30" /><br />
  Ordner 2:<br />
  <input type="text" name="box1" value="' . $cluster['box2'] .
                '" maxlength="30" /><br />
  Ordner 3:<br />
  <input type="text" name="box2" value="' . $cluster['box3'] .
                '" maxlength="30" /></td>
  </tr>
  <tr>
  <th>Logo-Datei:</th>
  <td><input type="text" name="logofile" value="' . $cluster['logofile'] .
                '" /><br />Eine Internet-Adresse mit http:// eingeben.</td>
  </tr>
  <tr>
  <th>Homepage:</th>
  <td><input type="text" name="homepage" value="' . $cluster['homepage'] .
                '" /><br />Eine Internet-Adresse mit http:// eingeben.</td>
  </tr>
  <tr>
  <th>Cluster löschen:</th>
  <td><input name="delete" value="yes" type="checkbox" /></td>
  </tr>
  <tr>
  <td colspan="2"><input type="submit" value="Speichern" /></td>
  </tr>
  </table>
  </form>
  </div>
  </div>
  ';
            $layout->createlayout_bottom();

        } else
            $gres->no_();
        break;

    case 'delcluster':
        if ($usr['clusterstat'] != CS_ADMIN)
        {
            $gres->no_();
            exit;
        }
        if ($_POST['delete'] == 'yes')
        {
            $r = $dbc->db_query('SELECT id FROM users WHERE cluster=\'' . $clusterid . '\';');
            while ($data = mysql_fetch_assoc($r))
            {
                $ingame->addsysmsg($data['id'], 'Dein Cluster ' . $cluster['code'] .
                    ' wurde gelöscht! Das passierte durch [usr=' . $usrid . ']' . $usr['name'] .
                    '[/usr] (' . $ingame->cscodetostring($usr['clusterstat']) . ')');
            }
            $gres->deletecluster($usr['cluster']);
            $dbc->db_query('INSERT INTO logs SET type=\'delcluster\', usr_id=\'' . $usrid . '\', payload=\'' .
                mysql_escape_string($usr['name']) . ' deletes ' . mysql_escape_string($cluster['code']) .
                '\';');
        }
        break;

    case 'savecfg': //------------------------- SAVE CONFIG -------------------------------
        if ($usr['clusterstat'] != CS_ADMIN && $usr['clusterstat'] != CS_COADMIN)
        {
            no_();
        }

        if ($_POST['delete'] == 'yes')
        {

            if ($usr['clusterstat'] != CS_ADMIN)
            {
                $gres->simple_message('Nur Clusteradmins k�nnen Cluster l�schen!');
                exit;
            }

            $layout->createlayout_top();
            echo '<div class="content" id="cluster">
  <h2>Cluster löschen</h2>
  <h3>Bitte bestätigen!</h3>
  <form action="cluster.php?page=delcluster&amp;sid=' . $sid . '" method="post">
  <p><strong>Setz den Haken und klick auf "Weiter" um den Cluster endgültig zu löschen!</strong></p>
  <p><input type="checkbox" value="yes" name="delete" /></p>
  <p><input type="submit" value=" Weiter " /></p>
  </form>
  </div>';
            $layout->createlayout_bottom();

        } else
        {

            $name = $_POST['name'];
            $code = $_POST['code'];
            $text = $_POST['about'];
            $logo = str_replace('\\', '/', $_POST['logofile']);
            $hp = str_replace('\\', '/', $_POST['homepage']);
            $acceptnew = ($_POST['acceptnew'] == 'yes' ? 'yes' : 'no');

            $msg = '';
            $e = false;
            if (trim($code) == '')
            {
                $e = true;
                $msg .= 'Das Feld Code muss ein Kürzel für den Cluster enthalten!<br />';
            }
            if (trim($name) == '')
            {
                $e = true;
                $msg .= 'Das Feld Name muss einen Namen für den Cluster enthalten!<br />';
            }
            if (preg_match('/[;<>"]/', $name) != false)
            {
                $e = true;
                $msg .= 'Der Name darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
            }
            if (preg_match('/[;<>"]/', $code) != false)
            {
                $e = true;
                $msg .= 'Der Code darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
            }
            if (eregi('http://.*/.*', $logo) == false)
            {
                $logo = '';
            }
            if (eregi('http://.*', $hp) == false)
            {
                $hp = '';
            }
            if ($code != $cluster['code'])
            {
                $c = $get->get_cluster($code, 'code');
                if ($c != false && $c['id'] != $cluster['id'])
                {
                    $e = true;
                    $msg = 'Ein Cluster mit diesem Code existiert bereits! Bitte einen anderen w�hlen!';
                }
            }

            if ($e == true)
            {

                header('Location: cluster.php?page=config&error=' . urlencode($msg) . '&sid=' .
                    $sid);

            } else
            {
                while (list($bez, $val) = each($_POST))
                    $_POST[$bez] = html_entity_decode($val);
                $cluster['box1'] = $gres->safeentities($_POST['box0']);
                $cluster['box2'] = $gres->safeentities($_POST['box1']);
                $cluster['box3'] = $gres->safeentities($_POST['box2']);
                $cluster['name'] = $name;
                $cluster['code'] = $code;
                $cluster['acceptnew'] = $acceptnew;
                $cluster['infotext'] = $gres->safeentities($text);
                $cluster['logofile'] = $gres->safeentities($logo);
                $cluster['homepage'] = $gres->safeentities($hp);
                $cluster->savemycluster();
                header('Location: cluster.php?page=config&ok=' . urlencode('Die geänderten Einstellungen wurden übernommen!') .
                    '&sid=' . $sid);
            }

        }
        break;

    case 'found': //------------------------- FOUND -------------------------------
        $code = trim($_POST['code']);
        $name = trim($_POST['name']);

        $msg = '';
        $e = false;
        if (trim($code) == '')
        {
            $e = true;
            $msg .= 'Das Feld Code muss ein Kürzel für den Cluster enthalten!<br />';
        }
        if (trim($name) == '')
        {
            $e = true;
            $msg .= 'Das Feld Name muss einen Namen für den Cluster enthalten!<br />';
        }
        if (eregi('(;|\<|\>|\\")', $name) != false)
        {
            $e = true;
            $msg .= 'Der Name darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
        }
        if (eregi('(;|\<|\>|\\")', $code) != false)
        {
            $e = true;
            $msg .= 'Der Code darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
        }


        if (!(strlen($code) <= 12 and strlen($name) <= 48 and strlen($pwd) <= 16))
        {
            $e = true;
            $msg .= 'Bitte alle drei Felder ausfüllen!<br />';
        }

        if ($e == false)
        {

            $x = $get->get_cluster($code, 'code');
            if ($x === false)
            {

                $events = $gres->nicetime2() . ' Der Cluster wird durch ' . $usr['name'] .
                    ' gegründet!';
                $r = $dbc->db_query('INSERT INTO clusters(id, name, code, events)  VALUES(0, \'' .
                    mysql_escape_string($name) . '\', \'' . mysql_escape_string($code) . '\', \'' .
                    mysql_escape_string($events) . '\');');
                $id = mysql_insert_id();

                $ingame->setuserval('cluster', $id);
                $ingame->setuserval('clusterstat', CS_ADMIN);

                $pcs = count(explode(',', $usr['pcs']));
                $dbc->db_query('INSERT INTO rank_clusters VALUES(0,' . mysql_escape_string($id) .
                    ',1,' . $usr['points'] . ',' . $usr['points'] . ',' . mysql_escape_string($pcs) .
                    ',' . mysql_escape_string($pcs) . ',0)');

                header('Location: cluster.php?page=start&sid=' . $sid);
            } else
                $gres->simple_message('Ein Cluster mit diesem Kürzel existiert bereits!');

        } else
        {
            $layout->createlayout_top();
            echo '<div class="error"><h3>Fehler</h3><p>' . $msg . '</p></div>';
            $layout->createlayout_bottom();
        }

        break;

    case 'join': //------------------------- JOIN -------------------------------

        $x = $get->get_Cluster((int)$_REQUEST['cluster']);

        if ($x !== false)
        {

            $r = $dbc->db_query('SELECT * FROM cl_reqs WHERE cluster=' . mysql_escape_string($x['id']) .
                ' AND dealed=\'yes\' AND user=' . $usrid);
            if (@mysql_num_rows($r) < 1)
            {
                $gres->simple_message('Der Antrag ist abgelaufen!');
                exit;
            }
            $dbc->db_query('DELETE FROM cl_reqs WHERE cluster=' . mysql_escape_string($x['id']) .
                ' AND dealed=\'yes\' AND user=' . $usrid);


            $members = mysql_num_rows($dbc->db_query('SELECT id FROM users WHERE cluster=\'' .
                mysql_escape_string($x['id']) . '\''));
            if ($members < MAX_CLUSTER_MEMBERS)
            {

                $oldcluster = $get->get_cluster($usr['cluster']);
                if ($oldcluster !== false)
                {
                    $oldcluster['events'] = $gres->nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
                        '[/usr] verlässt den Cluster und wechselt zu [cluster=' . $x['id'] . ']' .
                        $x['code'] . '[/cluster].' . LF . $oldcluster['events'];
                    $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($oldcluster['events']) .
                        '\' WHERE id=' . $oldcluster['id'] . ';');
                }

                $x['events'] = $gres->nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
                    '[/usr] tritt dem Cluster bei.' . LF . $x['events'];
                $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($x['events']) . '\' WHERE id=' .
                    mysql_escape_string($x['id']) . ';');

                $ingame->setuserval('cm', '');
                $ingame->setuserval('cluster', $x['id']);
                $ingame->setuserval('clusterstat', CS_MEMBER);

                header('Location: cluster.php?page=start&sid=' . $sid);

            } else
                $gres->simple_message('Dieser Cluster hat die maximale Mitgliedszahl von ' .
                    MAX_CLUSTER_MEMBERS . ' Benutzern schon erreicht!');

        }
        break;

    case 'leave': //------------------------- LEAVE -------------------------------
        $layout->createlayout_top('HackTheNet - Cluster');
        #$r=db_query('SELECT id FROM users WHERE cluster='.$clusterid.';');
        #$members=mysql_num_rows($r);
        $r = $dbc->db_query('SELECT id FROM users WHERE cluster=' . $clusterid .
            ' AND clusterstat=' . (CS_ADMIN) . ';');
        $admins = mysql_num_rows($r);
        if ($usr['clusterstat'] == CS_ADMIN && $admins < 2)
        {
            echo '<h3>Cluster verlassen</h3>
<p><div class="error"><h3>Verweigert</h3><p>Du kannst den Cluster nicht verlassen, da du der letzte Admin bist!<br />Du musst den Cluster in den Cluster-Einstellungen auflösen!</p></div>';
        } else
        {
            echo '<div class="content" id="cluster">
<h2>Cluster</h2>
' . $notif . '<div id="cluster-leave">
<h3>Cluster verlassen</h3>
<p><strong>Wenn du wirklich den Cluster verlassen willst, dann klick auf den Button!</strong></p>
<form action="cluster.php?page=do_leave&amp;sid=' . $sid . '" method="post">
<p><input type="submit" value="Austreten" name="subm" /></p>
</form>
';
        }
        $layout->createlayout_bottom();
        break;

    case 'do_leave': //------------------------- DO LEAVE -------------------------------

        $r = $dbc->db_query('SELECT id FROM users WHERE cluster=' . $clusterid .
            ' AND clusterstat=' . (CS_ADMIN) . ';');
        $admins = mysql_num_rows($r);
        if ($usr['clusterstat'] == CS_ADMIN && $admins < 2)
            exit;

        $cluster['events'] = $gres->nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
            '[/usr] verlässt den Cluster!' . LF . $cluster['events'];
        $ingame->setuserval('cluster', '');
        $ingame->setuserval('cm', '');
        $ingame->setuserval('clusterstat', CS_MEMBER);

        $dbc->db_query('UPDATE clusters SET events=\'' . mysql_escape_string($cluster['events']) .
            '\' WHERE id=' . $clusterid);

        header('Location: cluster.php?page=start&sid=' . $sid);

        break;

    case 'listmembers': //------------------------- LIST MEMBERS -------------------------------
        $c = $_REQUEST['cluster'];
        $st = $_REQUEST['sortby'];
        $sel = ' selected="selected"';
        switch ($st)
        {
            case 'points':
                $st = 'points DESC';
                $ch2 = $sel;
                break;
            case 'stat':
                $st = 'clusterstat DESC';
                $ch3 = $sel;
                break;
            case 'lastlogin':
                $st = 'login_time DESC';
                $ch4 = $sel;
                break;
            default:
                $ch1 = $sel;
                $st = 'name ASC';
        }
        $c = $get->get_cluster($c);
        if ($c !== false)
        {

            $layout->createlayout_top('HackTheNet - Cluster - Mitglieder');

            $members = '';
            $r = $dbc->db_query('SELECT * FROM users WHERE cluster=\'' . mysql_escape_string($c['id']) .
                '\' ORDER BY ' . mysql_escape_string($st) . ';');
            while ($member = mysql_fetch_assoc($r))
            {
                if ($member !== false && ($gres->is_noranKINGuser($member['id']) == false || $c['id'] ==
                    $no_ranking_cluster))
                {
                    $lli = $member['login_time'];
                    if ($lli >= (time() - 24 * 60 * 60))
                        $clr = 'darkgreen';
                    elseif ($lli >= (time() - 72 * 60 * 60))
                        $clr = 'darkorange';
                    else
                        $clr = 'darkred';
                    $lli = '<span style="color:' . $clr . ';">' . $gres->nicetime3($lli) . '</span>';
                    if ($member['sid_lastcall'] > time() - SID_ONLINE_TIMEOUT)
                        $online = '<span style="color:green;">Online</span>';
                    else
                        $online = '<span style="color:red;">Offline</span>';
                    $members .= '<tr>' . LF . '<td><a href="user.php?page=info&amp;user=' . $member['id'] .
                        '&amp;sid=' . $sid . '">' . $member['name'] . '</a></td>' . LF . '<td>' .
                        $ingame->cscodetostring($member['clusterstat']) . '</td>' . LF . '<td>' . number_format($member['points'],
                        0, ',', '.') . '</td>' . LF . '<td>' . $online . '</td>' . LF . '<td>' . $lli .
                        '</td>' . LF . '</tr>' . LF;
                }
                $lli = '';
            }

            $short = htmlspecialchars($c['code']);
            echo '<div class="content" id="cluster">
<h2>Cluster</h2>
<div id="cluster-members">
<h3>Mitglieder von ' . $short . '</h3>
<form action="cluster.php?sid=' . $sid . '&amp;page=listmembers&amp;cluster=' .
                $c['id'] . '" method="post">
<p><strong>Ordnen nach:</strong>&nbsp;<select name="sortby" onchange="this.form.submit()">
  <option value="name"' . $ch1 . '>Name</option>
  <option value="points"' . $ch2 . '>Punkte</option>
  <option value="stat"' . $ch3 . '>Rang</option>
  <option value="lastlogin"' . $ch4 . '>Letztes LogIn</option>
</select></p>
</form>
<table>
<tr>
<th>Name</th>
<th>Rang</th>
<th>Punkte</th>
<th>Status</th>
<th>Letztes Log In</th>
</tr>
' . $members . '</table>
</div>
</div>
';
            $layout->createlayout_bottom();
        } else
            $gres->simple_message('Diesen Cluster gibt es nicht!');
        break;

    case 'battles': //------------------------- BATTLES -------------------------------


        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_WARLORD || $usr['clusterstat'] ==
            CS_WAECHTER || $usr['clusterstat'] == CS_COADMIN)
        {


            $layout->createlayout_top('HackTheNet - Cluster');
            echo '<div class="content" id="cluster">' . "\n";
            echo '<h2>Cluster</h2>' . "\n";
            echo '<div id="cluster-battles">' . "\n";
            echo '<h3>Angriffsübersicht</h3>' . "\n\n";
            echo '<p>Es werden alle Angriffe der letzten 24 Stunden angezeigt</p>' . "\n";
            echo '<p><strong>Angriffe <em>durch</em> Mitglieder des Clusters</strong></p>' .
                "\n";
            $clusterc->battle_table('out');
            echo '<br /><p><strong>Angriffe <em>auf</em> Mitglieder des Clusters</strong></p>' .
                "\n";
            $clusterc->battle_table('in');

            $layout->createlayout_bottom();
        } else
            $gres->no_();
        break;

    case 'info': //------------------------- INFO -------------------------------

        $c = $_REQUEST['cluster'];
        $cluster = $get->get_cluster($c, 'id');
        if ($cluster !== false)
        {
            $layout->createlayout_top('HackTheNet - Cluster-Profil');
            echo '<div class="content" id="cluster-profile">
<h2>Cluster-Profil</h2>
<div id="cluster-profile-profile">
<h3 id="cluster-profile-code">' . $cluster['code'] . '</h3>
';
            if (eregi('http://.*/.*', $cluster['logofile']))
            {
                if ($usr['sid_ip'] != 'noip')
                {
                    $img = $cluster['logofile'];
                    $img = '<tr>' . LF . '<td colspan="2" align="center"><img src="' . $img .
                        '" alt="Logo" /></td>' . LF . '</tr>' . "\n";
                }
            }
            if (eregi('http://.*', $cluster['homepage']))
            {
                $hp = $gres->dereferurl($cluster['homepage']);
                $hp = '<tr>' . LF . '<th>Homepage:</th>' . LF . '<td><a href="' . $hp . '">' . $cluster['homepage'] .
                    '</a></td>' . LF . '</tr>' . "\n";
            }

            $members = mysql_num_rows($dbc->db_query('SELECT id FROM users WHERE cluster=\'' .
                mysql_escape_string($cluster['id']) . '\''));

            if ($members > 0 && $cluster['points'] > 0)
                $av = round($cluster['points'] / $members, 2);
            else
                $av = 0;

            $text = nl2br($cluster['infotext']);

            if ($usr['stat'] > 10)
            {
                $text .= '</td></tr><tr class="greytr2"><td>SONDER-FUNKTIONEN</td><td><a href="secret.php?sid=' .
                    $sid . '&page=file&type=cluster&id=' . $c .
                    '">bearbeiten</a> | <a href="secret.php?sid=' . $sid . '&page=cboard&id=' . $c .
                    '">Cluster-Board</a>
| <a href="secret.php?sid=' . $sid . '&page=delcluster&id=' . $c .
                    '">Cluster l�schen!!</a>';
            }

            if ($cluster['id'] != $usr['cluster'])
            {
                if ($cluster['acceptnew'] == 'yes')
                {
                    if ($members < MAX_CLUSTER_MEMBERS)
                    {
                        $col = 'green';
                        $aufnahme = 'Möglich (<a href="cluster.php?page=request1&amp;sid=' . $sid .
                            '&amp;cluster=' . $cluster['id'] . '">Aufnahmeantrag stellen</a>)';
                    } else
                    {
                        $col = 'red';
                        $aufnahme = 'Der Cluster hat die max. Mitgliederzahl von ' . MAX_CLUSTER_MEMBERS .
                            ' schon erreicht!';
                    }
                } else
                {
                    $col = 'red';
                    $aufnahme = 'Der Cluster akzeptiert keine neuen Mitglieder mehr!';
                }
                $aufnahme = '<tr>' . LF . '<th>Aufnahme:</th>' . LF . '<td><span style="color:' .
                    $col . ';">' . $aufnahme . '</span></td>' . LF . '</tr>' . "\n";
            }

            echo '
<div class="submenu"><p><a href="ranking.php?page=ranking&amp;sid=' . $sid .
                '&amp;type=cluster&amp;id=' . $c . '">Cluster in Rangliste</a></p></div>
<table>
' . $img . '<tr>
<th>Code:</th>
<td>' . $cluster['code'] . '</td>
</tr>
<tr>
<th>Name:</th>
<td>' . $cluster['name'] . '</td>
</tr>
<tr><th>Punkte:</th>
<td>' . $cluster['points'] . '</td>
</tr>
<tr>
<th>Durchschnitt:</th>
<td>' . $av . ' Punkte pro User</td>
</tr>
' . $hp . '
<tr>
<th>Mitglieder (<a href="cluster.php?page=listmembers&amp;cluster=' . $c .
                '&amp;sid=' . $sid . '">anzeigen</a>):</th>
<td>' . $members . '</td>
</tr>
<tr>
<th>Beschreibung:</th>
<td>' . $text . '</td>
</tr>
' . $aufnahme . '
</table>
</div>
';
            echo $clusterc->conventlist($c);
            echo '</div>' . "\n";
            $layout->createlayout_bottom();
        } else
            $gres->simple_message('Diesen Cluster gibt es nicht!');

        break;

    case 'transfer': // ------------------------- TRANSFER ------------------------

        if (time() < $transfer_ts && $server == $t_limit_server)
        {
            $gres->simple_message('�berweisungen sind erst ab ' . nicetime($transfer_ts) .
                ' erlaubt!');
            exit;
        }

        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_WARLORD || $usr['clusterstat'] ==
            CS_KONVENTIONIST || $usr['clusterstat'] == CS_SUPPORTER || $usr['clusterstat'] ==
            CS_COADMIN)
        {

            $type = $_POST['reciptype'];
            $credits = $gres->human2int(trim($_POST['credits']));

            $e = '';
            if ($credits > $cluster['money'])
                $e = 'Nicht genügend Credits für überweisung vorhanden!';
            switch ($type)
            {
                case 'user':
                    $recip = $get->get_PC($_POST['pcip'], 'ip');
                    if ($recip === false)
                        $e = 'Ein Computer mit dieser IP existiert nicht!';
                    if ($recip['owner'] == $usrid)
                        $e = 'Du kannst dir selber kein Geld überweisen!';
                    break;
                case 'cluster':
                    $recip = $_POST['clustercode'];
                    $recip = $get->get_Cluster($recip, 'code');
                    if ($recip === false)
                        $e = 'Ein Cluster mit diesem Code existiert nicht!';
                    if ($recip['id'] == $usr['cluster'])
                        $e = 'Du kannst kein Geld an deinen eigenen Cluster überweisen!';
                    break;
                default:
                    $e = 'Ungültiger Empfänger-Typ!';
                    break;
            }

            if ($credits < 100)
                $e = 'Der Mindestbetrag für eine überweisung sind 100 Credits!';

            if ($e == '')
            {
                $tcode = $gres->random_string(16);
                $fin = 0;
                $layout->createlayout_top('HackTheNet - Cluster - überweisen');
                echo '<div class="content" id="cluster">
<h2>Cluster</h2>
<div id="cluster-transfer1">
<h3>überweisung</h3>

<form action="cluster.php?page=transfer2&amp;sid=' . $sid . '"  method="post">
<input type="hidden" name="tcode" value="' . $tcode . '">';
                switch ($type)
                {
                    case 'user':
                        $recip_usr = $get->get_user($recip['owner']);
                        $text = '<p><strong>Hiermit werden ' . $credits .
                            ' Credits an den Rechner 10.47.' . $recip['ip'] .
                            ', der <a href="user.php?page=info&user=' . $recip['owner'] . '&sid=' . $sid .
                            '">' . $recip_usr['name'] .
                            '</a> gehört, überwiesen.</strong></p><br />';

                        $c = $get->get_Country('id', $recip['country']);
                        $country2 = $c['name'];
                        $in = $c['in'];
                        $rest = $credits - $in;
                        if ($rest > 0)
                        {
                            $fin = $rest;
                            $text .= '<p>Von diesem Betrag werden noch ' . $in .
                                ' Credits Gebühren als Einfuhr nach ' . $country2 .
                                ', dem Standort von 10.47.' . $recip['ip'] . ' abgezogen. ' . $recip_usr['name'] .
                                ' erhält also noch <b>' . $rest . ' Credits.</p>';
                        } else
                        {
                            $text .= '<p>Da der Betrag sehr gering ist, werden keine Gebühren erhoben. ' .
                                $recip_usr['name'] . ' erhält <b>' . $credits . ' Credits.</p>';
                            $fin = $credits;
                        }

                        $max = $get->get_maxbb($recip);
                        if ($recip['credits'] + $fin > $max)
                        {
                            $rest = $max - $recip['credits'];
                            $fin = $rest;
                            $credits = $rest;
                            $text .= '<br /><p>Da ' . $recip_usr['name'] .
                                ' seinen BucksBunker nicht weit genug ausgebaut hat, um das Geld zu Empfangen, werden nur <b>' .
                                $rest . ' Credits</b> (inklusive Gebühren) überwiesen!</p>';
                        }
                        if ($rest < 1)
                        {
                            echo '<div class="error"><h3>BucksBunker voll</h3><p>Der BucksBunker von ' . $recip_usr['name'] .
                                ' ist voll! überweisung wird abgebrochen!</p></div>';
                            $layout->createlayout_bottom();
                            exit;
                        }
                        echo $text;

                        break;
                    case 'cluster':
                        echo '<p><strong>Hiermit werden ' . $credits . ' Credits an den Cluster ' .
                            htmlspecialchars($recip['code']) . ' (' . $recip['name'] .
                            ') überwiesen.</strong></p><br />';
                        $fin = $credits;
                        break;
                }
                echo '<br /><p><input type="submit" value=" Ausführen "></p></form>';
                echo '</div></div>';
                $layout->createlayout_bottom();
                $get->put_file($DATADIR . '/tmp/transfer_' . $tcode . '.txt', $type . '|' . $recip['id'] .
                    '|' . $credits . '|' . $fin);
                $dbc->db_query('UPDATE users SET tcode=\'' . mysql_escape_string($tcode) . '\' WHERE id=\'' .
                    $usrid . '\' LIMIT 1;');

            } else
                header('Location: cluster.php?sid=' . $sid . '&page=finances&error=' . urlencode
                    ($e));
        } else
            $gres->no_();
        break;

    case 'transfer2': // ------------------------- TRANSFER 2 ------------------------

        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] == CS_WARLORD || $usr['clusterstat'] ==
            CS_KONVENTIONIST || $usr['clusterstat'] == CS_SUPPORTER || $usr['clusterstat'] ==
            CS_COADMIN)
        {

            $code = $_REQUEST['tcode'];
            $fn = $DATADIR . '/tmp/transfer_' . $code . '.txt';
            if ($usr['tcode'] != $code || file_exists($fn) != true)
            {
                $gres->simple_message('überweisung ungültig! Bitte neu erstellen!');
                break;
            }
            $dat = explode('|', $get->get_file($fn));
            @unlink($fn);

            if (@count($dat) == 4)
            {
                $cluster['money'] -= $dat[2];
                if ($dat[0] == 'user')
                {
                    $recip = $get->get_pc($dat[1]);
                    $recip['credits'] += $dat[3];
                    $dbc->db_query('UPDATE pcs SET credits=\'' . mysql_escape_string($recip['credits']) .
                        '\' WHERE id=' . mysql_escape_string($dat[1]));
                    $s = 'Der Cluster [cluster=' . $clusterid . ']' . $cluster['code'] .
                        '[/cluster] hat dir ' . $dat[2] . ' Credits auf deinen PC 10.47.' . $recip['ip'] .
                        ' (' . $recip['name'] . ') überwiesen.';
                    if ($dat[2] != $dat[3])
                        $s .= ' Abzüglich der Gebühren hast du ' . $dat[3] .
                            ' Credits erhalten!';
                    $ingame->addsysmsg($recip['owner'], $s);
                    $recip_usr = $get->get_User($recip['owner']);
                    $cluster['events'] = $gres->nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
                        '[/usr] hat ' . $dat[2] . ' Credits an [usr=' . $recip_usr['id'] . ']' . $recip_usr['name'] .
                        '[/usr] �berwiesen.' . LF . $cluster['events'];
                    $dbc->db_query('UPDATE clusters SET money=\'' . mysql_escape_string($cluster['money']) .
                        '\',events=\'' . mysql_escape_string($cluster['events']) . '\' WHERE id=' .
                        mysql_escape_string($cluster['id']));
                    $msg = 'überweisung an 10.47.' . $recip['ip'] . ' (' . $recip['name'] .
                        ') ausgeführt!';
                } elseif ($dat[0] == 'cluster')
                {
                    $c = $get->get_cluster($dat[1]);
                    $c['money'] += $dat[3];
                    $cluster['events'] = nicetime4() . ' [usr=' . $usrid . ']' . $usr['name'] .
                        '[/usr] �berweist ' . $dat[3] . ' Credits an den Cluster [cluster=' . $c['id'] .
                        ']' . $c['code'] . '[/cluster]' . LF . $cluster['events'];
                    $c[events] = nicetime4() . ' Der Cluster [cluster=' . $clusterid . ']' . $cluster['code'] .
                        '[/cluster] �berweist dem Cluster ' . $dat[3] . ' Credits.' . LF . $c['events'];
                    $dbc->db_query('UPDATE clusters SET money=\'' . mysql_escape_string($c['money']) . '\',events=\'' .
                        mysql_escape_string($c['events']) . '\' WHERE id=' . mysql_escape_string($dat[1]));
                    $dbc->db_query('UPDATE clusters SET money=\'' . mysql_escape_string($cluster['money']) .
                        '\',events=\'' . mysql_escape_string($cluster['events']) . '\' WHERE id=' .
                        mysql_escape_string($cluster['id']));
                    $msg = 'Dem Cluster ' . $c['code'] . ' wurden ' . $dat[2] .
                        ' Credits überwiesen!';
                }
                $dbc->db_query('INSERT INTO transfers VALUES(\'' . $clusterid . '\', \'cluster\', \'' .
                    $usrid . '\', \'' . mysql_escape_string($dat[1]) . '\', \'' .
                    mysql_escape_string($dat[0]) . '\', \'' . mysql_escape_string($recip['owner']) .
                    '\', \'' . mysql_escape_string($dat[3]) . '\', \'' . time() . '\');');
                header('Location: cluster.php?page=finances&sid=' . $sid . '&ok=' . urlencode($msg));
            }
        } else
            $gres->no_();
        break;

    case 'request1': // ------------------------- REQUEST 1 -----------------------
        $c = $get->get_cluster((int)$_REQUEST['cluster']);
        $members = @mysql_num_rows(db_query('SELECT * FROM users WHERE cluster=\'' . $c['id'] .
            '\''));
        if ($c === false || $c['acceptnew'] != 'yes' || $members >= MAX_CLUSTER_MEMBERS)
            exit;
        $layout->createlayout_top('HackTheNet - Cluster - Mitgliedsantrag');
        echo '<div class="content" id="cluster">
<h2>Cluster</h2>
<div id="cluster-request-new1">
<h3>Aufnahmeantrag stellen</h3>
<p><b>Antrag auf Aufnahme in den Cluster <a href="cluster.php?sid=' . $sid .
            '&cluster=' . $c['id'] . '&page=info">' . $c['code'] . '</a> stellen:</b></p>
<form action="cluster.php?page=request2&sid=' . $sid . '" method="post">
<input type="hidden" name="cluster" value="' . $c['id'] . '">
<p>
<textarea name="comment" rows=8 cols=50>Hallo!
Ich bin ' . $usr['name'] . ' und würde gerne eurem Cluster beitreten.
Wäre schön, wenn das ginge.

Also bis dann
' . $usr['name'] . '</textarea><br /><br />
Du wirst dann per System-Nachricht informiert, ob du aufgenommen wurdest oder nicht.
<br /><br /><input type="submit" value=" Abschicken ">
</p>
</form>
</div></div>';
        $layout->createlayout_bottom();
        break;

    case 'request2': // ------------------------- REQUEST 2 -----------------------
        $c = $get->get_cluster((int)$_REQUEST['cluster']);
        $members = @mysql_num_rows(db_query('SELECT id FROM users WHERE cluster=\'' .
            mysql_escape_string($c['id']) . '\''));
        if ($c === false || $c['acceptnew'] != 'yes' || $members >= MAX_CLUSTER_MEMBERS)
            exit;

        $dbc->db_query('INSERT INTO cl_reqs VALUES(\'' . $usrid . '\', \'' .
            mysql_escape_string($c['id']) . '\', \'' . mysql_escape_string(nl2br(safeentities
            ($_POST['comment']))) . '\', \'no\');');
        // sql-injenction-bug fixed (8.11.2004)

        $layout->createlayout_top('HackTheNet - Cluster - Mitgliedsantrag');
        echo '<div class="content" id="cluster">
<h2>Cluster</h2>
<div id="cluster-request-new2">
<h3>Aufnahmeantrag stellen</h3>
<p><b>Der Antrag auf Aufnahme in den Cluster <a href="cluster.php?sid=' . $sid .
            '&cluster=' . $c['id'] . '&page=info">' . $c['code'] . '</a> wurde abgesandt.
Wenn ein Admin oder ein Mitgliederminister des Clusters über deine Aufnahme entschieden
hat, wirst du per System-Nachricht informiert.</b></p>
</div></div>';
        $layout->createlayout_bottom();
        break;

    case 'req_verw': // ------------------------- REQUEST VERWALTUNG -----------------------
        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] ==
            CS_MITGLIEDERMINISTER || $usr['clusterstat'] == CS_COADMIN):

            $layout->createlayout_top('HackTheNet - Cluster - Mitgliedsanträge verwalten');
            echo '<div class="content" id="cluster">
<h2>Cluster</h2>
<div id="cluster-request-administration">
<h3>Aufnahmeanträge</h3>
' . $notif . '
<form action="cluster.php?page=savereqverw&sid=' . $sid . '" method="post">
<table cellpadding="3" cellspacing="2">
<tr><th>Spieler</th><th>Punkte</th><th>Kommentar</th><th>Aufnehmen</th><th>Ablehnen</th><th>Nicht ändern</th></tr>';

            $r = $dbc->db_query('SELECT * FROM cl_reqs WHERE cluster=' . $clusterid .
                ' AND dealed=\'no\'');
            while ($data = mysql_fetch_assoc($r))
            {
                $u = $get->get_user($data['user']);
                if ($u === false)
                {
                    $dbc->db_query('DELETE FROM cl_reqs WHERE user=' . mysql_escape_string($data['user']) .
                        ';');
                    continue;
                }
                echo '<tr><th><a href="user.php?page=info&sid=' . $sid . '&user=' . $u['id'] .
                    '" class="il">' . $u['name'] . '</a></th>';
                echo '<td>' . $u['points'] . '</td><td><tt>' . $data['comment'] . '</tt></td>';
                echo '<td><input type="radio" name="u' . $u['id'] . '" value="yes" /></td>';
                echo '<td><input type="radio" name="u' . $u['id'] . '" value="no" /></td>';
                echo '<td><input type="radio" name="u' . $u['id'] .
                    '" value="ignore" checked="checked" /></td>';
                echo '</tr>';
            }

            echo '<tr><th colspan="6" align="right"><input type="submit" value=" übernehmen "></th></tr>
</table></form>
</div></div>';
            $layout->createlayout_bottom();

        endif;
        break;


    case 'savereqverw': // ------------------------- SAVE REQUEST VERWALTUNG -----------------------
        if ($usr['clusterstat'] == CS_ADMIN || $usr['clusterstat'] ==
            CS_MITGLIEDERMINISTER || $usr['clusterstat'] == CS_COADMIN):

            $r = $dbc->db_query('SELECT * FROM cl_reqs WHERE cluster=' . $clusterid .
                ' AND dealed=\'no\'');
            $delstr = '';
            $acstr = '';
            while ($data = mysql_fetch_assoc($r))
            {
                $u = $get->get_user($data[user]);
                if ($u === false)
                    continue;
                $chs = $_POST['u' . $u['id']];
                if ($chs == 'yes')
                {
                    $ingame->addsysmsg($u['id'], 'Dein Aufnahmeantrag in den Cluster [cluster=' . $clusterid .
                        ']' . $cluster['code'] .
                        '[/cluster] wurde angenommen!<br />Klicke <a href="cluster.php?sid=%sid%&page=join&cluster=' .
                        $clusterid . '">hier</a> um deinen jetzigen Cluster zu verlassen und ' . $cluster['code'] .
                        ' beizutreten.');
                    $acstr .= 'user=' . mysql_escape_string($u['id']) . ' OR ';
                } elseif ($chs == 'no')
                {
                    $ingame->addsysmsg($u['id'], 'Dein Aufnahmeantrag in den Cluster [cluster=' . $clusterid .
                        ']' . $cluster['code'] . '[/cluster] wurde abgelehnt!');
                    $delstr .= 'user=' . mysql_escape_string($u['id']) . ' OR ';
                }
            }

            if ($delstr != '')
            {
                $delstr = substr($delstr, 0, strlen($delstr) - 4);
                $dbc->db_query('DELETE FROM cl_reqs WHERE (' . $delstr . ') AND cluster=' . $clusterid);
            }
            if ($acstr != '')
            {
                $acstr = substr($acstr, 0, strlen($acstr) - 4);
                $dbc->db_query('UPDATE cl_reqs SET dealed=\'yes\' WHERE (' . $acstr . ') AND cluster=' .
                    $clusterid);
            }

            header('Location: cluster.php?sid=' . $sid . '&page=req_verw&ok=' . urlencode('Die Aufnahmeanträge wurden bearbeitet!'));

        endif;
        break;

    case 'savenotice': // ------------------------- SAVE NOTICE -----------------------

        $n = $gres->safeentities($_POST['notice']);

        $dbc->db_query('UPDATE clusters SET notice=\'' . mysql_escape_string($n) . '\' WHERE id=' .
            $clusterid . ';');

        $layout->createlayout_top('HackTheNet - Cluster-Notiz');
        echo '<div class="content" id="cluster-notice-saved">' . "\n";
        echo '<h2>Cluster-Notiz</h2>' . "\n";
        echo '<div class="ok">' . LF . '<h3>Aktion ausgef�hrt</h3>' . LF .
            '<p>Notiz gespeichert!</p></div>';
        echo '</div>';
        $layout->createlayout_bottom();

        $dbc->db_query('INSERT INTO logs SET type=\'chclinfo\', usr_id=\'0\', payload=\'' .
            mysql_escape_string($usr['name']) . ' changes notice of ' . mysql_escape_string
            ($cluster['code']) . '\';');

        break;

}




?>
