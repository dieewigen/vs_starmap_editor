<?php
session_start();
include 'inccon.php';

//Funktion zum Anlegen der Sektordaten, falls nicht vorhanden
function check4sectordata($sec_id){
	$sql="INSERT INTO de_basedata_map_sector (sec_id) VALUES ('$sec_id');";
	//echo $sql;
	mysqli_query($GLOBALS['dbi'], $sql);
}

if(isset($_REQUEST['sec_id'])){
	$sec_id=intval($_REQUEST['sec_id']);
}else{
	$sec_id=intval($_SESSION['sec_id']);
}

if($sec_id<0){
	$sec_id=0;
}
$_SESSION['sec_id']=$sec_id;


//neuen Knoten erstellen?
if(isset($_REQUEST['new_knoten']) && $_REQUEST['new_knoten']==1){
	check4sectordata($_SESSION['sec_id']);

	$knoten_x=intval($_REQUEST['knoten_x']);
	$knoten_y=intval($_REQUEST['knoten_y']);

	if($knoten_x>50 && $knoten_x<950 && $knoten_y>50 && $knoten_y<950){
		//maximale knoten_id auslesen
		$db_daten=mysqli_query($GLOBALS['dbi'], "SELECT MAX(knoten_id) AS knoten_id FROM de_basedata_map_knoten WHERE sec_id='$sec_id'");
		$row = mysqli_fetch_array($db_daten);
		$knoten_id=$row['knoten_id']+1;
			
		$sql="INSERT INTO de_basedata_map_knoten (sec_id, knoten_id, pos_x, pos_y) VALUES ('$sec_id','$knoten_id', '$knoten_x','$knoten_y');";
		//echo $sql;
		mysqli_query($GLOBALS['dbi'], $sql);
	}
}

//Knoten löschen
if(isset($_REQUEST['delete_knoten']) && $_REQUEST['delete_knoten']==1){
	check4sectordata($_SESSION['sec_id']);

	$knoten_id=intval($_REQUEST['knoten_id']);
	
	//knoten
	$sql="DELETE FROM de_basedata_map_knoten WHERE sec_id='$sec_id' AND knoten_id='$knoten_id';";
	//echo $sql;
	mysqli_query($GLOBALS['dbi'], $sql);

	//mit dem knoten verbundene kanten
	$sql="DELETE FROM de_basedata_map_kanten WHERE sec_id='$sec_id' AND (knoten_id1='$knoten_id' OR knoten_id2='$knoten_id');";
	//echo $sql;
	mysqli_query($GLOBALS['dbi'], $sql);	
}

//neue Kante erstellen?
if(isset($_REQUEST['new_kante']) && $_REQUEST['new_kante']==1){
	check4sectordata($_SESSION['sec_id']);
	
	$knoten_id1=intval($_REQUEST['knoten_id1']);
	$knoten_id2=intval($_REQUEST['knoten_id2']);

	$sql="INSERT INTO de_basedata_map_kanten (sec_id, knoten_id1, knoten_id2) VALUES ('$sec_id','$knoten_id1', '$knoten_id2');";
	//echo $sql;
	mysqli_query($GLOBALS['dbi'], $sql);
}

//Kante löschen
if(isset($_REQUEST['delete_kante']) && $_REQUEST['delete_kante']==1){
	check4sectordata($_SESSION['sec_id']);
	
	$knoten_id1=intval($_REQUEST['knoten_id1']);
	$knoten_id2=intval($_REQUEST['knoten_id2']);
		
	//knoten
	$sql="DELETE FROM de_basedata_map_kanten WHERE sec_id='$sec_id' AND knoten_id1='$knoten_id1' AND knoten_id2='$knoten_id2';";
	//echo $sql;
	mysqli_query($GLOBALS['dbi'], $sql);
	
}

//Sektor-Status setzen
if(isset($_REQUEST['set_sector_active'])){
	check4sectordata($_SESSION['sec_id']);

	$status=intval($_REQUEST['set_sector_active']);
			
	$sql="UPDATE de_basedata_map_sector SET active='$status' WHERE sec_id='$sec_id';";
	//echo $sql;
	mysqli_query($GLOBALS['dbi'], $sql);

}
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8"/>
    <title>Starmap-Editor</title>
	<script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php

echo '<div style="display: flex; width: 100%;">';

////////////////////////////////////////////////////////////////////////
//Karte
////////////////////////////////////////////////////////////////////////
echo '<div style="width: 1020px;">';

//Bereich zum Blättern
echo '<div style="width: 100%; text-align:left;">';
echo '<a href="index.php" class="btn">Reload</a>&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<a href="?sec_id='.($sec_id-1).'" class="btn">&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;';
echo $sec_id;
echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="?sec_id='.($sec_id+1).'" class="btn">&gt;</a>';
echo '</div>';

//Kartendaten
echo '<div id="mapcontent" style="width: 1000px; height: 1000px; margin-top: 40px; background-color: #EFEFEF; position: relative;">';

//Knoten laden und anzeigen
$db_daten=mysqli_query($GLOBALS['dbi'], "SELECT * FROM de_basedata_map_knoten WHERE sec_id='$sec_id'");
$knoten_daten=array();
while($row = mysqli_fetch_array($db_daten)){

	//knoten zeichnen
	echo '<div class="knoten" data-knotenid="'.$row['knoten_id'].'" 
	style="position: absolute; z-index: 10; background-color: #333333; width: 100px; height: 100px; text-align: center; color: #FEFEFE; font-size: 30px; padding-top: 30px; box-sizing: border-box;
	top: '.($row['pos_y']-50).'px; left: '.($row['pos_x']-50).'px;">'.
	$row['knoten_id'].'</div>';

	$knoten_daten[$row['knoten_id']]=$row;
}

//Kanten laden und anzeigen
$db_daten=mysqli_query($GLOBALS['dbi'], "SELECT * FROM de_basedata_map_kanten WHERE sec_id='$sec_id'");
echo '<svg data-svg="1" width="1000" height="1000">';
while($row = mysqli_fetch_array($db_daten)){

	//kanten zeichnen
	echo '<line data-svg="1" x1="'.$knoten_daten[$row['knoten_id1']]['pos_x'].'" y1="'.$knoten_daten[$row['knoten_id1']]['pos_y'].'" x2="'.$knoten_daten[$row['knoten_id2']]['pos_x'].'" y2="'.$knoten_daten[$row['knoten_id2']]['pos_y'].'" stroke="black"/>';
}
echo '</svg>';

echo '</div>';//mapcontent ende

echo '</div>';//spalte ende


////////////////////////////////////////////////////////////////////////
//Funktionen
////////////////////////////////////////////////////////////////////////
echo '<div style="flex-grow: 1;">';

//Sektordaten
$db_daten=mysqli_query($GLOBALS['dbi'], "SELECT * FROM de_basedata_map_sector WHERE sec_id='$sec_id';");
$row = mysqli_fetch_array($db_daten);

echo '<div style="font-weight: bold;">Sektor</div>';
if(isset($row['active']) && $row['active']==0){
	echo '<a href="?set_sector_active=1">Aktiv: nein</a>';
}else{
	echo '<a href="?set_sector_active=0">Aktiv: ja</a>';
}



//Kanten
$db_daten=mysqli_query($GLOBALS['dbi'], "SELECT * FROM de_basedata_map_kanten WHERE sec_id='$sec_id' ORDER BY knoten_id1 ASC, knoten_id2 ASC");
echo '<br><br><div style="font-weight: bold;">Kanten</a>';
while($row = mysqli_fetch_array($db_daten)){

	//kanten zeichnen
	echo '<div>';
	echo $row['knoten_id1'].':'.$row['knoten_id2'];
	echo '&nbsp;<a href="?delete_kante=1&knoten_id1='.$row['knoten_id1'].'&knoten_id2='.$row['knoten_id2'].'">DEL</a>';
	
	echo '</div>';
}



echo '</div>';//spalte ende





echo '</div>';//grid end

?>
	
<script type="text/javascript">
	$( document ).ready(function() {

		$(document).keydown(function(event){
		if(event.which=="17")
			cntrlIsPressed = true;
	});

	$(document).keyup(function(){
		cntrlIsPressed = false;
	});

	var cntrlIsPressed = false;	

	$("#mapcontent").click(function(e){
		var x = e.pageX - this.offsetLeft;
		var y = e.pageY - this.offsetTop;

		window.location.href = "index.php?new_knoten=1&knoten_x="+x+"&knoten_y="+y;
	}).children().click(function(e) {

		if($(this).data('svg')==1){

		}else{
			return false;
		}
	});

	var connect_id1=-1;
	var connect_id2=-1;
	
	$(".knoten").click(function(e){

		if(cntrlIsPressed){
			window.location.href = "index.php?delete_knoten=1&knoten_id="+$(this).data('knotenid');	
		}else{
			if(connect_id1==-1){
				connect_id1=$(this).data('knotenid');
			}else{
				connect_id2=$(this).data('knotenid');

				window.location.href = "index.php?new_kante=1&knoten_id1="+connect_id1+"&knoten_id2="+connect_id2;	
			}
		}
	});

	/*
	$("#mapcontent").mouseup(function(e){
		var position = $("#mapcontent").position();
		var xk=e.clientX-position.left-500;
		var yk=500-(e.clientY-position.top);

		window.location.href = "index.php?new_knoten=1&knoten_x="+xk+"&knoten_y="+yk;
	}
	);
	*/
});

function lnk(parameter, target){
	$.getJSON('tt_ajax.php?'+parameter,
	function(data){
		$(target).html(data[0].output);
	});
}
</script>
</body>
</html>