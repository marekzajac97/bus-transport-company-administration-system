<?php
  include('admin_session.php');
  include('print_errors.php');
  $reg_errors = array();
  $del_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // REGISTER  
    if (isset($_POST['register_user'])) {   
      $regusername = pg_escape_string($db,$_POST['reg_username']);
      $regpassword = pg_escape_string($db,$_POST['reg_password']);
      $regpassoword2 = pg_escape_string($db,$_POST['reg_confirm_password']);
      if( empty($regusername) and empty($regpassword)){
         array_push($reg_errors, "Nazwa użytkownika i hasło są wymagane");
      } elseif (empty($regusername)) {
         array_push($reg_errors, "Nazwa użytkownika jest wymagana");
      } elseif(empty($regpassword)){
         array_push($reg_errors, "Hasło jest wymagane");
      } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/",$regpassword)) {
        array_push($reg_errors, "Hasło musi mieć długość conajmniej 8 znaków i zawierać przynajmniej jedną literę i jedną liczbę"); 
      } elseif($regpassword != $regpassoword2){
         array_push($reg_errors, "Hasła są różne");
      } else{
         $sql = "SELECT uid FROM users WHERE username = '$regusername'";
         $result = pg_query($db,$sql);
         $count = pg_num_rows($result);
         
         if($count == 1) {
            array_push($reg_errors, "Użytkownik o podanej nazwie już istnieje");
         }else {
            $regpassword = md5($regpassword);
            $sql = "INSERT INTO users (username,password) VALUES('$regusername','$regpassword')";
            $result = pg_query($db,$sql);
            if(!$result) {
              array_push($reg_errors, pg_last_error($db));
            } else {
               $reg_notify = "użytkownik dodany poprawnie";
            }
         }
      }
      if(!empty($reg_errors)){
        open_modal('id01');
      }elseif(isset($reg_notify)){
        open_modal('confirm-message');
      }
    }
    // DELETE
    if (isset($_POST['delete_user'])) {
      $delusername = pg_escape_string($db,$_POST['del_username']);
      $sql = "SELECT uid FROM users WHERE username = '$delusername'";
      $result = pg_query($db,$sql);
      $count = pg_num_rows($result);
      if($count == 1) {
        $sql = "DELETE FROM users where username='$delusername'";
        $result = pg_query($db,$sql);
        if(!$result) {
           array_push($del_errors, pg_last_error($db));
        } else {
           $del_notify = "User deleted successfully";
        }
      }else {
        array_push($del_errors, "Użytkownik o podanej nazwie nie istnieje");
      }
      if(!empty($del_errors)){
        open_modal('id02');
      }elseif(isset($del_notify)){
        open_modal('confirm-message');
      }
    }
  }

  pg_close($db);
?>
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
        <li><a class="active" href="admin_manage_accounts.php">Zarządzanie Kontami</a></li>
        <li><a href="admin_manage_busstop.php">Przystanki</a></li>
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
      <!-- services area -->
      <div class="clear section">
        <div class="two_quarter">
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Zarejestruj Użytkownika</button>
        <button onclick="document.getElementById('id02').style.display='block'" style="width:auto;">Usuń Użytkownika</button>
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
                if(isset($reg_notify)){ 
                  echo "<h2>$reg_notify</h2>";
                }elseif(isset($del_notify)){
                  echo "<h2>$del_notify</h2>";
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
              <!--<button onclick="updateFormAutofill()" style="width:auto;">Modyfikuj</button>-->
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
                  <h2>Zarejestruj nowego użytkownika</h2>
                </div>
            </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-25">
                    <label>Nazwa użytkownika:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "reg_username" class = "box"/><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Hasło:</label>
                  </div>
                  <div class="col-75">
                    <input type = "password" name = "reg_password" class = "box" /><br/><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Potwierdz hasło:</label>
                  </div>
                  <div class="col-75">
                    <input type = "password" name = "reg_confirm_password" class = "box" /><br/><br />
                  </div>
                </div>
                <div class="row">
                    <div class="col-100">
                      <input type = "submit" value = " Submit " name="register_user" /><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($reg_errors); ?></div>
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
                    <h2>Usuń użytkownika</h2>
                  </div>
              </div>
                <form action = "" method = "post">
                  <div class="row">
                    <div class="col-25">
                      <label>Nazwa użytkownika:</label>
                    </div>
                    <div class="col-75">
                      <input type = "text" id="delete_usernameInput" name = "del_username" class = "box"/><br /><br />
                    </div>
                  </div>
                  <div class="row">
                      <div class="col-100">
                        <input id="delete_submitButton" type = "submit" value = " Submit " name="delete_user"/><br />
                      </div>
                  </div>
                </form>
                <div class = "errorContainer"><?php print_errors($del_errors); ?></div>
            </div>
          </div>
        </div>

      </div>
      <div class="clear section last">

        <div class="four_quarter lastbox">
              <div class="row">
                  <div class="col-100">
                    <h2>Lista użytkowników</h2>
                  </div>
              </div>
              <div class="row">
                  <div class="col-100">
                    <table id="list">
                      <tr>
                        <th>ID</th>
                        <th>NAZWA UŻYTKOWNIKA</th>
                        <th>MD5 HASH HASŁA</th>
                      </tr>
                      <?php
                        include('config.php');
                        $sql = "SELECT * FROM users ORDER BY username";
                        $result = pg_query($db,$sql);
                        $rows = pg_fetch_all($result);
                        if(pg_num_rows($result) > 0){
                          foreach ($rows as $row) {
                              echo '<tr onclick="rowClick(this)"><td>' . $row['uid'] . '</td><td>' . $row['username'] . '</td><td>' . $row['password'] . '</td></tr>';
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
  var td, message;
  td = o.getElementsByTagName("td")[1];
  message = "Usunąć użytkownika " + (td.textContent || td.innerText) + "?";
  document.getElementById("rowClickMessage").innerHTML = message;
  document.getElementById('rowClick').style.display='block';
}
function deleteFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[1];
  username = td.textContent || td.innerText;
  document.getElementById('delete_usernameInput').value = username;
  //document.getElementById('delete_form').submit();
  document.getElementById('delete_submitButton').click();
  }
}
function updateFormAutofill(){

}

</script>

</body>
</html>
