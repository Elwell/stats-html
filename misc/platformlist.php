<?

# $Id: platformlist.php,v 1.7 2002/04/07 21:51:07 paul Exp $

$hour = 3;
$now = getdate();
if ($now['hours'] >= 0 and $now['hours'] < $hour) {
	$now = time();
} else {
	$now = time() + 86400;
}

Header("Cache-Control: must-revalidate");
Header("Expires: " . gmdate("D, d M Y", $now) . " $hour:00 GMT");

/// Variables passed in url
//   source == "y" for yseterday, all other values ignored.
//   view == display and sort order (t = total blocks, c = cpu, o = os, v = version)
//           page will show those columns in view, sorted in view's order

 if (!$view) {
   $view = "c";
 }

 include "../etc/config.inc";
 include "../etc/modules.inc";
 include "etc/project.inc";

 $lastupdate = last_update('e');
 $title = "CPU Participation";

 include "templates/header.inc";
 
 $selstr = "select";
 $frostr = "from Platform_Contrib p,";
 $whestr = "where p.PROJECT_ID = $project_id";
 $grostr = "group by";
 $ordstr = "order by";
 if("$source" == "y") {
   $whestr .= " and DATE = (select max(DATE) from Platform_Contrib)";
 }

 for($i=0; $i < strlen($view); $i++) {
   $ch = substr($view,$i,1);
   if($ch == 'c') $fieldname = "p.CPU";
   if($ch == 'o') $fieldname = "p.OS";
   if($ch == 'v') $fieldname = "p.VER";
   if($ch == 't') $fieldname = "p.PROJECT_ID";
   $selstr .= " $fieldname,";
   $grostr .= " $fieldname,";
 }


 $selstr .= " min(p.DATE) as first, max(p.DATE) as last, sum(p.WORK_UNITS)/$proj_divider as total,";

 for($i=0; $i < strlen($view); $i++) {
   $ch = substr($view,$i,1);
   if($ch == 'c') {
     $selstr = "$selstr min(c.name) as cpuname, min(c.image) as cpuimage,";
     $frostr = "$frostr STATS_cpu c,";
     $whestr = "$whestr and c.cpu = p.CPU";
     $ordstr = "$ordstr p.CPU,";
   }
   if($ch == 'o') {
     $selstr = "$selstr min(o.name) as osname, min(o.image) as osimage,";
     $frostr = "$frostr STATS_os o,";
     $whestr = "$whestr and o.os = p.OS";
     $ordstr = "$ordstr p.OS,";
   }
   if($ch == 'v') {
     $ordstr = "$ordstr p.VER,";
   }
   if($ch == 't') {
     $ordstr = "$ordstr total desc,";
   }
 }

 $selstr = substr($selstr,0,strlen($selstr)-1);
 $frostr = substr($frostr,0,strlen($frostr)-1);
 $grostr = substr($grostr,0,strlen($grostr)-1);
 $ordstr = substr($ordstr,0,strlen($ordstr)-1);

 $QSlist = "$selstr $frostr $whestr $grostr $ordstr";
 $result = sybase_query($qs);
 $par = sybase_fetch_object($result);
 sybase_query("set rowcount 0");
 $result = sybase_query($QSlist);

 debug_text("<!-- QSlist: $QSlist, result: $result -->", $debug);
 err_check_query_results($result);
 
 $rows = sybase_num_rows($result);
 $cols = 3;
 print "
    <center>
     <br>
     <table border=\"1\" cellspacing=\"0\" cellpadding\"0\" bgcolor=$header_bg>
      <tr>";
 for($i=0; $i < strlen($view); $i++) {
   $ch = substr($view,$i,1);
   if($ch == 'c') {
     print "<th>CPU</th>";
     $cols++;
   }
   if($ch == 'o') {
     print "<th>OS</th>";
     $cols++;
   }
   if($ch == 'v') {
     print "<th>Version</th>";
     $cols++;
   }
 }
?> 
       <th align="right">First Unit</th>
       <th align="right">Last Unit</th>
       <th align="right">Total <?=$proj_unitname?></th>
      </tr>
<?
$totalwu = 0;
$totalblocks = 0;
 for ($i = 0; $i<$rows; $i++) {

?>
<tr class="<?=row_background_color($i)?>">
<?
	sybase_data_seek($result,$i);
	$par = sybase_fetch_object($result);

	$totalwu += (double) $par->total ;
	$decimal_places=0;
	$totalf=number_style_convert( (double) $par->total );
	$firstd = sybase_date_format_long($par->first);
	$lastd = sybase_date_format_long($par->last);

	for($j=0; $j < strlen($view); $j++) {
	  $ch = substr($view,$j,1);
	  if($ch == 'c') print "<td><img alt=\"\" height=\"14\" width=\"14\" src=\"/images/icons/cpu/$par->cpuimage\"> $par->cpuname</td>";
	  if($ch == 'o') print "<td><img alt=\"\" height=\"14\" width=\"14\" src=\"/images/icons/os/$par->osimage\"> $par->osname</td>";
	  if($ch == 'v') print "<td>$par->VER</td>";
	}

	print "
		<td align=\"right\">$firstd</td>
		<td align=\"right\">$lastd</td>
		<td align=\"right\">$totalf</td>
		</tr>
	";
 }
 $totalblocks = number_format($totalblocks, 0);

 $padding = (int) $cols - 1;
 $ftotalwu = number_style_convert( $totalwu );
?> 
	 <tr bgcolor=<?=$footer_bg?>>
	  <td align="right" colspan="<?=$padding?>"><font <?=$footer_font?>>Total</font></td>
	  <td align="right"><font <?=$footer_font?>><?=$ftotalwu?></font></td>
	 </tr>
	</table>
<?include "templates/footer.inc";?>
