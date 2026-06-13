<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="ps-sidebar">
  <div class="ps-side-brand">
    <i class="fa-solid fa-shield-halved"></i>
    <span>PhishShield</span>
  </div>
  <ul class="ps-side-menu">
    <li><a href="<?= APP_URL ?>/dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="<?= APP_URL ?>/scanner.php"   class="<?= $current=='scanner.php'  ?'active':'' ?>"><i class="fa-solid fa-magnifying-glass"></i> Scanner</a></li>
    <li><a href="<?= APP_URL ?>/history.php"   class="<?= $current=='history.php'  ?'active':'' ?>"><i class="fa-solid fa-clock-rotate-left"></i> History</a></li>
    <li><a href="<?= APP_URL ?>/chatbot.php"   class="<?= $current=='chatbot.php'  ?'active':'' ?>"><i class="fa-solid fa-robot"></i> Chatbot</a></li>
    <li><a href="<?= APP_URL ?>/feedback.php"  class="<?= $current=='feedback.php' ?'active':'' ?>"><i class="fa-solid fa-comment-dots"></i> Feedback</a></li>
    <li><a href="<?= APP_URL ?>/settings.php"  class="<?= $current=='settings.php' ?'active':'' ?>"><i class="fa-solid fa-gear"></i> Settings</a></li>
    <li class="mt-auto"><a href="<?= APP_URL ?>/logout.php" class="text-danger"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a></li>
  </ul>
</aside>
