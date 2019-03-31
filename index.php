<?php
   include('session.php');

   pg_close($db);
?>
<html lang="en" dir="ltr">
<head>
<title>Strona główna</title>
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
        <li><a class="active" href="index.php">Strona Główna</a></li>
        <li><a href="user_manage.php">Zarządzanie Kontem</a></li>
        <li><a href="user_bookings.php">Rezerwacje</a></li>
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
        <img src="images/bus.jpg" alt="bus" height="540" width="960">
      </div>
  </div>
</div>
<!-- Footer -->
<div class="wrapper row3">
  <div id="footer" class="clear">
    <p class="fl_left">Created by Marek Zajac</p>
    <!--<p class="fl_right">Template by <a target="_blank" href="https://www.os-templates.com/" title="Free Website Templates">OS Templates</a></p>-->
  </div>
</div>

</body>
</html>
