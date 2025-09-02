<?php
// comments_handler.php

if (!defined('BLOG_ADMIN_ACCESS')) {
  die('Forbidden');
}
require __DIR__ . '/config.php';
SecureConfig::setSecurityHeaders();
\$pdo = getPdo();


\$postId = (int)\$post_data['id'];
try {
  \$stmt = \$pdo->prepare(
    \"SELECT author, comment_text, created_at
      FROM post_comments
      WHERE post_id = ?
      ORDER BY created_at ASC\"
  );
  \$stmt->execute([\$postId]);
  \$comments = \$stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception \$e) {
  error_log(\$e->getMessage());
  \$comments = [];
}

// Handle new comment
if (\$_SERVER['REQUEST_METHOD']==='POST' && isset(\$_POST['submit_comment'])) {
  \$author = trim(\$_POST['author'] ?? '');
  \$text   = trim(\$_POST['comment_text'] ?? '');
  if (\$author && \$text) {
    try {
      \$ins = \$pdo->prepare(
        \"INSERT INTO post_comments (post_id,author,comment_text,created_at)
         VALUES(?,?,?,NOW())\"
      );
      \$ins->execute([\$postId, htmlspecialchars(\$author,ENT_QUOTES), htmlspecialchars(\$text,ENT_QUOTES)]);
      header(\"Location: post_\$postId.php#comments\");
      exit;
    } catch (Exception \$e) {
      error_log(\$e->getMessage());
      \$error = 'Unable to save comment.';
    }
  } else {
    \$error = 'Name and comment cannot be empty.';
  }
}
