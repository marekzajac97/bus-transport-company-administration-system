<?php
  include('admin_session.php');
  include('print_errors.php');
  $driver_add_errors = array();
  $driver_del_errors = array();
  $driver_up_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // ADD  
    if (isset($_POST['add_driver'])) {   
      $driver_name = pg_escape_string($db,$_POST['driver_name']);
      $driver_surname = pg_escape_string($db,$_POST['driver_surname']);
      $driver_phone = pg_escape_string($db,$_POST['driver_phone']);

      if (empty($driver_name)) {
         array_push($driver_add_errors, "Imię jest wymagane");
      } elseif (empty($driver_surname)) {
         array_push($driver_add_errors, "Nazwisko jeest wymagane");
      } elseif (empty($driver_phone)) {
         array_push($driver_add_errors, "Numer telefonu jest wymagany");
      } elseif (!preg_match("/^[0-9]{9}$/",$driver_phone)) {
         array_push($driver_add_errors, "Numer telefonu niepoprawny");
      } else{
          $sql = "INSERT INTO kierowcy (imie,nazwisko,telefon) VALUES('$driver_name','$driver_surname','$driver_phone')";
          $result = pg_query($db,$sql);
          if(!$result) {
            array_push($driver_add_errors, pg_last_error($db));
          } else {
             $driver_add_notify = "Kierowca dodany pomyślnie";
          }
      }
      if(!empty($driver_add_errors)){
        open_modal('id01');
      }elseif(isset($driver_add_notify)){
        open_modal('confirm-message');
      }
    }
    // DELETE
    if (isset($_POST['delete_driver'])) {
      $del_driver_id = pg_escape_string($db,$_POST['driver_del_id']);

      if($del_driver_id != 'default'){
        $sql = "SELECT * FROM kierowcy WHERE kierid = $del_driver_id";
        $result = pg_query($db,$sql);
        $count = pg_num_rows($result);
        if($count == 1) {
          $sql = "DELETE FROM kierowcy WHERE kierid = $del_driver_id";
          $result = pg_query($db,$sql);
          if(!$result) {
             array_push($driver_del_errors, pg_last_error($db));
          } else {
             $driver_del_notify = "Kierowca usunięty pomyślnie";
          }
        }else{
          array_push($driver_del_errors, "Kierowca o podanym ID nie istnieje");
        }
      }else{
        array_push($driver_del_errors, "Wybierz kierowcę z listy");
      }
      if(!empty($driver_del_errors)){
        open_modal('id02');
      }elseif(isset($driver_del_notify)){
        open_modal('confirm-message');
      }
    }
    // UPDATE SELECT
    if (isset($_POST['driver_up_id'])) {
      $up_driver_id = pg_escape_string($db,$_POST['driver_up_id']);
      if($up_driver_id != 'default'){
      $sql = "SELECT * FROM kierowcy WHERE kierid = $up_driver_id";
      $result = pg_query($db,$sql);
        if(!$result) {
               array_push($driver_up_errors, pg_last_error($db));
        }else{
          $row = pg_fetch_all($result);
          if(pg_num_rows($result) == 1){
              $update_name_placeholder = $row[0]['imie'];
              $update_surname_placeholder = $row[0]['nazwisko'];
              $update_phone_placeholder = $row[0]['telefon'];
              $unhide = true;
          }
        } 
      }
      open_modal('id03');
    }
    // UPDATE
    if (isset($_POST['update_driver'])){
      $up_driver_id = pg_escape_string($db,$_POST['driver_up_id']);
      $driver_update_name = pg_escape_string($db,$_POST['driver_update_name']);
      $driver_update_surname = pg_escape_string($db,$_POST['driver_update_surname']);
      $driver_update_phone = pg_escape_string($db,$_POST['driver_update_phone']);

      if($up_driver_id != 'default'){
        if (empty($driver_update_name) and empty($driver_update_surname) and empty($driver_update_phone)) {
         array_push($driver_up_errors, "Brak zmian");
        }elseif(!empty($driver_update_phone) and !preg_match("/^[0-9]{9}$/",$driver_update_phone)){
          array_push($driver_up_errors, "Numer telefonu jest niepoprawny");
        }else{
          $imie = '';
          $nazwisko = '';
          $telefon = '';
          if(!empty($driver_update_name)){
            if(!empty($driver_update_surname) or !empty($driver_update_phone)){
              $imie = " imie = '$driver_update_name', ";
            }else{
              $imie = " imie = '$driver_update_name' ";
            }
          }
          if(!empty($driver_update_surname)){
            if(!empty($driver_update_phone)){
              $nazwisko = " nazwisko = '$driver_update_surname', ";
            }else{
              $nazwisko = " nazwisko = '$driver_update_surname' ";
            }
          }
          if(!empty($driver_update_phone)){
            $telefon = " telefon = '$driver_update_phone' ";
          }
        

          $sql = "UPDATE kierowcy SET $imie $nazwisko $telefon WHERE kierid = $up_driver_id";
          $result = pg_query($db,$sql);
            if(!$result) {
              array_push($driver_up_errors, pg_last_error($db));
            }else{
              if(!empty($driver_update_name)){
                $update_name_placeholder = $driver_update_name;
              }
              if(!empty($driver_update_surname)){
                $update_surname_placeholder = $driver_update_surname;
              }
              if(!empty($driver_update_phone)){
                $update_phone_placeholder = $driver_update_phone;
              }
              $driver_up_notify = "Zmiany zostały dokonane pomyślnie";
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
        <li><a class="active" href="admin_manage_drivers.php">Kierowcy</a></li>
        <li><a href="admin_manage_lines.php">Linie</a></li>
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
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Dodaj Kierowcę</button>
        <button onclick="document.getElementById('id02').style.display='block'" style="width:auto;">Usuń Kierowcę</button>
        <button onclick="document.getElementById('id03').style.display='block'" style="width:auto;">Modyfikuj Dane Kierowcy</button>
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
                if(isset($driver_add_notify)){ 
                  echo "<h2>$driver_add_notify</h2>";
                }elseif(isset($driver_del_notify)){
                  echo "<h2>$driver_del_notify</h2>";
                }elseif(isset($driver_up_notify)){
                  echo "<h2>$driver_up_notify</h2>";
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
                  <h2>Dodaj kierowcę</h2>
                </div>
            </div>
              <form action = "" method = "post" id="update_form">
                <div class="row">
                  <div class="col-25">
                    <label>Imię:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "driver_name" class = "box"/><br /><br />
                  </div>
                </div>
                  <div class="row">
                  <div class="col-25">
                    <label>Nazwisko:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "driver_surname" class = "box"/><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Numer Telefonu:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "driver_phone" class = "box"/><br /><br />
                  </div>
                </div>
                <div class="row">
                    <div class="col-100">
                      <input type = "submit" value = " Submit " name="add_driver" /><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($driver_add_errors); ?></div>
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
                  <h2>Usuń Kierowcę</h2>
                </div>
              </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-100">
                    <select id="delete_driver_driverSelect" name="driver_del_id">
                    <option value="default">Select</option>
                      <?php
                        printOptionsKier();
                      ?>
                    </select>
                    <br /><br />
                  </div>
                </div>

                <div class="row">
                    <div class="col-100">
                      <input id="delete_driver_submitButton" type = "submit" value = " Submit " name="delete_driver"/><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($driver_del_errors); ?></div>
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
                  <h2>Edytuj Dane Kierowcy</h2>
                </div>
            </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-25">
                    <label>Wybierz kierowcę:</label>
                  </div>
                  <div class="col-75">
                    <select id="update_driver_driverSelect" name="driver_up_id" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php
                        printOptionsKier($up_driver_id);
                      ?>
                    </select>
                    <br /><br />
                  </div>
                </div>
              <div <?php if(!isset($unhide)){ echo ' hidden '; } ?> >
                  <div class="row">
                  <div class="col-25">
                    <label>Imię:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "driver_update_name" class = "box" placeholder=
                      <?php
                      if(isset($update_name_placeholder)){
                        echo '"' . $update_name_placeholder . '"'; 
                      }else{
                        echo '""';
                      }
                      ?> 
                    /><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Nazwisko:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "driver_update_surname" class = "box" placeholder=
                      <?php
                      if(isset($update_surname_placeholder)){
                        echo '"' . $update_surname_placeholder . '"'; 
                      }else{
                        echo '""';
                      }
                      ?> 
                    /><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Numer Telefonu:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "driver_update_phone" class = "box" placeholder=
                      <?php
                      if(isset($update_phone_placeholder)){
                        echo '"' . $update_phone_placeholder . '"'; 
                      }else{
                        echo '""';
                      }
                      ?> 
                    /><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-100">
                    <input type = "submit" value = " Submit " name="update_driver"/><br />
                  </div>
                </div>
            </div>
            </form>
            <div class = "errorContainer"><?php print_errors($driver_up_errors); ?></div>
            </div>
          </div>
        </div>

      </div>
      <div class="clear section last">

        <div class="four_quarter lastbox">
          <div class="row">
              <div class="col-100">
                <h2>Lista Kierowców</h2>
              </div>
          </div>
          <div class="row">
              <div class="col-100">
                <table id="list">
                  <tr>
                    <th>ID</th>
                    <th>IMIĘ</th>
                    <th>NAZWISKO</th>
                    <th>NUMER TELEFONU</th>
                  </tr>
                  <?php
                    include('config.php');
                    $sql = "SELECT * FROM kierowcy ORDER BY nazwisko, imie";
                    $result = pg_query($db,$sql);
                    $rows = pg_fetch_all($result);
                    if(pg_num_rows($result) > 0){
                      foreach ($rows as $row) {
                          echo ('<tr onclick="rowClick(this)"><td>' . $row['kierid'] . 
                                '</td><td>' . $row['imie'] . 
                                '</td><td>' . $row['nazwisko'] . 
                                '</td><td>' . $row['telefon'] . 
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
var lastRowClicked
function rowClick(o){
  lastRowClicked = o;
  var td, username;
  td = o.getElementsByTagName("td")[1];
  name = (td.textContent || td.innerText);
  td = o.getElementsByTagName("td")[2];
  surname = (td.textContent || td.innerText);

  message = "Wybierz co zrobic z kierowcą " + name + " " + surname;
  document.getElementById("rowClickMessage").innerHTML = message;
  document.getElementById('rowClick').style.display='block';
}
function deleteFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  driverID = td.textContent || td.innerText;
  document.getElementById('delete_driver_driverSelect').value = driverID;
  //document.getElementById('delete_form').submit();
  document.getElementById('delete_driver_submitButton').click();
  }
}
function updateFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  driverID = td.textContent || td.innerText;
  document.getElementById('update_driver_driverSelect').value = driverID;
  document.getElementById('update_driver_driverSelect').form.submit();
  //document.getElementById('delete_form').submit();
  }
}
</script>

</body>
</html>
