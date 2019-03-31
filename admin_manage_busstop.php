<?php
  include('admin_session.php');
  include('print_errors.php');
  $busstop_add_errors = array();
  $busstop_del_errors = array();
  $busstop_up_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // ADD  
    if (isset($_POST['add_busstop'])) {  
      $busstopname = pg_escape_string($db,$_POST['busstop_name']);

      if (empty($busstopname)) {
         array_push($busstop_add_errors, "Pole nazwa jest wymagane");
      } else{
         $sql = "SELECT pid FROM przystanki WHERE nazwa = '$busstopname'";
         $result = pg_query($db,$sql);
         $count = pg_num_rows($result);
         
         if($count == 1) {
            array_push($busstop_add_errors, "Przystanek o podanej nazwie już isnieje");
         }else {
            $sql = "INSERT INTO przystanki (nazwa) VALUES('$busstopname')";
            $result = pg_query($db,$sql);
            if(!$result) {
              array_push($busstop_add_errors, pg_last_error($db));
            } else {
               $busstop_add_notify = "Przystanek dodany pomyślnie";
            }
         }
      }
      if(!empty($busstop_add_errors)){
        open_modal('id01');
      }elseif(isset($busstop_add_notify)){
        open_modal('confirm-message');
      }
    }
    // DELETE
    if (isset($_POST['delete_busstop'])) {
      $busstop_del_id = pg_escape_string($db,$_POST['busstop_del_id']);

      if($busstop_del_id != 'default'){
        $sql = "SELECT * FROM przystanki WHERE pid = $busstop_del_id";
        $result = pg_query($db,$sql);
        $count = pg_num_rows($result);
        if($count == 1) {
          $sql = "DELETE FROM przystanki where pid = $busstop_del_id";
          $result = pg_query($db,$sql);
          if(!$result) {
             array_push($busstop_del_errors, pg_last_error($db));
          } else {
             $busstop_del_notify = "Przystanek usunięty pomyślnie";
          }
        }else {
          array_push($busstop_del_errors, "Przystanek o podanym ID nie istnieje");
        }
      }else{
        array_push($busstop_del_errors, "Wybierz przystanek z listy");
      }
      if(!empty($busstop_del_errors)){
        open_modal('id02');
      }elseif(isset($busstop_del_notify)){
        open_modal('confirm-message');
      }
    }
    // UPDATE SELECT
    if (isset($_POST['busstop_up_id'])) {
      $busstop_up_id = pg_escape_string($db,$_POST['busstop_up_id']);
      if($busstop_up_id != 'default'){
      $sql = "SELECT * FROM przystanki WHERE pid = $busstop_up_id";
      $result = pg_query($db,$sql);
        if(!$result) {
               array_push($busstop_up_errors, pg_last_error($db));
        }else{
          $row = pg_fetch_all($result);
          if(pg_num_rows($result) == 1){
              $update_name_placeholder = $row[0]['nazwa'];
              $unhide = true;
          }
        } 
      }
      open_modal('id03');
    }
    // UPDATE
    if (isset($_POST['update_busstop'])){
      $busstop_up_name = pg_escape_string($db,$_POST['busstop_up_name']);

      if($busstop_up_id != 'default'){
        if (empty($busstop_up_name)) {
         array_push($busstop_up_errors, "No changes made");
        }else{
          $nazwa = " nazwa = '$busstop_up_name' ";
        
          $sql = "UPDATE przystanki SET $nazwa WHERE pid = $busstop_up_id";
          $result = pg_query($db,$sql);
          if(!$result) {
            array_push($busstop_up_errors, pg_last_error($db));
          }else{
            if(!empty($busstop_up_name)){
              $update_name_placeholder = $busstop_up_name;
            }
            $busstop_up_notify = "Zmiany zostały dokonane pomyślnie";
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
        <li><a class="active" href="admin_manage_busstop.php">Przystanki</a></li>
        <li><a href="admin_manage_buses.php">Autokary</a></li>
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
    <!-- main content -->
    <div id="homepage">
      <!-- services area -->
      <div class="clear section">

        <div class="two_quarter">
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Dodaj Przystanek</button>
        <button onclick="document.getElementById('id02').style.display='block'" style="width:auto;">Usuń Przystanek</button>
        <button onclick="document.getElementById('id03').style.display='block'" style="width:auto;">Modyfikuj Przystanek</button>
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
                if(isset($busstop_add_notify)){ 
                  echo "<h2>$busstop_add_notify</h2>";
                }elseif(isset($busstop_del_notify)){
                  echo "<h2>$busstop_del_notify</h2>";
                }elseif(isset($busstop_up_notify)){
                  echo "<h2>$busstop_up_notify</h2>";
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
                    <h2>Dodaj Przystanek</h2>
                  </div>
              </div>
                <form action = "" method = "post">
                  <div class="row">
                    <div class="col-25">
                      <label>Nazwa:</label>
                    </div>
                    <div class="col-75">
                      <input type = "text" name = "busstop_name" class = "box"/>
                    </div>
                  </div>
                  <div class="row">
                      <div class="col-100">
                        <input type = "submit" value = " Submit " name="add_busstop" />
                      </div>
                  </div>
                </form>
              <div class = "errorContainer"><?php print_errors($busstop_add_errors); ?></div>
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
                    <h2>Usuń przystanek</h2>
                  </div>
              </div>
                <form action = "" method = "post">
                  <div class="row">
                    <div class="col-25">
                      <label>Wybierz przystanek:</label>
                    </div>
                    <div class="col-75">
                      <select id="delete_busstop_select" name="busstop_del_id">
                      <option value="default">Select</option>
                        <?php
                          printOptionsBusstops();
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                      <div class="col-100">
                        <input id="delete_busstop_submitButton" type = "submit" value = " Submit " name="delete_busstop"/><br />
                      </div>
                  </div>
                </form>
                <div class = "errorContainer"><?php print_errors($busstop_del_errors); ?></div>
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
                    <h2>Modyfikuj przystanek</h2>
                  </div>
              </div>
                <form action = "" method = "post">
                  <div class="row">
                    <div class="col-25">
                      <label>Wybierz przystanek:</label>
                    </div>
                    <div class="col-75">
                    <select id="update_busstop_select" name="busstop_up_id" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php
                        printOptionsBusstops($busstop_up_id);
                      ?>
                    </select>
                    </div>
                  </div>
                  <div <?php if(!isset($unhide)){ echo ' hidden '; } ?> >
                    <div class="row" >
                      <div class="col-25">
                        <label>Nazwa:</label>
                      </div>
                      <div class="col-75">
                        <input type = "text" name = "busstop_up_name" class = "box" placeholder=
                        <?php
                        if(isset($update_name_placeholder)){
                          echo '"' . $update_name_placeholder . '"'; 
                        }else{
                          echo '""';
                        }
                        ?>
                        />
                      </div>
                    </div>
                  </div>
                  <div class="row">
                      <div class="col-100">
                        <input type = "submit" value = " Submit " name="update_busstop"/><br />
                      </div>
                  </div>
                </form>
                <div class = "errorContainer"><?php print_errors($busstop_up_errors); ?></div>
            </div>
          </div>
        </div>

      </div>
      <div class="clear section last">

        <div class="four_quarter lastbox">
          <div class="row">
              <div class="col-100">
                <h2>Lista przystanków</h2>
              </div>
          </div>
          <div class="row">
              <div class="col-100">
                <table id="list">
                  <tr>
                    <th>ID</th>
                    <th>NAZWA</th>
                  </tr>
                  <?php
                    include('config.php');
                    $sql = "SELECT * FROM przystanki ORDER BY nazwa";
                    $result = pg_query($db,$sql);
                    $rows = pg_fetch_all($result);
                    if(pg_num_rows($result) > 0){
                      foreach ($rows as $row) {
                          echo '<tr onclick="rowClick(this)"><td>' . $row['pid'] . '</td><td>' . $row['nazwa'] . '</td></tr>';
                      }
                    }
                    pg_close($db);
                  ?>
                </table>
              </div>
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

  message = "Wybierz co zrobic z przystankiem " + name;
  document.getElementById("rowClickMessage").innerHTML = message;
  document.getElementById('rowClick').style.display='block';
}
function deleteFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  driverID = td.textContent || td.innerText;
  document.getElementById('delete_busstop_select').value = driverID;
  //document.getElementById('delete_form').submit();
  document.getElementById('delete_busstop_submitButton').click();
  }
}
function updateFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  driverID = td.textContent || td.innerText;
  document.getElementById('update_busstop_select').value = driverID;
  document.getElementById('update_busstop_select').form.submit();
  //document.getElementById('delete_form').submit();
  }
}
</script>

</body>
</html>
