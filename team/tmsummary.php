<?
// vi: ts=2 sw=2 tw=120
// $Id: tmsummary.php,v 1.32 2003/09/01 16:33:50 decibel Exp $

// Variables Passed in url:
//  team == team id to display

include "../etc/config.inc";
include "../etc/modules.inc";
include "../etc/project.inc";
include "../etc/markup.inc";
include "../etc/team.php";
include "../etc/teamstats.php";

$title = "Team #$tm Summary";
$lastupdate = last_update('t');
include "../templates/header.inc";

// Query server
$team = new Team($gdb, $gproj, $tm);
if($team->get_id() == 0) {
	echo "<H2>That team is not known.</H2><BR>";
	include "../templates/footer.inc";
	exit;
}

$qs = "select *
        from Daily_Summary nolock
        where PROJECT_ID = $project_id
          and DATE = (select max(DATE) from Daily_Summary where project_id=$project_id)";
$result = $gdb->query($qs);
$yest_totals = $gdb->fetch_object($result);

$stats = $team->get_current_stats();

$neighbors = $team->get_neighbors();

if (private_markupurl_safety($team->get_logo()) != "") {
  $logo = "<img src=\"".$team->get_logo()."\" alt=\"team logo\">";
} else {
  $logo = "";
}
?>
<div style="text-align:center;">
<h1 class="phead"><?= safe_display($team->get_name()) ?></h1>
  <table width="100%">
    <tr>
      <td align="center"><?= $logo ?></td>
      <td align="center"><?= markup_to_html($team->get_description()) ?></td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        Contact: <?= safe_display($team->get_contact_email()) ?>
      </td>
    </tr>
  </table>
  <br>
  <br>
  <table cellspacing="4" style="margin: auto;">
    <tr>
      <td></td>
      <td align="center" class="phead2">Overall</td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td align="center" class="phead2">Yesterday</td>
<? } ?>
    </tr>
    <tr>
      <td align="left" class="phead2">Rank:</td>
      <td align="right"><?= $stats->get_stats_item('overall_rank') . " " . html_rank_arrow($stats->get_stats_item('overall_rank_previous') - $stats->get_stats_item('overall_rank')) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td align="right"><?= $stats->get_stats_item('day_rank') . " " . html_rank_arrow($stats->get_stats_item('day_rank_previous') - $stats->get_stats_item('day_rank')) ?></td>
<? } ?>
    </tr>
    <tr>
      <td align="left" class="phead2"><?= $gproj->get_scaled_unit_name() ?>:</td>
      <td align="right"><?= number_style_convert($stats->get_stats_item('work_total') * $gproj->get_scale()) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td align="right"><?= number_style_convert($stats->get_stats_item('work_today') * $gproj->get_scale()) ?></td>
<? } ?>
    </tr>
    <? if ($stats->get_stats_item('days_working') > 0) { ?>
    <tr>
      <td align="left" class="phead2"><?= $gproj->get_scaled_unit_name() ?>/sec:</td>
      <td align="right"><?= number_style_convert($stats->get_stats_item('work_total') * $gproj->get_scale() / (86400 * $stats->get_stats_item('days_working'))) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td align="right"><?= number_style_convert($stats->get_stats_item('work_today') * $gproj->get_scale() / 86400) ?></td>
    <? } ?>
    </tr>
<? } ?>
    <?if($stats->get_stats_item('members_overall') > 0) {?>
    <tr>
      <td align="left" class="phead2"><?= $gproj->get_scaled_unit_name() ?>/member:</td>
      <td align="right"><?= number_style_convert($stats->get_stats_item('work_total') * $gproj->get_scale() / $stats->get_stats_item('members_overall')) ?></td>
<? if ($stats->get_stats_item('work_today') > 0) { ?>
      <td align="right"><?= number_style_convert($stats->get_stats_item('work_today') * $gproj->get_scale() / $stats->get_stats_item('members_today')) ?></td>
<? } ?>
    </tr>
    <?}?>
    <!-- tr>
      <td align="left" class="phead2"><?= $proj_unscaled_unit_name ?>:</td>
      <td align="right"><?= number_style_convert($par->WORK_TOTAL) ?></td>
<? if ($par->WORK_TODAY > 0) { ?>
      <td align="right"><?= number_style_convert($par->WORK_TODAY) ?></td>
<? } ?>
    </tr>
    <?if ($par->Days_Working > 0) { ?>
    <tr>
      <td align="left" class="phead2"><?= $proj_unscaled_unit_name ?>/sec:</td>
      <td align="right"><?= number_style_convert($par->WORK_TOTAL / (86400 * $par->Days_Working)) ?></td>
<? if ($par->WORK_TODAY > 0) { ?>
      <td align="right"><?= number_style_convert($par->WORK_TODAY / 86400) ?></td>
<? } ?>
    </tr>
    <?}?>
    <?if($par->MEMBERS_OVERALL > 0) {?>
    <tr>
      <td align="left" class="phead2"><?= $proj_unscaled_unit_name ?>/member:</td>
      <td align="right"><?= number_style_convert($par->WORK_TOTAL / $par->MEMBERS_OVERALL) ?></td>
<? if ($par->WORK_TODAY > 0) { ?>
      <td align="right"><?= number_style_convert($par->WORK_TODAY / $par->MEMBERS_TODAY) ?></td>
<? } ?>
    </tr>
    <?}?>
    -->
    <tr>
      <td align="left" class="phead2">Time Working:</td>
      <td align="right" colspan="<?= ($stats->get_stats_item('work_today') > 0) ? 3 : 2 ?>"><?= number_style_convert($stats->get_stats_item('days_working')) ?> days</td>
    </tr>
  </table>
  <br>
  <br>
  <? if($gproj->get_total_units() > 0 && $stats->get_stats_item('work_today') == 0)
     {
   ?>
  The odds are 1 in a zillion-trillion that this team will find the key before anyone else does.
  <?} else if ($gproj->get_total_units() > 0 && $stats->get_stats_item('work_today') > 0) { ?>
  The odds are 1 in <?= number_style_convert($yest_totals->work_units / $stats->get_stats_item('work_today')) ?> that this team will
    find the key before anyone else does. 
  <? } ?>
  <br>
  <p style="text-align: center">
    This team has had <?= number_style_convert($stats->get_stats_item('members_overall')) ?> participants contribute blocks.
    Of those, <?= number_style_convert($stats->get_stats_item('members_current')) ?> are still on this team,
    and <?= number_style_convert($stats->get_stats_item('members_today')) ?> submitted work today.
  </p>
  <?
  //Some buttons to view team history will go here
  if ($team->get_show_members() == "NO") {
    ?>	
    <p style="text-align:center">This team wishes to keep its membership private.<p></center>
  <? } else {  
    if ($stats->get_stats_item('work_today') == 0) {
      print "<p style=\"text-align:center\">Click here to view this team's 
      <a href=\"tmember.php?project_id=$project_id&amp;team=$tm\">overall</a> participant stats";
    } else {
      print "<p style=\"text-align: center;\">Click here to view this team's participant stats for
      <a href=\"tmember.php?project_id=$project_id&amp;team=$tm&amp;source=y\">yesterday</a> or
      <a href=\"tmember.php?project_id=$project_id&amp;team=$tm\">overall</a>";
    }
	
    if ($team->get_show_members() == "PAS") {
      print " (Password required)";
    }

    print ".</p>";
  }

  //A list of teams goes here
  ?> 
    <table border="1" cellspacing="0" style="margin: auto;">
      <tr>
        <th class="thead">Rank</th>
        <th class="thead">Team</th>
        <th class="thead" align="right">Days</th>
        <th class="thead" align="right"><?= $gproj->get_scaled_unit_name() ?></th>
      </tr>
      <?
      $totalwork = 0;
      for ($i = 0; $i < count($neighbors); $i++) {
        $tmpStats = $neighbors[$i]->get_current_stats();
      ?>
        <tr class="<?= row_background_color($i) ?>">
        <?        
        $totalwork += $tmpStats->get_stats_item('work_total');
        ?>
          <td><?= $tmpStats->get_stats_item('overall_rank') . " " . html_rank_arrow($tmpStats->get_stats_item('overall_rank_previous') - $tmpStats->get_stats_item('overall_rank')) ?></td>
          <td>
              <a href="tmsummary.php?project_id=<?= $project_id ?>&amp;team=<?= $neighbors[$i]->get_id() ?>"><?= $neighbors[$i]->get_name() ?></a>
          </td>
          <td align="right"><?= number_style_convert($tmpStats->get_stats_item('days_working')) ?></td>
          <td align="right"><?= number_style_convert($tmpStats->get_stats_item('work_total') * $gproj->get_scale()) ?></td>
        </tr>
      <?
      }
      ?>
      <tr>
        <td class="tfoot" align="right" colspan="3">Total</td>
        <td class="tfoot" align="right"><?= number_style_convert($totalwork * $gproj->get_scale()) ?></td>
      </tr>
    </table>
    <hr>
    <!--
    <a href="/participant/pjointeam.php?team=<?=$tm?>">I want to join this team!</a>
    <hr>
    <form action="tmedit.php" method="post">
      <p>
        Edit this team's information 
        <br>
        Password:
        <input name="pass" size="8" maxlength="8" type="password">
        <input name="team" type="hidden" value="<?=$team->get_id()?>">
        <input value="Edit" type="submit">
      </p>
    </form>
    <form action="tmpass.php"><p>
    If you are the team coordinator, and you've forgotten your team password,<br> click
    <input type="hidden" name="team" value="<?=$team->get_id()?>">
    <input type="submit" value="here"> and the password will be mailed to
    <?=$team->get_contact_name()?>.
    </p></form>
    -->
  </div>

<? include "../templates/footer.inc"; ?>
