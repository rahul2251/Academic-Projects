<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/gemini.php';
require_login();
$user = current_user($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {
    header('Content-Type: application/json');
    $msg = trim($_POST['msg']);
    if ($msg === '') { echo json_encode(['reply' => '...']); exit; }
    $stmt = $pdo->prepare("INSERT INTO chat_history (user_id,role,message) VALUES (?,?,?)");
    $stmt->execute([$user['id'],'user',$msg]);
    $reply = chatBotReply($msg);
    $stmt->execute([$user['id'],'bot',$reply]);
    echo json_encode(['reply' => $reply]);
    exit;
}

$hist = $pdo->prepare("SELECT role, message FROM chat_history WHERE user_id=? ORDER BY id ASC LIMIT 50");
$hist->execute([$user['id']]);
$hist = $hist->fetchAll();

$pageTitle = 'AI Chatbot';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="ps-app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="ps-main fade-in">
    <div class="ps-page-head"><h1><i class="fa-solid fa-robot text-cyan"></i> Cybersecurity Chatbot</h1></div>

    <div class="ps-card chat-box">
      <div class="chat-log" id="chatLog">
        <?php if (!$hist): ?>
          <div class="chat-msg bot">👋 Hi <?= e($user['name']) ?>! I'm PhishShield AI. Ask me anything about phishing, scams or staying safe online.</div>
        <?php else: foreach ($hist as $h): ?>
          <div class="chat-msg <?= $h['role']==='user'?'user':'bot' ?>"><?= e($h['message']) ?></div>
        <?php endforeach; endif; ?>
      </div>
      <form id="chatForm" class="d-flex gap-2 mt-3">
        <input id="chatInput" class="form-control" placeholder="Ask something like: How do I spot a phishing email?" required>
        <button class="btn btn-grad"><i class="fa-solid fa-paper-plane"></i></button>
      </form>
    </div>
  </main>
</div>

<script>
const log  = document.getElementById('chatLog');
const form = document.getElementById('chatForm');
const inp  = document.getElementById('chatInput');

function addMsg(role, text) {
  const d = document.createElement('div');
  d.className = 'chat-msg ' + role;
  d.textContent = text;
  log.appendChild(d);
  log.scrollTop = log.scrollHeight;
  return d;
}
log.scrollTop = log.scrollHeight;

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const msg = inp.value.trim();
  if (!msg) return;
  addMsg('user', msg);
  inp.value=''; inp.disabled=true;
  const thinking = addMsg('bot','Thinking…');
  try {
    const fd = new FormData(); fd.append('msg', msg);
    const r = await fetch('chatbot.php', { method:'POST', body: fd });
    const j = await r.json();
    thinking.textContent = j.reply || '(no reply)';
  } catch (err) {
    thinking.textContent = 'Error contacting AI.';
  }
  inp.disabled=false; inp.focus();
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
