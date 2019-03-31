<?php
  include('admin_session.php');
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
        <li><a class="active" href="admin_home.php">Strona Główna</a></li>
        <li><a href="admin_manage_accounts.php">Zarządzanie Kontami</a></li>
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
