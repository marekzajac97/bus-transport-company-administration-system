<?php
  function printOptionsKier($selectedValue = -1){
    include('config.php');
    $sql = "SELECT * FROM kierowcy ORDER BY nazwisko, imie";
    $result = pg_query($db,$sql);
    $rows = pg_fetch_all($result);
    if(pg_num_rows($result) > 0){
      foreach ($rows as $row) {
          $selected = '';
          if(intval($row['kierid']) == $selectedValue){
            $selected = 'selected="selected"';
          }
          $select_string = $row['imie'] . ' ' . $row['nazwisko'] . ' (' . $row['telefon'] . ')';
          echo '<option ' . $selected . ' value="' . $row['kierid'] . '">' . $select_string . '</option>' ;
      }
    }
    pg_close($db);
  }
  function printOptionsAuto($selectedValue = -1){
    include('config.php');
    $sql = "SELECT * FROM autokary ORDER BY model, producent";
    $result = pg_query($db,$sql);
    $rows = pg_fetch_all($result);
    if(pg_num_rows($result) > 0){
      foreach ($rows as $row) {
          $selected = '';
          if(intval($row['autoid']) == $selectedValue){
            $selected = 'selected="selected"';
          }
          $select_string = $row['producent'] . ' ' . $row['model'] . ' (' . $row['lmiejsc'] . ')';
          echo '<option ' . $selected . ' value="' . $row['autoid'] . '">' . $select_string . '</option>' ;
      }
    }
    pg_close($db);
  }
  function printOptionsKursy($selectedValue = -1){
    include('config.php');
    $sql = "SELECT * FROM kursy k JOIN linie l USING(lid) ORDER BY data_odjazdu";
    $result = pg_query($db,$sql);
    $rows = pg_fetch_all($result);
    if(pg_num_rows($result) > 0){
      foreach ($rows as $row) {
          //$auto_name = autoidToName($row['autoid'],$db);
          //$kier_name = kieridToName($row['kierid'],$db);

          $start_pid = intval($row['start_pid']);
          $start_name = pidToName($start_pid,$db);
          $end_pid = convertToArray($row['end_pid_dist'])[0];
          $end_name = pidToName($end_pid,$db);

          $selected = '';
          if(intval($row['kursid']) == $selectedValue){
            $selected = 'selected="selected"';
          }
          $select_string = $row['data_odjazdu'] . ' ( ' . $start_name . ' - ' . $end_name . ')';
          echo '<option ' . $selected . ' value="' . $row['kursid'] . '">' . $select_string . '</option>' ;
      }
    }
    pg_close($db);
  }
  function printOptionsLines($selectedValue = -1){
    include('config.php');
    $sql = "SELECT * FROM linie ORDER BY lid";
    $result = pg_query($db,$sql);
    $rows = pg_fetch_all($result);
    if(pg_num_rows($result) > 0){
      foreach ($rows as $row) {
        $start_pid = intval($row['start_pid']);
        $start_name = pidToName($start_pid,$db);

        $end_pid = convertToArray($row['end_pid_dist'])[0];
        $end_name = pidToName($end_pid,$db);

        $end_dist = convertToArray($row['end_pid_dist'])[1];

        $inter_names = '';
        if(isset($row['inter_pid_dist'])){
          $inter_pid_dist = convertToArray($row['inter_pid_dist']);
          foreach ($inter_pid_dist as $pid_dist_pair) {       
            $inter_pid = $pid_dist_pair[0];
            $inter_dist = $pid_dist_pair[1];
            $inter_name = pidToName($inter_pid,$db);
            $inter_names .= $inter_name . " ";
          }
        }else{
          $inter_names = 'Brak przystanków pośrednich';
        }
        $selected = '';
        if(intval($row['lid']) == $selectedValue){
          $selected = 'selected="selected"';
        }
        $select_string = "$start_name $end_name [Przez $inter_names]";
        echo '<option ' . $selected . ' value="' . $row['lid'] . '">' . $select_string . '</option>' ;
      }
    }
    pg_close($db);
  }
  function printOptionsBusstops($selectedValue = -1){
    include('config.php');
    $sql = "SELECT * FROM przystanki ORDER BY nazwa";
    $result = pg_query($db,$sql);
    $rows = pg_fetch_all($result);
    if(pg_num_rows($result) > 0){
      foreach ($rows as $row) {
        $selected = '';
        if(intval($row['pid']) == $selectedValue){
          $selected = 'selected="selected"';
        }
        $select_string = $row['nazwa'];
        echo '<option ' . $selected . ' value="' . $row['pid'] . '">' . $select_string . '</option>' ;
      }
    }
    pg_close($db);
  }
  function printOptionsBusstopsByKursId($kursid, $selectedValue = -1){
    include('config.php');
    $sql = "SELECT * FROM kursy JOIN linie USING(lid) WHERE kursid=$kursid";
    $result = pg_query($db,$sql);
    $row = pg_fetch_all($result);
    if(pg_num_rows($result) == 1){
      $start_pid = intval($row[0]['start_pid']);
      $end_pid = convertToArray($row[0]['end_pid_dist'])[0];
      $inter_pids = array();
      if(isset($row[0]['inter_pid_dist'])){
        $inter_pid_dist = convertToArray($row[0]['inter_pid_dist']);
        foreach ($inter_pid_dist as $pid_dist_pair) {       
          $inter_pid = $pid_dist_pair[0];
          array_push($inter_pids, $inter_pid);
        }
      }
      array_unshift($inter_pids, $start_pid);
      array_push($inter_pids, $end_pid);
      $all_pids = $inter_pids;

      foreach ($all_pids as $pid) {
        $selected = '';
        if($pid == $selectedValue){
          $selected = 'selected="selected"';
        }
        echo '<option ' . $selected . ' value="' . $pid . '">' . pidToName($pid,$db) . '</option>' ;
      }
    }
  }
  function printOptionsBookings($selectedValue = -1){
    include('config.php');
    $sql = "SELECT * FROM rezerwacje JOIN kursy USING(kursid) JOIN linie USING(lid) ORDER BY imie, nazwisko";
    $result = pg_query($db,$sql);
    $rows = pg_fetch_all($result);
    if(pg_num_rows($result) > 0){
      foreach ($rows as $row) {
        $start_pid = intval($row['start_pid']);
        $start_name = pidToName($start_pid,$db);
        $end_pid = convertToArray($row['end_pid_dist'])[0];
        $end_name = pidToName($end_pid,$db);
        $selected = '';
        if(intval($row['rezid']) == $selectedValue){
          $selected = 'selected="selected"';
        }
        $select_string = $row['imie'] . " " . $row['nazwisko'] . "  [ " . $row['data_odjazdu'] . ' ' . $start_name . ' - ' . $end_name . ' ]';
        echo '<option ' . $selected . ' value="' . $row['rezid'] . '">' . $select_string . '</option>' ;
      }
    }
    pg_close($db);
  }
?>