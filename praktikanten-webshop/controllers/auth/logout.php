<?php
session_start();
session_unset();      // entfernt alle Session-Variablen
session_destroy();    // zerstört die Session komplett

header("Location: ../../public/index.php?page=home");
exit;
