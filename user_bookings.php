<?php
  include('session.php');
  include('print_errors.php');
  function freeSeatsCheck2($kursid,$from_pid,$to_pid,$db,$avoidThisRezid = -1){
    $sql = "SELECT kursid FROM kursy WHERE kursid = $kursid FOR UPDATE";
    $result = pg_query($db,$sql);
    if(!$result) {
      pg_query($db,"ROLLBACK") or die("Transaction rollback failed\n");
      array_push($book_add_errors, pg_last_error($db));
    } else{
      //
      // POBERZ LICZBE MIEJSC W AUTOBUSIE ORAZ PRZYSTANKI NA LINI
      $sql = "SELECT a.lmiejsc, l.start_pid, l.end_pid_dist, l.inter_pid_dist
      FROM kursy k JOIN autokary a USING(autoid) JOIN linie l USING(lid) WHERE k.kursid = $kursid";
      $result = pg_query($db,$sql);
      if(!$result){
        pg_query($db,"ROLLBACK") or die("Transaction rollback failed\n");
        array_push($book_add_errors, pg_last_error($db));
      }else{
        $row = pg_fetch_all($result);
        if(pg_num_rows($result) == 1){
          $avalibleSeats = intval($row[0]['lmiejsc']); //liczba dostępnych miejsc

          $start_pid = intval($row[0]['start_pid']); // przyst poczatkowy
          $end_pid = convertToArray($row[0]['end_pid_dist'])[0]; // przytsanek koncowy
          $inter_pids = array(); // tablica przystankow posrednich

          if(isset($row[0]['inter_pid_dist'])){
            $inter_pid_dist = convertToArray($row[0]['inter_pid_dist']);
            foreach ($inter_pid_dist as $pid_dist_pair) {       
              $inter_pid = $pid_dist_pair[0];
              array_push($inter_pids, $inter_pid);
            }
          }

          array_unshift($inter_pids, $start_pid);
          array_push($inter_pids, $end_pid);
          $all_pids = $inter_pids; // wszytskie przystanki na lini po kolei

          $sql = "SELECT * FROM rezerwacje r WHERE kursid = $kursid"; // POBIERZ WSZYTSKIE REZERWACJE NA DANY KURS
          $result = pg_query($db,$sql);
          if(!$result){
            pg_query($db,"ROLLBACK") or die("Transaction rollback failed\n");
            array_push($book_add_errors, pg_last_error($db));
          }else{
            $bookings = pg_fetch_all($result);
            $freeSeats = $avalibleSeats;
            foreach ($bookings as $booking) {
              if($booking['rezid'] == $avoidThisRezid){
                $freeSeats+=1;
              }elseif( seatColision($all_pids,$from_pid,$to_pid,$booking['from_pid'],$booking['to_pid']) ){
                $freeSeats-=1;
              }
            }
            return $freeSeats;
          }
        }
      }
    }
  }
  function seatColision($all_pids,$from_pid,$to_pid,$booked_from_pid,$booked_to_pid){
    // ALGORYTM
    //sprawdz $booked_from_pid PRZED from_pid to   
    //    TAK sprawdz czy $booked_to_pid PRZED LUB TAKI SAM $from_pid
    //        TAK - brak kolizji //rezerwacja przed nasza rezerwacją
    //        NIE - kolizja // book end miedzy naszym from - to
    //    NIE sprawdz $booked_from_pid ZA LUB TAKI SAM to_pid
    //          TAK - brak kolizji //rezerwacja za nasza rezerwacją
    //          NIE - kolizja // book from miedzy naszym from - to
    $i=0;
    foreach ($all_pids as $pid) {
      if($pid == $booked_from_pid){
        $booked_from_index = $i;
      }
      if($pid == $from_pid){
        $from_index = $i;
      }
      if($pid == $booked_to_pid){
        $booked_to_index = $i;
      }
      if($pid == $to_pid){
        $to_index = $i;
      }
      $i+=1;
    }
    if($booked_from_index < $from_index){
      if($booked_to_index <= $from_index){
        return false;
      }else{
        return true;
      }
    }else{
      if($booked_from_index >= $to_index){
        return false;
      }else{
        return true;
      }
    }
  }
  $book_add_errors = array();
  $book_del_errors = array();
  $book_up_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST);
    // ADD KURS SELECT
    if (isset($_POST['book_add_kursid'])) {
      $add_book_kursid = pg_escape_string($db,$_POST['book_add_kursid']);
      if($add_book_kursid != 'default'){
        $add_book_kursid = intval($add_book_kursid);
      }
      open_modal('id01');
    }
    // ADD FROM/TO SELECT
    if (isset($_POST['book_add_from_pid']) and isset($_POST['book_add_to_pid'])) {
      $book_add_from_pid = pg_escape_string($db,$_POST['book_add_from_pid']);
      $book_add_to_pid = pg_escape_string($db,$_POST['book_add_to_pid']);
      if($book_add_from_pid != 'default' and $book_add_to_pid != 'default' and $add_book_kursid != 'default'){
        $freeSeats = freeSeatsCheck2($add_book_kursid, intval($book_add_from_pid), intval($book_add_to_pid),$db);
      }
      open_modal('id01');
    }
    // ADD  
    if (isset($_POST['add_book'])) {   
      $book_add_name = pg_escape_string($db,$_POST['book_add_name']);
      $book_add_surname = pg_escape_string($db,$_POST['book_add_surname']);
      $book_add_phone = pg_escape_string($db,$_POST['book_add_phone']);
      $book_add_email = pg_escape_string($db,$_POST['book_add_email']);
      $book_add_addInfo = pg_escape_string($db,$_POST['book_add_addInfo']);
      $book_add_kursId = pg_escape_string($db,$_POST['book_add_kursid']);
      $book_add_from_pid = pg_escape_string($db,$_POST['book_add_from_pid']);
      $book_add_to_pid = pg_escape_string($db,$_POST['book_add_to_pid']);

      if (empty($book_add_name)) {
         array_push($book_add_errors, "Imie jest wymagane");
      } elseif (empty($book_add_surname)) {
         array_push($book_add_errors, "Nazwisko jest wymagane");
      } elseif (empty($book_add_phone)) {
         array_push($book_add_errors, "Telefon jest wymagany");
      } elseif (empty($book_add_email)) {
         array_push($book_add_errors, "Email jest wymagany");
      } elseif ($book_add_kursId == 'default') {
         array_push($book_add_errors, "Wybierz kurs z listy");
      } elseif ($book_add_from_pid == 'default') {
         array_push($book_add_errors, "Wybierz przystanek startowy");
      } elseif ($book_add_to_pid == 'default') {
         array_push($book_add_errors, "Wybierz przystanek docelowy");
      } elseif (!preg_match("/^[0-9]{9}$/",$book_add_phone)) {
         array_push($book_add_errors, "Numer telefonu nie poprawny");
      } else{
        pg_query($db,"BEGIN") or die("Transaction begin failed\n");
        $seatsLeft = freeSeatsCheck2($book_add_kursId,$book_add_from_pid,$book_add_to_pid,$db);
        if($seatsLeft > 0){
          if(!empty($book_add_addInfo)){
            $sql = "INSERT INTO rezerwacje (imie,nazwisko,telefon,email,kursid,from_pid,to_pid,uwagi)
            VALUES('$book_add_name','$book_add_surname','$book_add_phone','$book_add_email',$book_add_kursId,$book_add_from_pid,$book_add_to_pid,'$book_add_addInfo')";
          }else{
            $sql = "INSERT INTO rezerwacje (imie,nazwisko,telefon,email,kursid,from_pid,to_pid)
            VALUES('$book_add_name','$book_add_surname','$book_add_phone','$book_add_email',$book_add_kursId,$book_add_from_pid,$book_add_to_pid)";
          }
          $result = pg_query($db,$sql);
          if(!$result) {
            pg_query($db,"ROLLBACK") or die("Transaction rollback failed\n");
            array_push($book_add_errors, pg_last_error($db));
          } else {
            $book_add_notify = "Rezerwacja dodana pomyślnie";
            open_modal('confirm-message');
            //echo "Commiting transaction\n";
            pg_query($db,"COMMIT") or die("Transaction commit failed\n");
            //echo pg_last_notice($db);
          }
        }else{
          pg_query($db,"ROLLBACK") or die("Transaction rollback failed\n");
          array_push($book_add_errors, "Brak wolnych miejsc");
        }
      }
    }
    // DELETE
    if (isset($_POST['delete_book'])) {
      $del_book_id = pg_escape_string($db,$_POST['book_del_id']);

      if($del_book_id != 'default'){
        $sql = "SELECT * FROM rezerwacje WHERE rezid = $del_book_id";
        $result = pg_query($db,$sql);
        $count = pg_num_rows($result);
        if($count == 1) {
          $sql = "DELETE FROM rezerwacje WHERE rezid = $del_book_id";
          $result = pg_query($db,$sql);
          if(!$result) {
             array_push($book_del_errors, pg_last_error($db));
          } else {
             $book_del_notify = "Rezerwacja usunięta pomyślnie";
          }
        }else{
          array_push($book_del_errors, "Rezerwacja o podanym ID nie istnieje");
        }
      }else{
        array_push($book_del_errors, "Wybierz rezerwację z listy");
      }
      if(!empty($book_del_errors)){
        open_modal('id02');
      }elseif(isset($book_del_notify)){
        open_modal('confirm-message');
      }
    }
    // UPDATE SELECT
    if (isset($_POST['book_up_id'])) {
      $up_book_id = pg_escape_string($db,$_POST['book_up_id']);
      if($up_book_id != 'default'){
      $sql = "SELECT * FROM rezerwacje WHERE rezid = $up_book_id";
      $result = pg_query($db,$sql);
        if(!$result) {
               array_push($book_up_errors, pg_last_error($db));
        }else{
          $row = pg_fetch_all($result);
          if(pg_num_rows($result) == 1){
              $update_name_inputPlaceholder = $row[0]['imie'];
              $update_surname_inputPlaceholder = $row[0]['nazwisko'];
              $update_phone_inputPlaceholder = $row[0]['telefon'];
              $update_email_inputPlaceholder = $row[0]['email'];
              $update_addInfo_inputPlaceholder = $row[0]['uwagi'];
              $update_kursId_selectValue = $row[0]['kursid'];
              $update_from_pid_selectValue = $row[0]['from_pid'];
              $update_to_pid_selectValue = $row[0]['to_pid'];
              $unhide = true;
          }
        } 
      }
      open_modal('id03');
    }
    // UPDATE
    if (isset($_POST['update_book'])){
      $up_book_id = pg_escape_string($db,$_POST['book_up_id']);
      $book_update_name = pg_escape_string($db,$_POST['book_up_name']);
      $book_update_surname = pg_escape_string($db,$_POST['book_up_surname']);
      $book_update_phone = pg_escape_string($db,$_POST['book_up_phone']);
      $book_update_email = pg_escape_string($db,$_POST['book_up_email']);
      $book_update_addInfo = pg_escape_string($db,$_POST['book_up_addInfo']);
      $book_update_kursId = pg_escape_string($db,$_POST['book_up_kursid']);
      $book_update_from_pid = pg_escape_string($db,$_POST['book_up_from_pid']);
      $book_update_to_pid = pg_escape_string($db,$_POST['book_up_to_pid']);

      if($up_book_id != 'default'){
        /*if (empty($book_update_name)
          and empty($book_update_surname)
          and empty($book_update_phone)
          and empty($book_update_email)
          and empty($book_update_addInfo)) {
         array_push($book_up_errors, "Brak zmian");
        }else*/if($book_update_kursId == 'default'){
          array_push($book_up_errors, "Wybierz kurs z listy");
        }elseif($book_update_from_pid == 'default'){
          array_push($book_up_errors, "Wybierz przystanek startowy");
        }elseif($book_update_to_pid == 'default'){
          array_push($book_up_errors, "Wybierz przystanek docelowy");
        }elseif(freeSeatsCheck2($book_update_kursId,$book_update_from_pid,$book_update_to_pid,$db,$up_book_id) <= 0){
          array_push($book_up_errors, "Zmiana przystanku początkowego lub końcwego spowoduje brak miejsc");
        }else{

          $setQuery = '';
          
          $kursid = $book_update_kursId;
          $from_pid = $book_update_from_pid;
          $to_pid = $book_update_to_pid;

          if(!empty($book_update_name)){
            $setQuery .= ", imie = '$book_update_name' ";
          }
          if(!empty($book_update_surname)){
            $setQuery .= ", nazwisko = '$book_update_surname' ";
          }
          if(!empty($book_update_phone)){
            $setQuery .= ", telefon = '$book_update_phone' ";
          }
          if(!empty($book_update_email)){
            $setQuery .= ", email = '$book_update_email' ";
          }
          if(!empty($book_update_addInfo)){
            $setQuery .= ", uwagi = '$book_update_addInfo' ";
          }

          $setQuery .= ", kursid=$book_update_kursId,from_pid=$from_pid,to_pid=$to_pid";
          $from = '/'.preg_quote(',', '/').'/';
          $setQuery = preg_replace($from, '', $setQuery, 1);

          //var_dump($setQuery);
          $sql = "UPDATE rezerwacje SET $setQuery WHERE rezid = $up_book_id";
          $result = pg_query($db,$sql);
            if(!$result) {
              array_push($book_up_errors, pg_last_error($db));
            }else{
              $book_up_notify = "Zmiany zostały dokonane pomyślnie";
              open_modal('confirm-message');
            }
        }
      }
    }
  }
  pg_close($db);
?>
<?php include("printOptions.php"); ?>
<html lang="en" dir="ltr">
<head>
<title>Rezerwacje</title>
<meta charset="UTF-8">
<link rel="stylesheet" href="styles/layout2.css" type="text/css">
</head>
<body>
<div class="wrapper row1">
  <div id="header" class="clear">
    <div id="hgroup">
      <h1><a href="index.php">Witaj <?php echo $login_session ?></a></h1>
      <h2>System zarządzania firmy przewozowej</h2>
    </div>
    <nav>
      <ul>
        <li><a href="index.php">Strona Główna</a></li>
        <li><a href="user_manage.php">Zarządzanie Kontem</a></li>
        <li><a class="active"  href="user_bookings.php">Rezerwacje</a></li>
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
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Dodaj Rezerwacje</button>
        <button onclick="document.getElementById('id02').style.display='block'" style="width:auto;">Usuń Rezerwacje</button>
        <button onclick="document.getElementById('id03').style.display='block'" style="width:auto;">Edytuj Rezerwacje</button>
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
                if(isset($book_add_notify)){ 
                  echo "<h2>$book_add_notify</h2>";
                }elseif(isset($book_del_notify)){
                  echo "<h2>$book_del_notify</h2>";
                }elseif(isset($book_up_notify)){
                  echo "<h2>$book_up_notify</h2>";
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
                  <h2>Dodaj Rezerwację</h2>
                </div>
            </div>
              <form action = "" method = "post" id="update_form">
                <div class="row">
                  <div class="col-25">
                    <label>Kurs:</label>
                  </div>
                  <div class="col-75">
                    <select name="book_add_kursid" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php printOptionsKursy($add_book_kursid); ?>
                    </select>
                </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Z:</label>
                  </div>
                  <div class="col-75">
                    <select name="book_add_from_pid" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php printOptionsBusstopsByKursId($add_book_kursid,$book_add_from_pid); ?>
                    </select>
                </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Do:</label>
                  </div>
                  <div class="col-75">
                    <select name="book_add_to_pid" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php printOptionsBusstopsByKursId($add_book_kursid,$book_add_to_pid); ?>
                    </select>
                    <p>Liczba wolych miejsc: <span><?php if(isset($freeSeats)){echo $freeSeats;} ?></span></p>
                </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Imię:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "book_add_name" class = "box"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Nazwisko:</label>
                  </div>
                  <div class="col-75">
                      <input type = "text" name = "book_add_surname" class = "box"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Telefon:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "book_add_phone" class = "box"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Email:</label>
                  </div>
                  <div class="col-75">
                    <input type = "email" name = "book_add_email" class = "box"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Dodatkowe uwagi:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "book_add_addInfo" class = "box"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-100">
                    <input type = "submit" value = " Submit " name="add_book" /><br />
                  </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($book_add_errors); ?></div>
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
                  <h2>Usuń Rezerwację</h2>
                </div>
              </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-100">
                    <select id="delete_book_bookSelect" name="book_del_id">
                    <option value="default">Select</option>
                      <?php printOptionsBookings(); ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                    <div class="col-100">
                      <input id="delete_book_submitButton" type = "submit" value = " Submit " name="delete_book"/><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($book_del_errors); ?></div>
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
                  <h2>Edytuj Rezerwacje</h2>
                </div>
            </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-25">
                    <label>Wybierz rezerwacje:</label>
                  </div>
                  <div class="col-75">
                    <select id="update_book_bookSelect" name="book_up_id" onchange="this.form.submit()">
                    <option value="default">Select</option>
                      <?php printOptionsBookings($up_book_id); ?>
                    </select>
                    <br /><br />
                  </div>
                </div>
              <div <?php if(!isset($unhide)){ echo ' hidden '; } ?> >

                <div class="row">
                  <div class="col-25">
                    <label>Kurs:</label>
                  </div>
                  <div class="col-75">
                    <select name="book_up_kursid">
                    <option value="default">Select</option>
                      <?php printOptionsKursy($update_kursId_selectValue) ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Z:</label>
                  </div>
                  <div class="col-75">
                    <select name="book_up_from_pid">
                    <option value="default">Select</option>
                      <?php printOptionsBusstopsByKursId($update_kursId_selectValue,$update_from_pid_selectValue); ?>
                    </select>
                </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Do:</label>
                  </div>
                  <div class="col-75">
                    <select name="book_up_to_pid">
                    <option value="default">Select</option>
                      <?php printOptionsBusstopsByKursId($update_kursId_selectValue,$update_to_pid_selectValue); ?>
                    </select>
                </div>
                </div>

                <div class="row">
                  <div class="col-25">
                    <label>Imię:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "book_up_name" class = "box" placeholder=
                      <?php
                        if(isset($update_name_inputPlaceholder)){
                          echo '"' . $update_name_inputPlaceholder . '"'; 
                        }else{
                          echo '""';
                        }
                      ?>
                     />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Nazwisko:</label>
                  </div>
                  <div class="col-75">
                      <input type = "text" name = "book_up_surname" class = "box" placeholder=
                      <?php
                        if(isset($update_surname_inputPlaceholder)){
                          echo '"' . $update_surname_inputPlaceholder . '"'; 
                        }else{
                          echo '""';
                        }
                      ?>/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Telefon:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "book_up_phone" class = "box" placeholder=
                      <?php
                        if(isset($update_phone_inputPlaceholder)){
                          echo '"' . $update_phone_inputPlaceholder . '"'; 
                        }else{
                          echo '""';
                        }
                      ?>/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Email:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "book_up_email" class = "box" placeholder=
                      <?php
                        if(isset($update_email_inputPlaceholder)){
                          echo '"' . $update_email_inputPlaceholder . '"'; 
                        }else{
                          echo '""';
                        }
                      ?>/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Dodatkowe uwagi:</label>
                  </div>
                  <div class="col-75">
                    <input type = "text" name = "book_up_addInfo" class = "box" placeholder=
                      <?php
                        if(isset($update_addInfo_inputPlaceholder)){
                          echo '"' . $update_addInfo_inputPlaceholder . '"'; 
                        }else{
                          echo '""';
                        }
                      ?>/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-100">
                    <input type = "submit" value = " Submit " name="update_book" /><br />
                  </div>
                </div>
            </div>
            </form>
            <div class = "errorContainer"><?php print_errors($book_up_errors); ?></div>
            </div>
          </div>
        </div>

      </div>
      <div class="clear section">
        <div class="two_quarter">
            <h2>Lista Rezerwacji</h2>
        </div>
        <div class="two_quarter lastbox">
          <form class="fl_right">
            <label><input type="checkbox" onchange="checkboxClick(this)"> Pokzuj tylko rezerwacje na aktualne kursy</label>
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
                    <th>IMIĘ</th>
                    <th>NAZWISKO</th>
                    <th>TELEFON</th>
                    <th>EMAIL</th>
                    <th>UWAGI</th>
                    <th>CZAS ODJAZDU</th>
                    <th>Z</th>
                    <th>DO</th>
                    <th>LINIA</th>
                  </tr>
                  <?php
                    include('config.php');
                    $sql = "SELECT r.*, k.data_odjazdu, l.lid
                    FROM rezerwacje r JOIN kursy k USING(kursid) JOIN linie l USING(lid)
                    ORDER BY k.data_odjazdu";
                    $result = pg_query($db,$sql);
                    $rows = pg_fetch_all($result);
                    if(pg_num_rows($result) > 0){
                      foreach ($rows as $row) {
                          $line_name = lidToName($row['lid'],$db);
                          $from_pid_name = pidToName($row['from_pid'],$db);
                          $to_pid_name = pidToName($row['to_pid'],$db);
                          echo ('<tr onclick="rowClick(this)" data-date-filtered="false" data-search-filtered="false"><td>' . $row['rezid'] .    '</td><td>' . $row['imie'] .
                                '</td><td>' . $row['nazwisko'] .
                                '</td><td>' . $row['telefon'] .
                                '</td><td>' . $row['email'] .
                                '</td><td>' . $row['uwagi'] .
                                '</td><td>' . $row['data_odjazdu'] .
                                '</td><td>' . $from_pid_name .
                                '</td><td>' . $to_pid_name . 
                                '</td><td>' . $line_name .   
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
  bookID = td.textContent || td.innerText;
  console.log(bookID);
  console.log(document.getElementById('delete_book_bookSelect'));
  document.getElementById('delete_book_bookSelect').value = bookID;
  //document.getElementById('delete_form').submit();
  document.getElementById('delete_book_submitButton').click();
  }
}
function updateFormAutofill(){
  if(lastRowClicked){
  td = lastRowClicked.getElementsByTagName("td")[0];
  bookID = td.textContent || td.innerText;
  document.getElementById('update_book_bookSelect').value = bookID;
  document.getElementById('update_book_bookSelect').form.submit();
  //document.getElementById('delete_form').submit();
  }
}
function checkboxClick(o){
  var input, filter, table, tr, td, i, dateTimeRowTxt, dateTimeRow;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("list");
  tr = table.getElementsByTagName("tr");
  if(o.checked == true){
    var today = new Date();

    for (i = 1; i < tr.length; i++) {
      td = tr[i].getElementsByTagName("td")[6];
      dateTimeRowTxt = td.textContent || td.innerText;
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
