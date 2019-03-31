<?php
  include('admin_session.php');
  include('print_errors.php');
  $course_add_errors = array();
  $course_del_errors = array();
  $course_up_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // ADD  
    if (isset($_POST['add_course'])) {   
      $course_dateAndTime = pg_escape_string($db,$_POST['course_dateAndTime']);
      $course_kierId = pg_escape_string($db,$_POST['course_kierId']);
      $course_autoId = pg_escape_string($db,$_POST['course_autoId']);
      $course_lid = pg_escape_string($db,$_POST['course_lid']);

      if (empty($course_dateAndTime)) {
         array_push($course_add_errors, "Czas odjazdu jest wymagany");
      } elseif ($course_kierId == 'default') {
         array_push($course_add_errors, "Kierowca jest wymagany");
      } elseif ($course_autoId == 'default') {
         array_push($course_add_errors, "Autokar jest wymagany");
      } elseif ($course_lid == 'default') {
         array_push($course_add_errors, "Linia jest wymagana");
      } else{
          $course_dateAndTime = str_replace("T"," ", $course_dateAndTime);
          $sql = "INSERT INTO kursy (data_odjazdu,kierid,autoid,lid) VALUES('$course_dateAndTime',$course_kierId,$course_autoId,$course_lid)";
          $result = pg_query($db,$sql);
          if(!$result) {
            array_push($course_add_errors, pg_last_error($db));
          } else {
             $course_add_notify = "Kurs dodany pomyślnie";
          }
      }
      if(!empty($course_add_errors)){
        open_modal('id01');
      }elseif(isset($course_add_notify)){
        open_modal('confirm-message');
      }
    }
    // DELETE
    if (isset($_POST['delete_course'])) {
      $del_course_id = pg_escape_string($db,$_POST['course_del_id']);

      if($del_course_id != 'default'){
        $sql = "SELECT * FROM kursy WHERE kursid = $del_course_id";
        $result = pg_query($db,$sql);
        $count = pg_num_rows($result);
        if($count == 1) {
          $sql = "DELETE FROM kursy WHERE kursid = $del_course_id";
          $result = pg_query($db,$sql);
          if(!$result) {
             array_push($course_del_errors, pg_last_error($db));
          } else {
             $course_del_notify = "Kurs usunięty pomyślnie";
          }
        }else{
          array_push($course_del_errors, "Kurs o podanym ID nie istnieje");
        }
      }else{
        array_push($course_del_errors, "Wybierz kurs z listy");
      }
      if(!empty($course_del_errors)){
        open_modal('id02');
      }elseif(isset($course_del_notify)){
        open_modal('confirm-message');
      }
    }
    // UPDATE SELECT
    if (isset($_POST['course_up_id'])) {
      $up_course_id = pg_escape_string($db,$_POST['course_up_id']);
      if($up_course_id != 'default'){
      $sql = "SELECT * FROM kursy WHERE kursid = $up_course_id";
      $result = pg_query($db,$sql);
        if(!$result) {
               array_push($course_up_errors, pg_last_error($db));
        }else{
          $row = pg_fetch_all($result);
          if(pg_num_rows($result) == 1){
              $update_dateAndTime_inputValue = str_replace(" ","T", $row[0]['data_odjazdu']);
              $update_kierId_selectValue = $row[0]['kierid'];
              $update_autoId_selectValue = $row[0]['autoid'];
              $update_lid_selectValue = $row[0]['lid'];
              $unhide = true;
          }
        } 
      }
      open_modal('id03');
    }
    // UPDATE
    if (isset($_POST['update_course'])){
      $up_course_id = pg_escape_string($db,$_POST['course_up_id']);
      $course_update_dateAndTime = pg_escape_string($db,$_POST['course_update_dateAndTime']);
      $course_update_kierId = pg_escape_string($db,$_POST['course_update_kierId']);
      $course_update_autoId = pg_escape_string($db,$_POST['course_update_autoId']);
      $course_update_lid = pg_escape_string($db,$_POST['course_update_lid']);

      if($up_course_id != 'default'){
        if (empty($course_update_dateAndTime) and empty($course_update_kierId) and empty($course_update_autoId)) {
         array_push($course_up_errors, "Brak zmian");
        }elseif(empty($course_update_dateAndTime)){
          array_push($course_up_errors, "Czas odjazdu jest pusty");
        }elseif($course_update_kierId == 'default'){
          array_push($course_up_errors, "Wybierz Kierowcę z listy");
        }elseif($course_update_autoId == 'default'){
          array_push($course_up_errors, "Wybierz Autokar z listy");
        }elseif($course_update_lid == 'default'){
          array_push($course_up_errors, "Wybierz Linię z listy");
        }else{
          $course_update_dateAndTime = str_replace("T"," ", $course_update_dateAndTime);
          $sql = "UPDATE kursy SET (data_odjazdu,kierid,autoid,lid) = ('$course_update_dateAndTime',$course_update_kierId,$course_update_autoId,$course_update_lid)
          WHERE kursid = $up_course_id";
          $result = pg_query($db,$sql);
            if(!$result) {
              array_push($course_up_errors, pg_last_error($db));
            }else{
              $course_up_notify = "Zmiany zostały dokonane pomyślnie";
              open_modal('confirm-message');
            }
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
        <li><a href="admin_manage_lines.php">Linie</a></li>
        <li><a class="active" href="admin_manage_courses.php">Kursy</a></li>
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
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Dodaj Kurs</button>
        <button onclick="document.getElementById('id02').style.display='block'" style="width:auto;">Usuń Kurs</button>
        <button onclick="document.getElementById('id03').style.display='block'" style="width:auto;">Modyfikuj Kurs</button>
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
                if(isset($course_add_notify)){ 
                  echo "<h2>$course_add_notify</h2>";
                }elseif(isset($course_del_notify)){
                  echo "<h2>$course_del_notify</h2>";
                }elseif(isset($course_up_notify)){
                  echo "<h2>$course_up_notify</h2>";
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
                  <h2>Dodaj Kurs</h2>
                </div>
            </div>
              <form action = "" method = "post" id="update_form">
                <div class="row">
                  <div class="col-25">
                    <label>Czas Odjazdu:</label>
                  </div>
                  <div class="col-75">
                    <input type = "datetime-local" name = "course_dateAndTime" class = "box"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Linia:</label>
                  </div>
                  <div class="col-75">
                    <select name="course_lid">
                    <option value="default">Select</option>
                      <?php printOptionsLines() ?>
                    </select>
                </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Kierowca:</label>
                  </div>
                  <div class="col-75">
                    <select name="course_kierId">
                    <option value="default">Select</option>
                      <?php printOptionsKier() ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Autobus:</label>
                  </div>
                  <div class="col-75">
                    <select name="course_autoId">
                    <option value="default">Select</option>
                      <?php printOptionsAuto() ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                    <div class="col-100">
                      <input type = "submit" value = " Submit " name="add_course" /><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($course_add_errors); ?></div>
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
                  <h2>Usuń Kurs</h2>
                </div>
              </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-100">
                    <select id="delete_course_courseSelect" name="course_del_id">
                    <option value="default">Select</option>
                      <?php printOptionsKursy() ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                    <div class="col-100">
                      <input id="delete_course_submitButton" type = "submit" value = " Submit " name="delete_course"/><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($course_del_errors); ?></div>
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
                  <h2>Edytuj Kurs</h2>
                </div>
            </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-25">
                    <label>Wybierz kurs:</label>
                  </div>
                  <div class="col-75">
                    <select id="update_course_courseSelect" name="course_up_id" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php printOptionsKursy($up_course_id) ?>
                    </select>
                    <br /><br />
                  </div>
                </div>
              <div <?php if(!isset($unhide)){ echo ' hidden '; } ?> >
                <div class="row">
                  <div class="col-25">
                    <label>Numer Telefonu:</label>
                  </div>
                  <div class="col-75">
                    <input type = "datetime-local" name = "course_update_dateAndTime" class = "box" value=
                      <?php
                      if(isset($update_dateAndTime_inputValue)){
                        echo '"' . $update_dateAndTime_inputValue . '"'; 
                      }else{
                        echo '""';
                      }
                      ?> 
                    />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Linia:</label>
                  </div>
                  <div class="col-75">
                    <select name="course_update_lid">
                    <option value="default">Select</option>
                      <?php printOptionsLines($update_lid_selectValue) ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Kierowca:</label>
                  </div>
                  <div class="col-75">
                    <select name="course_update_kierId">
                    <option value="default">Select</option>
                      <?php printOptionsKier($update_kierId_selectValue) ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Autobus:</label>
                  </div>
                  <div class="col-75">
                    <select name="course_update_autoId">
                    <option value="default">Select</option>
                      <?php printOptionsAuto($update_autoId_selectValue) ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                  <div class="col-100">
                    <input type = "submit" value = " Submit " name="update_course"/><br />
                  </div>
                </div>
            </div>
            </form>
            <div class = "errorContainer"><?php print_errors($course_up_errors); ?></div>
            </div>
          </div>
        </div>

      </div>
      <div class="clear section">
        <div class="two_quarter">
            <h2>Lista Kierowców</h2>
        </div>
        <div class="two_quarter lastbox">
          <form class="fl_right">
            <label><input type="checkbox" onchange="checkboxClick(this)"> Pokzuj tylko aktualne kursy</label>
          </form>
        </div>
      </div>
      <div class="clear section last">

        <div class="four_quarter lastbox">
          <div class="row">
              <div class="col-100">
                <table id="list">
                  <tr>
                    <th>ID</th>
                    <th>DATA ODJAZDU</th>
                    <th>GODZINA ODJAZDU</th>
                    <th>LINIA</th>
                    <th>AUTOKAR</th>
                    <th>KIEROWCA</th>
                  </tr>
                  <?php
                    include('config.php');
                    $sql = "SELECT * FROM kursy ORDER BY data_odjazdu";
                    $result = pg_query($db,$sql);
                    $rows = pg_fetch_all($result);
                    if(pg_num_rows($result) > 0){
                      foreach ($rows as $row) {
                          $kier_name = kieridToName($row['kierid'],$db);
                          $auto_name = autoidToName($row['autoid'],$db);
                          $linia_name = lidToName($row['lid'],$db);
                          echo ('<tr onclick="rowClick(this)" data-date-filtered="false" data-search-filtered="false"><td>' . $row['kursid'] . 
                                '</td><td>' . (explode(" ", $row['data_odjazdu'])[0]) . 
                                '</td><td>' . (explode(" ", $row['data_odjazdu'])[1]) . 
                                '</td><td>' . $linia_name . 
                                '</td><td>' . $auto_name . 
                                '</td><td>' . $kier_name . 
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
function searchList() { 
  var input, filter, table, tr, td, i, j, txtValue, textToFind, isDateFiltered;
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
    isDateFiltered = tr[i].getAttribute("data-date-filtered");
    if (textToFind.toUpperCase().indexOf(filter) > -1 ) {
      if(isDateFiltered === "false"){
        tr[i].style.display = "";
      }
    } else {
      tr[i].style.display = "none";
      tr[i].setAttribute("data-search-filtered", "true");
    }
  }
}
var lastRowClicked
function rowClick(o){
  lastRowClicked = o;
  var td, username;
  td = o.getElementsByTagName("td")[1];
  data = (td.textContent || td.innerText);
  td = o.getElementsByTagName("td")[2];
  godzina = (td.textContent || td.innerText);

  message = "Wybierz co zrobic z kursem " + data + " " + godzina;
  document.getElementById("rowClickMessage").innerHTML = message;
  document.getElementById('rowClick').style.display='block';
}
function deleteFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  courseID = td.textContent || td.innerText;
  document.getElementById('delete_course_courseSelect').value = courseID;
  //document.getElementById('delete_form').submit();
  document.getElementById('delete_course_submitButton').click();
  }
}
function updateFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  courseID = td.textContent || td.innerText;
  document.getElementById('update_course_courseSelect').value = courseID;
  document.getElementById('update_course_courseSelect').form.submit();
  //document.getElementById('delete_form').submit();
  }
}
function checkboxClick(o){
  var input, filter, table, tr, td, i, timeRowTxt, dateRowTxt, dateTimeRowTxt, dateTimeRow;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("list");
  tr = table.getElementsByTagName("tr");
  if(o.checked == true){
    var today = new Date();

    for (i = 1; i < tr.length; i++) {
      td = tr[i].getElementsByTagName("td")[1];
      dateRowTxt = td.textContent || td.innerText;
      td = tr[i].getElementsByTagName("td")[2];
      timeRowTxt = td.textContent || td.innerText;
      dateTimeRowTxt = ( dateRowTxt + " " + timeRowTxt);
      dateTimeRow = new Date(dateTimeRowTxt);

      isSearchFiltered = tr[i].getAttribute("data-search-filtered");

      if (dateTimeRow >= today) {
        if(isSearchFiltered === "false"){
          tr[i].style.display = "";
        }
      } else {
        tr[i].style.display = "none";
        tr[i].setAttribute("data-date-filtered", "true");
      }
    }
  }else{
      for (i = 1; i < tr.length; i++) {
      isDateFiltered = tr[i].getAttribute("data-date-filtered");
      if(isDateFiltered === "true"){
        tr[i].style.display = "";
      }
    }
  }
}
</script>

</body>
</html>
