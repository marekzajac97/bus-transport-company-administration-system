<?php
  include('admin_session.php');
  include('print_errors.php');
  $bus_add_errors = array();
  $bus_del_errors = array();
  $bus_up_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // ADD  
    if (isset($_POST['add_bus'])) {   
      $bus_producent = pg_escape_string($db,$_POST['bus_producent']);
      $bus_model = pg_escape_string($db,$_POST['bus_model']);
      $bus_nOfSeats = pg_escape_string($db,$_POST['bus_nOfSeats']);

      if (empty($bus_producent)) {
         array_push($bus_add_errors, "Producent jest wymagane");
      } elseif (empty($bus_model)) {
         array_push($bus_add_errors, "Model jest wymagane");
      } elseif (empty($bus_nOfSeats)) {
         array_push($bus_add_errors, "Liczba miejsc jest wymagana");
      } elseif (!preg_match("/^[0-9]+$/",$bus_nOfSeats)) {
         array_push($bus_add_errors, "Liczba miejsc jest niepoprawna");
      } else{
          $sql = "INSERT INTO autokary (producent,model,lmiejsc) VALUES('$bus_producent','$bus_model','$bus_nOfSeats')";
          $result = pg_query($db,$sql);
          if(!$result) {
            array_push($bus_add_errors, pg_last_error($db));
          } else {
             $bus_add_notify = "Autokar dodany pomyślnie";
          }
      }
      if(!empty($bus_add_errors)){
        open_modal('id01');
      }elseif(isset($bus_add_notify)){
        open_modal('confirm-message');
      }
    }
    // DELETE
    if (isset($_POST['delete_bus'])) {
      $del_bus_id = pg_escape_string($db,$_POST['bus_del_id']);

      if($del_bus_id != 'default'){
        $sql = "SELECT * FROM autokary WHERE autoid = $del_bus_id";
        $result = pg_query($db,$sql);
        $count = pg_num_rows($result);
        if($count == 1) {
          $sql = "DELETE FROM autokary WHERE autoid = $del_bus_id";
          $result = pg_query($db,$sql);
          if(!$result) {
             array_push($bus_del_errors, pg_last_error($db));
          } else {
             $bus_del_notify = "Autokar usunięty pomyślnie";
          }
        }else{
          array_push($bus_del_errors, "Autokar o podanym ID nie istnieje");
        }
      }else{
        array_push($bus_del_errors, "Wybierz Autokar z listy");
      }
      if(!empty($bus_del_errors)){
        open_modal('id02');
      }elseif(isset($bus_del_notify)){
        open_modal('confirm-message');
      }
    }
    // UPDATE SELECT
    if (isset($_POST['bus_up_id'])) {
      $up_bus_id = pg_escape_string($db,$_POST['bus_up_id']);
      if($up_bus_id != 'default'){
      $sql = "SELECT * FROM autokary WHERE autoid = $up_bus_id";
      $result = pg_query($db,$sql);
        if(!$result) {
               array_push($bus_up_errors, pg_last_error($db));
        }else{
          $row = pg_fetch_all($result);
          if(pg_num_rows($result) == 1){
              $update_producent_placeholder = $row[0]['producent'];
              $update_model_placeholder = $row[0]['model'];
              $update_lmiejsc_placeholder = $row[0]['lmiejsc'];
              $unhide = true;
          }
        } 
      }
      open_modal('id03');
    }
    // UPDATE
    if (isset($_POST['update_bus'])){
      $up_bus_id = pg_escape_string($db,$_POST['bus_up_id']);
      $bus_update_producent = pg_escape_string($db,$_POST['bus_update_producent']);
      $bus_update_model = pg_escape_string($db,$_POST['bus_update_model']);
      $bus_update_lmiejsc = pg_escape_string($db,$_POST['bus_update_lmiejsc']);

      if($up_bus_id != 'default'){
        if (empty($bus_update_producent) and empty($bus_update_model) and empty($bus_update_lmiejsc)) {
         array_push($bus_up_errors, "Brak zmian");
        }elseif(empty($bus_update_lmiejsc) or !preg_match("/^[0-9]+$/",$bus_update_lmiejsc)){
          array_push($bus_up_errors, "Liczba miejsc jest niepoprawna");
        }else{
          $producent = '';
          $model = '';
          $lmiejsc = '';
          if(!empty($bus_update_producent)){
            if(!empty($bus_update_model) or !empty($bus_update_lmiejsc)){
              $producent = " producent = '$bus_update_producent', ";
            }else{
              $producent = " producent = '$bus_update_producent' ";
            }
          }
          if(!empty($bus_update_model)){
            if(!empty($bus_update_lmiejsc)){
              $model = " model = '$bus_update_model', ";
            }else{
              $model = " model = '$bus_update_model' ";
            }
          }
          if(!empty($bus_update_lmiejsc)){
            $lmiejsc = " lmiejsc = '$bus_update_lmiejsc' ";
          }
        

          $sql = "UPDATE autokary SET $producent $model $lmiejsc WHERE autoid = $up_bus_id";
          $result = pg_query($db,$sql);
            if(!$result) {
              array_push($bus_up_errors, pg_last_error($db));
            }else{
              if(!empty($bus_update_producent)){
                $update_producent_placeholder = $bus_update_producent;
              }
              if(!empty($bus_update_model)){
                $update_model_placeholder = $bus_update_model;
              }
              if(!empty($bus_update_lmiejsc)){
                $update_lmiejsc_placeholder = $bus_update_lmiejsc;
              }
              $bus_up_notify = "Zmiany zostały dokonane pomyślnie";
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
        <li><a class="active" href="admin_manage_buses.php">Autokary</a></li>
        <li><a href="admin_manage_drivers.php">Kierowcy</a></li>
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
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Dodaj Autokar</button>
        <button onclick="document.getElementById('id02').style.display='block'" style="width:auto;">Usuń Autokar</button>
        <button onclick="document.getElementById('id03').style.display='block'" style="width:auto;">Modyfikuj Autokar</button>
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
                if(isset($bus_add_notify)){ 
                  echo "<h2>$bus_add_notify</h2>";
                }elseif(isset($bus_del_notify)){
                  echo "<h2>$bus_del_notify</h2>";
                }elseif(isset($bus_up_notify)){
                  echo "<h2>$bus_up_notify</h2>";
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
                  <h2>Dodaj Autokar</h2>
                </div>
            </div>
              <form action = "" method = "post" id="update_form">
                <div class="row">
                  <div class="col-25">
                    <label>Producent:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "bus_producent" class = "box"/><br /><br />
                  </div>
                </div>
                  <div class="row">
                  <div class="col-25">
                    <label>Model:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "bus_model" class = "box"/><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Liczba Miejsc:</label>
                  </div>
                  <div class="col-75">
                    <input type = "number" min="1" name = "bus_nOfSeats" class = "box"/><br /><br />
                  </div>
                </div>
                <div class="row">
                    <div class="col-100">
                      <input type = "submit" value = " Submit " name="add_bus" /><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($bus_add_errors); ?></div>
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
                  <h2>Usuń Autokar</h2>
                </div>
              </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-100">
                    <select id="delete_bus_busSelect" name="bus_del_id">
                    <option value="default">Select</option>
                      <?php
                        printOptionsAuto();
                      ?>
                    </select>
                    <br /><br />
                  </div>
                </div>

                <div class="row">
                    <div class="col-100">
                      <input id="delete_bus_submitButton" type = "submit" value = " Submit " name="delete_bus"/><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($bus_del_errors); ?></div>
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
                  <h2>Edytuj Informacje o Autokarze</h2>
                </div>
            </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-25">
                    <label>Wybierz Autokar:</label>
                  </div>
                  <div class="col-75">
                    <select id="update_bus_busSelect" name="bus_up_id" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php
                        printOptionsAuto($up_bus_id);
                      ?>
                    </select>
                    <br /><br />
                  </div>
                </div>
              <div <?php if(!isset($unhide)){ echo ' hidden '; } ?> >
                <div class="row">
                  <div class="col-25">
                    <label>Producent:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "bus_update_producent" class = "box" placeholder=
                      <?php
                      if(isset($update_producent_placeholder)){
                        echo '"' . $update_producent_placeholder . '"'; 
                      }else{
                        echo '""';
                      }
                      ?> 
                    /><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Model:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "bus_update_model" class = "box" placeholder=
                      <?php
                      if(isset($update_model_placeholder)){
                        echo '"' . $update_model_placeholder . '"'; 
                      }else{
                        echo '""';
                      }
                      ?> 
                    /><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Liczba miejsc:</label>
                  </div>
                  <div class="col-75">
                    <input type = "number" min="1" name = "bus_update_lmiejsc" class = "box" placeholder=
                      <?php
                      if(isset($update_lmiejsc_placeholder)){
                        echo '"' . $update_lmiejsc_placeholder . '"'; 
                      }else{
                        echo '""';
                      }
                      ?> 
                    /><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-100">
                    <input type = "submit" value = " Submit " name="update_bus"/><br />
                  </div>
                </div>
            </div>
            </form>
            <div class = "errorContainer"><?php print_errors($bus_up_errors); ?></div>
            </div>
          </div>
        </div>

      </div>
      <div class="clear section last">

        <div class="four_quarter lastbox">
          <div class="row">
              <div class="col-100">
                <h2>Lista Autokarów</h2>
              </div>
          </div>
          <div class="row">
              <div class="col-100">
                <table id="list">
                  <tr>
                    <th>ID</th>
                    <th>PRODUCENT</th>
                    <th>MODEL</th>
                    <th>LICZBA MIEJSC</th>
                  </tr>
                  <?php
                    include('config.php');
                    $sql = "SELECT * FROM autokary ORDER BY producent, model";
                    $result = pg_query($db,$sql);
                    $rows = pg_fetch_all($result);
                    if(pg_num_rows($result) > 0){
                      foreach ($rows as $row) {
                          echo ('<tr onclick="rowClick(this)"><td>' . $row['autoid'] . 
                                '</td><td>' . $row['producent'] . 
                                '</td><td>' . $row['model'] . 
                                '</td><td>' . $row['lmiejsc'] . 
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

  message = "Wybierz co zrobic z Autokarem " + name + " " + surname;
  document.getElementById("rowClickMessage").innerHTML = message;
  document.getElementById('rowClick').style.display='block';
}
function deleteFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  busID = td.textContent || td.innerText;
  document.getElementById('delete_bus_busSelect').value = busID;
  //document.getElementById('delete_form').submit();
  document.getElementById('delete_bus_submitButton').click();
  }
}
function updateFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  busID = td.textContent || td.innerText;
  document.getElementById('update_bus_busSelect').value = busID;
  document.getElementById('update_bus_busSelect').form.submit();
  //document.getElementById('delete_form').submit();
  }
}
</script>

</body>
</html>
