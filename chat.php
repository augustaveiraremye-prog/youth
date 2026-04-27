<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gatsinde Youth – Chat</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="chat-page">

<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$current_user = $_SESSION['user'];
$current_name = $_SESSION['fullname'];
$current_avatar = $_SESSION['avatar'];

// Load users
$users_file = __DIR__ . '/includes/users.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

// Messages file
$msgs_file = __DIR__ . '/includes/messages.json';
if (!file_exists($msgs_file)) file_put_contents($msgs_file, json_encode([]));

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $messages = json_decode(file_get_contents($msgs_file), true);
    $text = trim($_POST['message']);
    $to = trim($_POST['to'] ?? 'public');
    if (!empty($text)) {
        $messages[] = [
            'id'     => uniqid(),
            'from'   => $current_user,
            'name'   => $current_name,
            'avatar' => $current_avatar,
            'to'     => $to,
            'text'   => htmlspecialchars($text),
            'time'   => time(),
        ];
        // Keep last 500 messages
        if (count($messages) > 500) $messages = array_slice($messages, -500);
        file_put_contents($msgs_file, json_encode($messages));
    }
    if (isset($_POST['ajax'])) {
        echo json_encode(['ok' => true]);
        exit;
    }
    header('Location: chat.php?room=' . urlencode($to));
    exit;
}

// Load messages
$messages = json_decode(file_get_contents($msgs_file), true);
$room = $_GET['room'] ?? 'public';

// Filter messages for this room
$room_messages = array_filter($messages, function($m) use ($room, $current_user) {
    if ($room === 'public') return $m['to'] === 'public';
    return ($m['to'] === $room && $m['from'] === $current_user)
        || ($m['to'] === $current_user && $m['from'] === $room);
});

// Get conversation list (DM partners + public)
$dm_partners = [];
foreach ($messages as $m) {
    if ($m['from'] === $current_user && $m['to'] !== 'public') {
        $dm_partners[$m['to']] = true;
    }
    if ($m['to'] === $current_user) {
        $dm_partners[$m['from']] = true;
    }
}

// Other users (not current)
$other_users = array_filter($users, fn($u, $k) => $k !== $current_user, ARRAY_FILTER_USE_BOTH);
?>

<div class="app-layout">

  <!-- SIDEBAR LEFT: Navigation -->
  <aside class="sidebar-nav">
    <div class="nav-logo">
      <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
        <circle cx="24" cy="24" r="24" fill="url(#gn)"/>
        <circle cx="24" cy="24" r="4" fill="white"/>
        <path d="M14 24C14 18.477 18.477 14 24 14s10 4.477 10 10-4.477 10-10 10" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
        <defs>
          <linearGradient id="gn" x1="0" y1="0" x2="48" y2="48">
            <stop offset="0%" stop-color="#F5785A"/>
            <stop offset="100%" stop-color="#833AB4"/>
          </linearGradient>
        </defs>
      </svg>
    </div>

    <nav class="nav-items">
      <a href="chat.php?room=public" class="nav-item <?= $room === 'public' ? 'active' : '' ?>" title="Community">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <span>Community</span>
      </a>
      <a href="#messages" class="nav-item <?= $room !== 'public' ? 'active' : '' ?>" title="Messages">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <span>Messages</span>
      </a>
      <a href="#explore" class="nav-item" title="Members">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <span>Members</span>
      </a>
    </nav>

    <div class="nav-bottom">
      <div class="nav-user">
        <img src="<?= htmlspecialchars($current_avatar) ?>" alt="avatar" class="nav-avatar"/>
        <div class="nav-user-info">
          <span class="nav-username"><?= htmlspecialchars($current_name) ?></span>
          <span class="nav-handle">@<?= htmlspecialchars($current_user) ?></span>
        </div>
      </div>
      <a href="logout.php" class="logout-btn" title="Logout">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
  </aside>

  <!-- SIDEBAR CENTER: Conversations -->
  <aside class="sidebar-chats">
    <div class="chats-header">
      <h2><?= htmlspecialchars($current_name) ?></h2>
      <button class="icon-btn" title="New message">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
    </div>

    <div class="search-bar">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      <input type="text" placeholder="Search conversations..." id="searchInput"/>
    </div>

    <!-- Stories row -->
    <div class="stories-row">
      <div class="story-item active-story">
        <div class="story-ring">
          <img src="<?= htmlspecialchars($current_avatar) ?>" alt="your story"/>
        </div>
        <span>Your Story</span>
      </div>
      <?php foreach(array_slice($other_users, 0, 5, true) as $uname => $udata): ?>
      <div class="story-item">
        <div class="story-ring">
          <img src="<?= htmlspecialchars($udata['avatar']) ?>" alt="<?= htmlspecialchars($uname) ?>"/>
        </div>
        <span><?= htmlspecialchars(explode(' ', $udata['fullname'])[0]) ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Conversation list -->
    <div class="convo-list">
      <!-- Public room -->
      <a href="chat.php?room=public" class="convo-item <?= $room === 'public' ? 'active' : '' ?>">
        <div class="convo-avatar community-avatar">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="white" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="white" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div class="convo-info">
          <span class="convo-name">Community Chat</span>
          <span class="convo-preview">Everyone is here 🌍</span>
        </div>
        <span class="convo-badge">Live</span>
      </a>

      <!-- DM list -->
      <?php foreach($other_users as $uname => $udata): ?>
      <?php
        // Get last message with this user
        $last_msg = null;
        foreach(array_reverse($messages) as $m) {
            if (($m['from'] === $current_user && $m['to'] === $uname) ||
                ($m['from'] === $uname && $m['to'] === $current_user)) {
                $last_msg = $m;
                break;
            }
        }
        $has_convo = isset($dm_partners[$uname]);
      ?>
      <a href="chat.php?room=<?= urlencode($uname) ?>" class="convo-item <?= $room === $uname ? 'active' : '' ?> <?= !$has_convo ? 'dim' : '' ?>">
        <div class="convo-avatar-wrap">
          <img src="<?= htmlspecialchars($udata['avatar']) ?>" class="convo-avatar-img" alt="<?= htmlspecialchars($uname) ?>"/>
          <span class="online-dot"></span>
        </div>
        <div class="convo-info">
          <span class="convo-name"><?= htmlspecialchars($udata['fullname']) ?></span>
          <span class="convo-preview">
            <?= $last_msg ? htmlspecialchars(mb_substr($last_msg['text'], 0, 28)) . (mb_strlen($last_msg['text']) > 28 ? '…' : '') : '@' . htmlspecialchars($uname) ?>
          </span>
        </div>
        <?php if($last_msg): ?>
        <span class="convo-time"><?= date('H:i', $last_msg['time']) ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>

      <?php if(empty($other_users)): ?>
      <div class="empty-state">
        <p>No other members yet.<br/>Share the link to invite friends!</p>
      </div>
      <?php endif; ?>
    </div>
  </aside>

  <!-- MAIN: Chat Window -->
  <main class="chat-main">
    <!-- Chat Header -->
    <div class="chat-header">
      <?php if($room === 'public'): ?>
      <div class="chat-header-info">
        <div class="chat-avatar community-avatar sm">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="white" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="white" stroke-width="2"/></svg>
        </div>
        <div>
          <h3>Community Chat</h3>
          <span class="chat-status"><?= count($users) ?> members · Everyone can see this</span>
        </div>
      </div>
      <?php elseif(isset($users[$room])): ?>
      <div class="chat-header-info">
        <img src="<?= htmlspecialchars($users[$room]['avatar']) ?>" class="chat-avatar-img"/>
        <div>
          <h3><?= htmlspecialchars($users[$room]['fullname']) ?></h3>
          <span class="chat-status online">● Active now</span>
        </div>
      </div>
      <?php endif; ?>
      <div class="chat-header-actions">
        <button class="icon-btn">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .14h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.13-1.13a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <button class="icon-btn">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <button class="icon-btn">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
        </button>
      </div>
    </div>

    <!-- Messages -->
    <div class="messages-area" id="messagesArea">
      <?php if(empty($room_messages)): ?>
      <div class="messages-empty">
        <div class="empty-icon">💬</div>
        <p><?= $room === 'public' ? 'Be the first to say something in Community Chat!' : 'Start a conversation!' ?></p>
      </div>
      <?php endif; ?>

      <?php
      $prev_date = '';
      $prev_sender = '';
      foreach($room_messages as $msg):
        $is_me = $msg['from'] === $current_user;
        $msg_date = date('M j', $msg['time']);
        $is_new_sender = $msg['from'] !== $prev_sender;
        $prev_sender = $msg['from'];
      ?>

      <?php if($msg_date !== $prev_date): ?>
      <div class="date-divider"><span><?= $msg_date ?></span></div>
      <?php $prev_date = $msg_date; endif; ?>

      <div class="message-row <?= $is_me ? 'me' : 'them' ?>">
        <?php if(!$is_me && $is_new_sender): ?>
        <img src="<?= htmlspecialchars($msg['avatar']) ?>" class="msg-avatar" alt="avatar"/>
        <?php elseif(!$is_me): ?>
        <div class="msg-avatar-spacer"></div>
        <?php endif; ?>

        <div class="message-group">
          <?php if(!$is_me && $is_new_sender): ?>
          <span class="msg-sender-name"><?= htmlspecialchars($msg['name']) ?></span>
          <?php endif; ?>
          <div class="bubble">
            <?= htmlspecialchars($msg['text']) ?>
          </div>
          <span class="msg-time"><?= date('H:i', $msg['time']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Input Area -->
    <div class="chat-input-area">
      <form id="msgForm" method="POST">
        <input type="hidden" name="to" value="<?= htmlspecialchars($room) ?>"/>
        <input type="hidden" name="ajax" value="1"/>
        <button type="button" class="input-icon-btn" title="Emoji">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M8 14s1.5 2 4 2 4-2 4-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="9" x2="9.01" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="15" y1="9" x2="15.01" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <div class="input-wrapper">
          <input
            type="text"
            name="message"
            id="messageInput"
            placeholder="Message <?= $room === 'public' ? 'Community...' : htmlspecialchars($users[$room]['fullname'] ?? 'user') ?>..."
            autocomplete="off"
          />
        </div>
        <button type="button" class="input-icon-btn" title="Image">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/><polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <button type="submit" class="send-btn" id="sendBtn">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polygon points="22 2 15 22 11 13 2 9 22 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </form>
    </div>
  </main>

  <!-- RIGHT PANEL: Profile / Members -->
  <aside class="sidebar-profile">
    <div class="profile-header">
      <?php if($room === 'public'): ?>
      <div class="community-icon">🌍</div>
      <h3>Community Chat</h3>
      <p>Open group · <?= count($users) ?> members</p>
      <?php elseif(isset($users[$room])): ?>
      <img src="<?= htmlspecialchars($users[$room]['avatar']) ?>" class="profile-avatar"/>
      <h3><?= htmlspecialchars($users[$room]['fullname']) ?></h3>
      <p>@<?= htmlspecialchars($room) ?></p>
      <span class="profile-since">Member since <?= $users[$room]['joined'] ?? '2024' ?></span>
      <?php endif; ?>
    </div>

    <div class="profile-section">
      <h4>Members</h4>
      <div class="members-list">
        <?php foreach($users as $uname => $udata): ?>
        <a href="chat.php?room=<?= urlencode($uname) ?>" class="member-row">
          <img src="<?= htmlspecialchars($udata['avatar']) ?>" class="member-avatar"/>
          <div class="member-info">
            <span class="member-name"><?= htmlspecialchars($udata['fullname']) ?></span>
            <span class="member-handle">@<?= htmlspecialchars($uname) ?></span>
          </div>
          <span class="member-online-dot"></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>

</div>

<script>
// Auto-scroll to bottom
const area = document.getElementById('messagesArea');
if (area) area.scrollTop = area.scrollHeight;

// AJAX message send
const form = document.getElementById('msgForm');
const input = document.getElementById('messageInput');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const text = input.value.trim();
  if (!text) return;

  const formData = new FormData(form);
  input.value = '';

  try {
    await fetch('chat.php', { method: 'POST', body: formData });
    // Add message optimistically
    addMessageToUI(text, true);
  } catch(err) {
    console.error(err);
  }
});

function addMessageToUI(text, isMe) {
  const now = new Date();
  const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');

  const row = document.createElement('div');
  row.className = 'message-row me';
  row.innerHTML = `
    <div class="message-group">
      <div class="bubble">${escapeHtml(text)}</div>
      <span class="msg-time">${time}</span>
    </div>
  `;
  area.appendChild(row);
  area.scrollTop = area.scrollHeight;

  // Animate in
  row.style.opacity = '0';
  row.style.transform = 'translateY(10px)';
  setTimeout(() => {
    row.style.transition = 'all 0.3s ease';
    row.style.opacity = '1';
    row.style.transform = 'translateY(0)';
  }, 10);
}

function escapeHtml(text) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(text));
  return d.innerHTML;
}

// Enter to send
input.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    form.dispatchEvent(new Event('submit'));
  }
});

// Search filter
const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.convo-item').forEach(item => {
      const name = item.querySelector('.convo-name')?.textContent.toLowerCase() || '';
      item.style.display = name.includes(q) ? '' : 'none';
    });
  });
}

// Auto-refresh messages every 5 seconds
setInterval(() => {
  // Only reload if not focused on input
  if (document.activeElement !== input) {
    fetch(window.location.href)
      .then(r => r.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newArea = doc.getElementById('messagesArea');
        if (newArea && area) {
          const wasAtBottom = area.scrollTop + area.clientHeight >= area.scrollHeight - 20;
          area.innerHTML = newArea.innerHTML;
          if (wasAtBottom) area.scrollTop = area.scrollHeight;
        }
      }).catch(() => {});
  }
}, 5000);
</script>

</body>
</html>
