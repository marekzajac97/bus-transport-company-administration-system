<?php
  include('admin_session.php');
  include('print_errors.php');
  $line_add_errors = array();
  $line_del_errors = array();
  $line_up_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // ADD  
    if (isset($_POST['add_line'])) {   
      $lineStartId_add = pg_escape_string($db,$_POST['lineStartId_add']);
      $lineEndId_add = pg_escape_string($db,$_POST['lineEndId_add']);
      $lineEndDistance_add = pg_escape_string($db,$_POST['lineEndDistance_add']);
      $i = 0;
      $InterArray = "ARRAY[";
      $lineInterIds_add = array();
      $InterIdsOK = true;
      $InterDistOK = true;
      while(isset($_POST['lineInterId_add' . $i])){
        if($i!=0){$InterArray .= ", ";}
        $interId = pg_escape_string($db,$_POST['lineInterId_add' . $i]);
        $interDist = pg_escape_string($db,$_POST['lineInterDist_add' . $i]);
        if($interId == 'default'){
          $InterIdsOK = false;
          break;
        }
        if(!preg_match("/^[0-9]+$/",$interDist)){
          $InterDistOK = false;
          break;
        }
        array_push($lineInterIds_add, array($interId, $interDist));
        $InterArray .= "[$interId,$interDist]";
        $i=$i+1;
      }
      $InterArray .= "]";
      if($InterArray == "ARRAY[]"){$InterArray = "NULL";}
      if($lineStartId_add == 'default' or $lineEndId_add == 'default' or !$InterIdsOK){
        array_push($line_add_errors, "Wybierz przystanki z listy");
      }elseif(!preg_match("/^[0-9]+$/",$lineEndDistance_add) or !$InterDistOK){
        array_push($line_add_errors, "Dystans ma niepoprawną wartosć");
      }else{
        $sql = "INSERT INTO linie(start_pid,end_pid_dist,inter_pid_dist)
        VALUES ($lineStartId_add,ARRAY[$lineEndId_add,$lineEndDistance_add], $InterArray);";
        $result = pg_query($db,$sql);
        if(!$result) {
          array_push($line_add_errors, pg_last_error($db));
        } else {
           $line_add_notify = "Linia dodana pomyślnie";
        }
      }
      if(!empty($line_add_errors)){
        open_modal('id01');
      }elseif(isset($line_add_notify)){
        open_modal('confirm-message');
      }
    }
    // DELETE
    if (isset($_POST['delete_line'])) {
      $lineId_del = pg_escape_string($db,$_POST['lineId_del']);

      if($lineId_del != 'default'){
        $sql = "SELECT * FROM linie WHERE lid = $lineId_del";
        $result = pg_query($db,$sql);
        $count = pg_num_rows($result);
        if($count == 1) {
          $sql = "DELETE FROM linie WHERE lid = $lineId_del";
          $result = pg_query($db,$sql);
          if(!$result) {
             array_push($line_del_errors, pg_last_error($db));
          } else {
             $line_del_notify = "Linia usunięta pomyślnie";
          }
        }else{
          array_push($line_del_errors, "Linia o podanym ID nie istnieje");
        }
      }else{
        array_push($line_del_errors, "Wybierz Linię z listy");
      }
      if(!empty($line_del_errors)){
        open_modal('id02');
      }elseif(isset($line_del_notify)){
        open_modal('confirm-message');
      }
    }
    // UPDATE SELECT
    if (isset($_POST['lineId_up'])) {
      $lineId_up = pg_escape_string($db,$_POST['lineId_up']);
      $inter_pid_dist_selectedValue = array();
      if($lineId_up != 'default'){
      $sql = "SELECT * FROM linie WHERE lid = $lineId_up";
      $result = pg_query($db,$sql);
        if(!$result) {
               array_push($line_up_errors, pg_last_error($db));
        }else{
          $row = pg_fetch_all($result);
          if(pg_num_rows($result) == 1){
              $start_pid_selectedValue = intval($row[0]['start_pid']);
              $end_pid_selectedValue = convertToArray($row[0]['end_pid_dist'])[0];
              $end_dist_selectedValue = convertToArray($row[0]['end_pid_dist'])[1];
              $lid = intval($lineId_up);
              if(isset($row[0]['inter_pid_dist'])){
			          $inter_pid_dist_selectedValue = convertToArray($row[0]['inter_pid_dist']);
			        }
              $unhide = true;
          }
        } 
      }
      open_modal('id03',phpToJsArray($inter_pid_dist_selectedValue));
    }
    // UPDATE
    if (isset($_POST['up_line'])){
    	$lineId_up = pg_escape_string($db,$_POST['lineId_up']);
			$lineStartId_up = pg_escape_string($db,$_POST['lineStartId_up']);
      $lineEndId_up = pg_escape_string($db,$_POST['lineEndId_up']);
      $lineEndDistance_up = pg_escape_string($db,$_POST['lineEndDistance_up']);
      $i = 0;
      $InterArray = "ARRAY[";
      $lineInterIds_up = array();
      $InterIdsOK = true;
      $InterDistOK = true;
      while(isset($_POST['lineInterId_up' . $i])){
        if($i!=0){$InterArray .= ", ";}
        $interId = pg_escape_string($db,$_POST['lineInterId_up' . $i]);
        $interDist = pg_escape_string($db,$_POST['lineInterDist_up' . $i]);
        if($interId == 'default'){
          $InterIdsOK = false;
          break;
        }
        if(!preg_match("/^[0-9]+$/",$interDist)){
          $InterDistOK = false;
          break;
        }
        array_push($lineInterIds_up, array($interId, $interDist));
        $InterArray .= "[$interId,$interDist]";
        $i=$i+1;
      }
      $InterArray .= "]";
      if($InterArray == "ARRAY[]"){$InterArray = "NULL";}
      if($lineStartId_up == 'default' or $lineEndId_up == 'default' or !$InterIdsOK){
        array_push($line_up_errors, "Wybierz przystanki z listy");
      }elseif(!preg_match("/^[0-9]+$/",$lineEndDistance_up) or !$InterDistOK){
        array_push($line_up_errors, "Dystans ma niepoprawną wartosć");
      }else{
				$sql = "UPDATE linie SET (start_pid,end_pid_dist,inter_pid_dist) = ($lineStartId_up,ARRAY[$lineEndId_up,$lineEndDistance_up], $InterArray)
        WHERE lid = $lineId_up;";
        $result = pg_query($db,$sql);
        if(!$result) {
          array_push($line_up_errors, pg_last_error($db));
        } else {
          $line_up_notify = "Zmiany zostały dokonane pomyślnie";
          open_modal('confirm-message');
        }
      }
    }
  }

  pg_close($db);
?>
<?php include('printOptions.php');?>
<html lang="en" dir="ltr">
<head>
<title>Admin Page</title>
<meta charset="UTF-8">
<link rel="stylesheet" href="styles/layout2.css" type="text/css">
</head>
<body>
<div class="wrapper row1">
  <div id="header" class="clear">
    <div id="hgroup">
      <h1><a href="admin_home.php">Panel Administratora</a></h1>
      <h2>System zarządzania firmy przewozowej</h2>
    </div>
    <nav>
      <ul>
        <li><a href="admin_home.php">Strona Główna</a></li>
        <li><a href="admin_manage_accounts.php">Zarządzanie Kontami</a></li>
        <li><a href="admin_manage_busstop.php">Przystanki</a></li>
        <li><a href="admin_manage_buses.php">Autokary</a></li>
        <li><a href="admin_manage_drivers.php">Kierowcy</a></li>
        <li><a class="active" href="admin_manage_lines.php">Linie</a></li>
        <li><a href="admin_manage_courses.php">Kursy</a></li>
        <li class="last"><a href="logout.php">Wyloguj</a></li>
      </ul>
    </nav>
  </div>
</div>
<!-- content -->
<div class="wrapper row2">
  <div id="container" class="clear">
      <!-- services area -->
      <div class="clear section">
        <div class="two_quarter">
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Dodaj Linię</button>
        <button onclick="document.getElementById('id02').style.display='block'" style="width:auto;">Usuń Linię</button>
        <button onclick="document.getElementById('id03').style.display='block'" style="width:auto;">Modyfikuj Linię</button>
      </div>
      <div class="two_quarter lastbox">
        <input type="text" id="searchInput" onkeyup="searchList()" placeholder="Wyszukaj..." title="Type in something">
      </div>

        <div id="confirm-message" class="modal">
          <div class="modal-content animate">
            <div class="close-container">
              <span onclick="document.getElementById('confirm-message').style.display='none'" class="close" title="Close Modal">&times;</span>
            </div>
            <div class="modal-content-container">
              <div class = "notificationContainer">
                <?php 
                if(isset($line_add_notify)){ 
                  echo "<h2>$line_add_notify</h2>";
                }elseif(isset($line_del_notify)){
                  echo "<h2>$line_del_notify</h2>";
                }elseif(isset($line_up_notify)){
                  echo "<h2>$line_up_notify</h2>";
                }
                ?>  
              </div>
            </div>
            <div class="modal-content-container button-container">
              <button onclick="document.getElementById('confirm-message').style.display='none'" style="width:auto;">OK</button>
            </div>
          </div>
        </div>

        <div id="rowClick" class="modal">
          <div class="modal-content animate">
            <div class="close-container">
              <span onclick="document.getElementById('rowClick').style.display='none'" class="close" title="Close Modal">&times;</span>
            </div>
            <div class="modal-content-container">
              <h2 id="rowClickMessage"></h2>
            </div>
            <div class="modal-content-container button-container">
              <button onclick="updateFormAutofill()" style="width:auto;">Modyfikuj</button>
              <button class="delete-button" onclick="deleteFormAutofill()" style="width:auto;">Usuń</button>
            </div>
          </div>
        </div>

        <div id="id01" class="modal">
          <div class="modal-content animate">
            <div class="close-container">
              <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
            </div>
            <div class="modal-content-container">
            <div class="row">
                <div class="col-100">
                  <h2>Dodaj nową linię</h2>
                </div>
            </div>
              <form action = "" method = "post" id="add_form">
                <div class="row">
                  <div class="col-25">
                    <label>Przystanek Początkowy:</label>
                  </div>
                  <div class="col-75">
                    <select name="lineStartId_add">
                    <option value="default">Select</option>
                      <?php printOptionsBusstops(); ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Przystanek Końcowy:</label>
                  </div>
                  <div class="col-50">
                    <select name="lineEndId_add">
                    <option value="default">Select</option>
                      <?php printOptionsBusstops(); ?>
                    </select>
                  </div>
                  <div class="col-25">
                    <input type = "number" min="0" name = "lineEndDistance_add" class = "box" placeholder="Dystans [km]"/>
                  </div>
                </div>
                <div id="InterBlock">
                  <div class="row" id="AddInterRow" hidden>
                    <div class="col-25">
                      <label>Przystanek Pośredni:</label>
                    </div>
                    <div class="col-50">
                      <select name="lineInterId_add">
                      <option value="default">Select</option>
                        <?php printOptionsBusstops(); ?>
                      </select>
                    </div>
                    <div class="col-20">
                      <input type = "number" min="0" name = "lineInterDist_add" class = "box" placeholder="Dystans [km]"/>
                    </div>
                    <div class="col-05">
                      <span onclick="removeInterSelectField()" class="remove" title="Remove">&times;</span>
                    </div>
                  </div>
                </div>

                <div class="row">
                    <div class="col-100">
                      <button type="button" id="addInter" onclick="addInterSelectField()" style="width:auto;">Dodaj przystanek posredni</button>
                      <input type = "submit" value = " Submit " name="add_line" /><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($line_add_errors); ?></div>
            </div>
          </div>
        </div>

        <div id="id02" class="modal">
          <div class="modal-content animate">
            <div class="close-container">
              <span onclick="document.getElementById('id02').style.display='none'" class="close" title="Close Modal">&times;</span>
            </div>
            <div class="modal-content-container">
              <div class="row">
                <div class="col-100">
                  <h2>Usuń Linię</h2>
                </div>
              </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-100">
                    <select id="delete_line_select" name="lineId_del">
                    <option value="default">Select</option>
                      <?php printOptionsLines(); ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                    <div class="col-100">
                      <input id="delete_line_submitButton" type = "submit" value = " Submit " name="delete_line"/><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($line_del_errors); ?></div>
            </div>
          </div>
        </div>

        <div id="id03" class="modal">
          <div class="modal-content animate">
            <div class="close-container">
              <span onclick="document.getElementById('id03').style.display='none'" class="close" title="Close Modal">&times;</span>
            </div>
            <div class="modal-content-container">
	           	<div class="row">
	                <div class="col-100">
	                  <h2>Modyfikuj Linię</h2>
	                </div>
	            </div>
	              <form action = "" method = "post">
									<div class="row">
	                  <div class="col-25">
	                    <label>Wybierz linię:</label>
	                  </div>
	                  <div class="col-75">
	                    <select id="update_line_select" name="lineId_up" onchange="this.form.submit()">
	                    <option value="default">Select</option>
	                      <?php printOptionsLines($lid); ?>
	                    </select>
	                  </div>
                	</div>
	              <div <?php if(!isset($unhide)){ echo ' hidden '; } ?> >
	              	<div class="row">
	                <div class="col-25">
	                  <label>Przystanek Początkowy:</label>
	                </div>
	                <div class="col-75">
	                  <select name="lineStartId_up">
	                  <option value="default">Select</option>
	                    <?php printOptionsBusstops($start_pid_selectedValue); ?>
	                  </select>
	                </div>
	                </div>
	                <div class="row">
	                  <div class="col-25">
	                    <label>Przystanek Końcowy:</label>
	                  </div>
	                  <div class="col-50">
	                    <select name="lineEndId_up">
	                    <option value="default">Select</option>
	                      <?php printOptionsBusstops($end_pid_selectedValue); ?>
	                    </select>
	                  </div>
	                  <div class="col-25">
	                    <input type = "number" min="0" name = "lineEndDistance_up" class = "box" value=
	                      <?php
	                      if(isset($end_dist_selectedValue)){
	                        echo '"' . $end_dist_selectedValue . '"'; 
	                      }else{
	                        echo '""';
	                      }
	                      ?> 
	                      />
	                  </div>
	                </div>
									<div id="UpdateInterBlock">
	                  <div class="row" id="UpdateInterRow" hidden>
	                    <div class="col-25">
	                      <label>Przystanek Pośredni:</label>
	                    </div>
	                    <div class="col-50">
	                      <select name="lineInterId_up">
	                      <option value="default">Select</option>
	                        <?php printOptionsBusstops(); ?>
	                      </select>
	                    </div>
	                    <div class="col-20">
	                      <input type = "number" min="0" name = "lineInterDist_up" class = "box" placeholder="Dystans [km]"/>
	                    </div>
	                    <div class="col-05">
	                      <span onclick="removeInterSelectFieldUpdate()" class="remove" title="Remove">&times;</span>
	                    </div>
	                  </div>
	                </div>

	           		</div>

								<div class="row">
							    <div class="col-100">
							      <button type="button" id="addInterUpdate" onclick="addInterSelectFieldUpdate()" style="width:auto;">Dodaj przystanek posredni</button>
							      <input type = "submit" value = " Submit " name="up_line" /><br />
							    </div>
								</div>

	            </form>
	            <div class = "errorContainer"><?php print_errors($line_up_errors); ?></div>
            </div>
          </div>
        </div>

      </div>
      <div class="clear section last">

        <div class="four_quarter lastbox">
          <div class="row">
              <div class="col-100">
                <h2>Lista Linii</h2>
              </div>
          </div>
          <div class="row">
              <div class="col-100">
                <table id="list">
                  <tr>
                    <th>ID</th>
                    <th>Przystanek początkowy</th>
                    <th>Przystanek końcowy</th>
                    <th>Przystanki Pośrednie</th>
                  </tr>
                  <?php
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

                        $table_inter_string = '';
                        if(isset($row['inter_pid_dist'])){
                          $inter_pid_dist = convertToArray($row['inter_pid_dist']);
                          foreach ($inter_pid_dist as $pid_dist_pair) {       
                            $inter_pid = $pid_dist_pair[0];
                            $inter_dist = $pid_dist_pair[1];
                            $inter_name = pidToName($inter_pid,$db);
                            $table_inter_string .= $inter_name . " (" . $inter_dist ."km), ";
                          }
                        }else{
                          $table_inter_string = 'Brak';
                        }

                        echo ('<tr onclick="rowClick(this)"><td>' . $row['lid'] . 
                              '</td><td>' . $start_name . 
                              '</td><td>' . $end_name . " (" . $end_dist ."km)" . 
                              '</td><td>' . $table_inter_string . 
                              '</td></tr>');
                      }
                    }
                    pg_close($db);
                  ?>
                </table>
              </div>
          </div>
        </div>

      </div>
    <!-- / content body -->
  </div>
</div>
<!-- Footer -->
<div class="wrapper row3">
  <div id="footer" class="clear">
    <p class="fl_left">Created by Marek Zajac</p>
    <!--<p class="fl_right">Template by <a target="_blank" href="https://www.os-templates.com/" title="Free Website Templates">OS Templates</a></p>-->
  </div>
</div>
<script>
var lastRowClicked;
var interCount = -1;
var interCountUpdate = -1;
function searchList() { 
  var input, filter, table, tr, td, i, j, txtValue, textToFind;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("list");
  tr = table.getElementsByTagName("tr");

  for (i = 1; i < tr.length; i++) {
    textToFind = "";
    j=0;
    while(true){
      td = tr[i].getElementsByTagName("td")[j];
      if(td){
        txtValue = td.textContent || td.innerText;
        textToFind += (txtValue + " ");
        j+=1;
      }else{
        break;
      }
    }
    textToFind = textToFind.trim();
    if (textToFind.toUpperCase().indexOf(filter) > -1) {
      tr[i].style.display = "";
    } else {
      tr[i].style.display = "none";
    }
  }
}

function rowClick(o){
  lastRowClicked = o;
  var td, username;
  td = o.getElementsByTagName("td")[1];
  start = (td.textContent || td.innerText);
  td = o.getElementsByTagName("td")[2];
  end = (td.textContent || td.innerText);

  message = "Wybierz co zrobic z linią " + start + " - " + end;
  document.getElementById("rowClickMessage").innerHTML = message;
  document.getElementById('rowClick').style.display='block';
}
function deleteFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  ID = td.textContent || td.innerText;
  document.getElementById('delete_line_select').value = ID;
  //document.getElementById('delete_form').submit();
  document.getElementById('delete_line_submitButton').click();
  }
}
function updateFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  ID = td.textContent || td.innerText;
  document.getElementById('update_line_select').value = ID;
  document.getElementById('update_line_select').form.submit();
  //document.getElementById('delete_form').submit();
  }
}
function addInterSelectField(){
  interCount += 1;
  console.log("Added new row nr "+interCountUpdate+ " in add form");
  var select = document.getElementById("AddInterRow");
  var clone = select.cloneNode(true);
  clone.removeAttribute("hidden");
  clone.setAttribute("id", "AddInterRow" + interCount);
  clone.getElementsByTagName("select")[0].setAttribute("name", ("lineInterId_add" + interCount));
  clone.getElementsByTagName("input")[0].setAttribute("name", ("lineInterDist_add" + interCount));
  clone.getElementsByTagName("span")[0].setAttribute("onclick", ("removeInterSelectField("+interCount+")"));

  document.getElementById("InterBlock").appendChild(clone);
}
function removeInterSelectField(index){
  var i=0;
  var isRemoved = false;
  while(document.getElementById("AddInterRow" + i)){
  	if(isRemoved){
  	  //in folowing selects decrement value in IDs by one
  	  var row = document.getElementById("AddInterRow" + i);
  	  row.setAttribute("id", ("AddInterRow" + (i-1)));
  	  row.getElementsByTagName("select")[0].setAttribute("name", ("lineInterId_add" + (i-1)));
  	  row.getElementsByTagName("input")[0].setAttribute("name", ("lineInterDist_add" + (i-1)));
  	  row.getElementsByTagName("span")[0].setAttribute("onclick", ("removeInterSelectField("+(i-1)+")"));
  	}
  	if(index==i){
  	  //remove when index match
  	  var row = document.getElementById("AddInterRow" + i);
  	  row.parentNode.removeChild(row);
  	  isRemoved = true;
  	}

  	i+=1;
  }
  interCount -= 1;
}
function printInterSelectsUpdate(interData){
	console.log(interData);
	for (var i = 0; i < interData.length; i++){
	  interCountUpdate += 1;
	  console.log("Added new row nr "+interCountUpdate+ " in update form");
	  var div = document.getElementById("UpdateInterRow");
	  var clone = div.cloneNode(true);
	  clone.removeAttribute("hidden");
	  clone.setAttribute("id", "UpdateInterRow" + interCountUpdate);
	  var select = clone.getElementsByTagName("select")[0];
	  select.setAttribute("name", ("lineInterId_up" + interCountUpdate));
	  options = select.getElementsByTagName("option");
	  for (var j = 0; j < options.length; j++){
	  	if(options[j].value==interData[i][0]){
	  		options[j].setAttribute("selected", "selected");
	  	}
	  }
	  //select.getElementsByTagName("option").setAttribute("selected", "selected");
	  var input = clone.getElementsByTagName("input")[0];
	  input.setAttribute("name", ("lineInterDist_up" + interCountUpdate));
	  input.value = interData[i][1];
	  clone.getElementsByTagName("span")[0].setAttribute("onclick", ("removeInterSelectFieldUpdate("+interCountUpdate+")"));

	  document.getElementById("UpdateInterBlock").appendChild(clone);
	}
}
function addInterSelectFieldUpdate(){
  interCountUpdate += 1;
  console.log("Added new row nr "+interCountUpdate+ " in update form");
  var select = document.getElementById("UpdateInterRow");
  var clone = select.cloneNode(true);
  clone.removeAttribute("hidden");
  clone.setAttribute("id", "UpdateInterRow" + interCountUpdate);
  clone.getElementsByTagName("select")[0].setAttribute("name", ("lineInterId_up" + interCountUpdate));
  clone.getElementsByTagName("input")[0].setAttribute("name", ("lineInterDist_up" + interCountUpdate));
  clone.getElementsByTagName("span")[0].setAttribute("onclick", ("removeInterSelectFieldUpdate("+interCountUpdate+")"));

  document.getElementById("UpdateInterBlock").appendChild(clone);
}
function removeInterSelectFieldUpdate(index){
  var i=0;
  var isRemoved = false;
  while(document.getElementById("UpdateInterRow" + i)){
  	if(isRemoved){
  	  //in folowing selects decrement value in IDs by one
  	  var row = document.getElementById("UpdateInterRow" + i);
  	  row.setAttribute("id", ("UpdateInterRow" + (i-1)));
  	  row.getElementsByTagName("select")[0].setAttribute("name", ("lineInterId_up" + (i-1)));
  	  row.getElementsByTagName("input")[0].setAttribute("name", ("lineInterDist_up" + (i-1)));
  	  row.getElementsByTagName("span")[0].setAttribute("onclick", ("removeInterSelectFieldUpdate("+(i-1)+")"));
  	  //row.getElementsByClassName("remove")[0].setAttribute("id", ("removeButton" + (i-1)));
  	}
  	if(index==i){
  	  //remove when index match
  	  var row = document.getElementById("UpdateInterRow" + i);
  	  row.parentNode.removeChild(row);
  	  isRemoved = true;
  	}

  	i+=1;
  }
  interCountUpdate -= 1;
}

</script>

</body>
</html>
