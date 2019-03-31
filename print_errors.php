<?php  
function print_errors($errors) {
  if (count($errors) > 0) {
	  foreach ($errors as $error) {
	  	echo $error;
	  	echo "<br>"; 
	  }
  }
}
function phpToJsArray($phpArray) {
	$jsArray ='[';
	$i=0;
	foreach ($phpArray as $pidAndDist) {
		if($i==0){
			$jsArray .= '[' . $pidAndDist[0] . ',' . $pidAndDist[1] . ']';
		}else{
			$jsArray .= ', [' . $pidAndDist[0] . ',' . $pidAndDist[1] . ']';
		}
		$i+=1;
	}
	$jsArray .=']';
	return $jsArray;
}
function open_modal($id,$InterData = '[]') {
	echo "<script>window.onload=function(){
		var temp = $InterData;
		if(temp.length != 0){
			printInterSelectsUpdate(temp);
		}
		document.getElementById('$id').style.display='block';
	}</script>";
}
function convertToArray($stringarray) {
	$output = array();
	$stringarray = str_replace("{{","",$stringarray,$count);
	$stringarray = str_replace("}}","",$stringarray,$count);
	if($count == 1){
			
		$array = preg_split("/},{/", $stringarray);
		foreach ($array as $idAndDistString) {
			$idAndDistArray = explode(",", $idAndDistString);
			$id = intval($idAndDistArray[0]);
			$distance = intval($idAndDistArray[1]);
			array_push($output, array($id,$distance));
		}
	}else{
		$stringarray = str_replace("{","",$stringarray);
		$stringarray = str_replace("}","",$stringarray);

		$idAndDistArray = explode(",", $stringarray);
		$id = intval($idAndDistArray[0]);
		$distance = intval($idAndDistArray[1]);
		array_push($output, $id);
		array_push($output, $distance);
	}
	return $output;
}
function pidToName($pid,$db) {
    $sql = "SELECT nazwa FROM przystanki WHERE pid=$pid";
    $result = pg_query($db,$sql);
    if(pg_num_rows($result) == 1) {
      $temp = pg_fetch_array($result,0,PGSQL_ASSOC);
      $name = $temp['nazwa'];
    }
    return $name;
}
function kieridToName($kierid,$db) {
    $sql = "SELECT imie, nazwisko FROM kierowcy WHERE kierid=$kierid";
    $result = pg_query($db,$sql);
    if(pg_num_rows($result) == 1) {
      $temp = pg_fetch_array($result,0,PGSQL_ASSOC);
      $name = $temp['imie'] . " " . $temp['nazwisko'];
    }
    return $name;
}
function autoidToName($autoid,$db) {
    $sql = "SELECT * FROM autokary WHERE autoid=$autoid";
    $result = pg_query($db,$sql);
    if(pg_num_rows($result) == 1) {
      $temp = pg_fetch_array($result,0,PGSQL_ASSOC);
      $name = $temp['producent'] . " " . $temp['model'] . " [" . $temp['lmiejsc'] . "]";
    }
    return $name;
}
function lidToName($lid,$db) {
    $sql = "SELECT * FROM linie WHERE lid=$lid";
    $result = pg_query($db,$sql);
    if(pg_num_rows($result) == 1) {
      $temp = pg_fetch_array($result,0,PGSQL_ASSOC);
      $start_pid = pidToName($temp['start_pid'],$db);
      $end_pid = pidToName(convertToArray($temp['end_pid_dist'])[0],$db);
      $name = $start_pid . " - " . $end_pid;
    }
    return $name;
}
?>