<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'includes/functions.php';
require_user_login();
$page_title = 'AI Chatbot';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
$extra_js = '<script src="' . SITE_URL . '/assets/js/chatbot.js"></script>';
?>
<?php include 'includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar">
        <div>
            <div class="fw-semibold text-white">AI Security Assistant</div>
            <div class="text-muted small">Powered by Google Gemini</div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i>Online</span>
        </div>
    </div>
    <div class="ps-content" style="max-width:900px;">
        <div class="ps-card p-0 overflow-hidden">
            <!-- Chat window -->
            <div id="chatWindow" class="chat-window" style="min-height:400px;">
                <div class="d-flex justify-content-start">
                    <div class="chat-bubble bot">
                        <strong>👋 Hi, I'm PhishShield AI!</strong><br>
                        I'm your cybersecurity assistant. Ask me anything about phishing, suspicious URLs, email threats, or how to stay safe online.
                    </div>
                </div>
            </div>
            <!-- Input -->
            <div style="border-top:1px solid var(--ps-border); padding:1rem;">
                <form id="chatForm" class="d-flex gap-2">
                    <textarea id="chatInput" class="form-control glow-input" rows="2"
                        placeholder="Ask about phishing, suspicious URLs, email threats..." style="resize:none;"></textarea>
                    <button type="submit" id="sendBtn" class="btn btn-accent px-4" style="align-self:flex-end;">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
                <div class="text-muted mt-2" style="font-size:.75rem;">
                    Press <kbd>Enter</kbd> to send · <kbd>Shift+Enter</kbd> for new line
                </div>
            </div>
        </div>

        <!-- Quick prompts -->
        <div class="mt-3">
            <div class="text-muted small mb-2">Quick Questions:</div>
            <div class="d-flex flex-wrap gap-2">
                <?php
                $prompts = [
                    'What is phishing?',
                    'How to spot a phishing email?',
                    'Is bit.ly safe to use?',
                    'What are signs of a fake website?',
                    'How to report phishing?'
                ];
                foreach ($prompts as $p): ?>
                <button class="btn btn-sm" style="background:var(--ps-dark3);border:1px solid var(--ps-border);color:var(--ps-text);"
                    onclick="document.getElementById('chatInput').value='<?= htmlspecialchars($p) ?>'; document.getElementById('chatForm').dispatchEvent(new Event('submit'));">
                    <?= h($p) ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</div>
<script>
// Make SITE_URL accessible to chatbot.js
window.PHISHSHIELD_API = '<?= SITE_URL ?>/api/chatbot.php';
</script>
<script>
// Override fetch URL in chatbot.js
document.addEventListener('DOMContentLoaded',function(){
    var form = document.getElementById('chatForm');
    if(!form) return;
    form.addEventListener('submit',function(e){
        e.preventDefault();
        var msg = document.getElementById('chatInput').value.trim();
        if(!msg) return;
        appendBubble('user',msg);
        document.getElementById('chatInput').value='';
        document.getElementById('sendBtn').disabled=true;
        var typing=appendTyping();
        fetch('<?= SITE_URL ?>/api/chatbot.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'message='+encodeURIComponent(msg)
        }).then(r=>r.json()).then(data=>{
            typing.remove();
            appendBubble('bot',data.reply||'Sorry, no response.');
            document.getElementById('sendBtn').disabled=false;
        }).catch(()=>{
            typing.remove();
            appendBubble('bot','Connection error. Please check your Gemini API key.');
            document.getElementById('sendBtn').disabled=false;
        });
    });
});
function appendBubble(role,text){
    var w=document.getElementById('chatWindow');
    var d=document.createElement('div');
    d.className='d-flex '+(role==='user'?'justify-content-end':'justify-content-start');
    var b=document.createElement('div');
    b.className='chat-bubble '+role;
    b.textContent=text;
    d.appendChild(b);w.appendChild(d);w.scrollTop=w.scrollHeight;return d;
}
function appendTyping(){
    var w=document.getElementById('chatWindow');
    var d=document.createElement('div');
    d.className='d-flex justify-content-start';
    d.innerHTML='<div class="chat-bubble bot"><span class="typing-dots"><span></span><span></span><span></span></span></div>';
    w.appendChild(d);w.scrollTop=w.scrollHeight;return d;
}
document.getElementById('chatInput').addEventListener('keydown',function(e){
    if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();document.getElementById('chatForm').dispatchEvent(new Event('submit'));}
});
</script>
<?php include 'includes/footer.php'; ?>
