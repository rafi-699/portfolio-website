<?php
define('BLOG_ADMIN_ACCESS', true);


if (
    !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== '<username>' ||
    $_SERVER['PHP_AUTH_PW'] !== '<password>'
) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Authentication Required');
}

session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
]);

require 'config.php';
SecureConfig::setSecurityHeaders();


function csrfToken(): string {
    return generate_csrf_token();
}

function checkCsrf(string $token) {
    if (!verify_csrf_token($token)) {
        throw new Exception('Invalid security token. Please refresh the page.');
    }
}


function formatContent(string $text): string {
    $text = str_replace(["\r\n","\r"], "\n", trim($text));
    $paras = preg_split("/\n{2,}/", $text);
    foreach ($paras as &$p) {
        $p = htmlspecialchars($p, ENT_QUOTES, 'UTF-8');
        $p = nl2br($p, false);
    }
    unset($p);
    return '<p>' . implode("</p>\n<p>", $paras) . '</p>';
}


function createPostFile(int $id, string $title, string $htmlContent, string $imageUrl): void {
    $date = date('F j, Y');
    $template = file_get_contents(__DIR__.'/post_template.php');
    $replacements = [
        '__POST_ID__'   => $id,
        '__TITLE__'     => str_replace("'", "\\'", $title),
        '__DATE__'      => $date,
        '__CONTENT__'   => str_replace("'", "\\'", $htmlContent),
        '__IMAGE_URL__' => str_replace("'", "\\'", $imageUrl),
    ];
    $fileContent = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $template
    );
    file_put_contents(__DIR__."/post_{$id}.php", $fileContent, LOCK_EX);
}


$isLoggedIn = false;
if (!empty($_SERVER['PHP_AUTH_USER'])) {
    $isLoggedIn = true;
} elseif (!empty($_SESSION['admin_logged_in'])) {
    $isLoggedIn = true;
}

if (!$isLoggedIn && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['php_login'])) {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if ($u==='admin' && $p==='Zerozero7@333') {
        $_SESSION['admin_logged_in']=true;
        $isLoggedIn=true;
        $message='Login successful!';
    } else {
        $error='Invalid credentials.';
    }
}


if ($isLoggedIn && isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}


if ($isLoggedIn && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create_post'])) {
    try {
        checkCsrf($_POST['csrf_token']??'');
        $title = trim($_POST['title'] ?? '');
        $raw   = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $img   = trim($_POST['image_url'] ?? '');
        
        if ($title==='' || trim($raw)==='') {
            throw new Exception('Title and content are required.');
        }
        
        $formatted = formatContent($raw);
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $title)) ?: 'post-'.time();
        $pdo = getPdo();
        
   
        $stmt=$pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug=?");
        $orig=$slug; $c=1;
        while(true){
            $stmt->execute([$slug]);
            if($stmt->fetchColumn()>0) $slug="{$orig}-".($c++);
            else break;
        }
        
   
        $stmt=$pdo->prepare(
            "INSERT INTO blog_posts(title,excerpt,content,image_url,slug,created_at)
             VALUES(?,?,?,?,?,NOW())"
        );
        $stmt->execute([$title,$excerpt,$formatted,$img,$slug]);
        $id=$pdo->lastInsertId();
        
        createPostFile((int)$id,$title,$formatted,$img);
        $message="Post created! <a href='post_{$id}.php' target='_blank'>View</a>";
    } catch(Exception $e) {
        $error=$e->getMessage();
    }
}


if ($isLoggedIn && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_post'])) {
    try {
        checkCsrf($_POST['csrf_token']??'');
        $id=intval($_POST['post_id']);
        if($id>0){
            $pdo=getPdo();
            $stmt=$pdo->prepare("SELECT title FROM blog_posts WHERE id=?");
            $stmt->execute([$id]);
            if($t=$stmt->fetchColumn()){
                $pdo->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
                @unlink(__DIR__."/post_{$id}.php");
                $message="Deleted post \"{$t}\".";
            }
        }
    }catch(Exception $e){
        $error=$e->getMessage();
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Blog Admin - SM Rafi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        textarea { font-family: monospace; resize: vertical; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #005a87; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #f5c6cb; }
        .form-section { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .post-list { list-style: none; padding: 0; }
        .post-item { padding: 15px; margin: 10px 0; background: #f9f9f9; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .post-actions { display: flex; gap: 10px; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
<?php if(!$isLoggedIn): ?>
    <div class="container">
        <h2>🔐 Admin Login</h2>
        <?php if(!empty($error)): ?><div class="error">❌ <?=htmlspecialchars($error)?></div><?php endif;?>
        <form method="POST">
            <label>Username<br><input name="username" required></label>
            <label>Password<br><input type="password" name="password" required></label>
            <button name="php_login">🔓 Login</button>
        </form>
    </div>
<?php else: ?>
    <div class="container">
        <h1>📝 Blog Admin Dashboard</h1>
        <?php if(!empty($message)): ?><div class="success">✅ <?=$message?></div><?php endif;?>
        <?php if(!empty($error)): ?><div class="error">❌ <?=$error?></div><?php endif;?>
        
        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?=csrfToken()?>">
                <h2>✏️ Create New Post</h2>
                <label>📝 Title *<br><input type="text" name="title" required></label>
                <label>📄 Excerpt<br><textarea name="excerpt" rows="2" placeholder="Brief summary (optional)"></textarea></label>
                <label>✍️ Content *<br><textarea name="content" rows="10" required placeholder="Write your post content here..."></textarea></label>
                <small style="color: #666;">💡 <strong>Formatting:</strong> Single Enter = line break • Double Enter = paragraph • HTML tags supported</small><br><br>
                <label>🖼️ Image URL<br><input type="url" name="image_url" placeholder="https://example.com/image.jpg (optional)"></label>
                <button name="create_post">🚀 Publish Post</button>
            </form>
            
            
<?php

$uploadedImageUrl = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    try {

        checkCsrf($_POST['csrf_token'] ?? '');

 
        $imagesDirRel = '/images';          // URL path to use in pages
        $imagesDirAbs = __DIR__ . $imagesDirRel;  // filesystem path

        if (!is_dir($imagesDirAbs)) {
            if (!@mkdir($imagesDirAbs, 0755, true) && !is_dir($imagesDirAbs)) {
                throw new Exception('Failed to create images directory.');
            }
        }

        if (empty($_FILES['image_file']) || !is_array($_FILES['image_file'])) {
            throw new Exception('No file received.');
        }

        $err = $_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($err === UPLOAD_ERR_NO_FILE) {
            throw new Exception('Please choose a file.');
        }
        if ($err !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed. Please try again.');
        }

 
        $maxBytes = 5 * 1024 * 1024;
        $size = (int)($_FILES['image_file']['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new Exception('Image too large. Max 5 MB.');
        }

        $tmp = $_FILES['image_file']['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            throw new Exception('Upload validation failed.');
        }

  
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmp) ?: '';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        if (!isset($allowed[$mime])) {
            throw new Exception('Invalid image type. Only JPEG, PNG, WebP, or GIF.');
        }

     
        $ext  = $allowed[$mime];
        $name = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
        $destAbs = $imagesDirAbs . '/' . $name;
        $destRel = $imagesDirRel . '/' . $name;

        if (!move_uploaded_file($tmp, $destAbs)) {
            throw new Exception('Failed to save uploaded image.');
        }

      
        $uploadedImageUrl = $destRel;

    } catch (Exception $e) {
        $upload_error = $e->getMessage();
    }
}
?>


<div style="border:1px solid #ddd;border-radius:6px;padding:12px;margin:12px 0;">
  <h3 style="margin:0 0 8px;">Upload Post Image</h3>

  <form method="post" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" required>
    <button type="submit" name="upload_image" value="1">Upload</button>
  </form>

  <?php if (!empty($upload_error)): ?>
    <div style="color:#b00020;margin-top:8px;"><?= htmlspecialchars($upload_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if (!empty($uploadedImageUrl)): ?>
    <div style="margin-top:8px;">
      <div>Uploaded URL:</div>
      <code style="display:block;padding:6px;background:#f7f7f7;border:1px solid #eee;border-radius:4px;word-break:break-all;">
        <?= htmlspecialchars($uploadedImageUrl, ENT_QUOTES, 'UTF-8') ?>
      </code>
      
      <script>
        (function(){
          var input = document.getElementById('image_url');
          if (input) input.value = <?= json_encode($uploadedImageUrl) ?>;
        })();
      </script>
    </div>
  <?php endif; ?>
</div>


        </div>
        
        <h2>📚 Existing Posts</h2>
        <?php
            $pdo=getPdo();
            $rows=$pdo->query("SELECT id,title,created_at FROM blog_posts ORDER BY created_at DESC")->fetchAll();
            if($rows):
        ?>
            <ul class="post-list">
                <?php foreach($rows as $r): ?>
                    <li class="post-item">
                        <div>
                            <strong><?=htmlspecialchars($r['title'])?></strong><br>
                            <small>📅 <?=date('M j, Y @ g:i A',strtotime($r['created_at']))?> | ID: <?=$r['id']?></small>
                        </div>
                        <div class="post-actions">
                            <a href="post_<?=$r['id']?>.php" target="_blank" class="btn" style="background: #28a745; color: white; text-decoration: none; padding: 8px 12px; border-radius: 4px;">👁️ View</a>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?=csrfToken()?>">
                                <input type="hidden" name="delete_post" value="1">
                                <input type="hidden" name="post_id" value="<?=$r['id']?>">
                                <button onclick="return confirm('🗑️ Delete: <?=addslashes($r['title'])?>\n\nThis action cannot be undone!')" class="btn-danger">🗑️ Delete</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach;?>
            </ul>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <div style="font-size: 48px; margin-bottom: 20px;">📝</div>
                <h3>No posts yet</h3>
                <p>Create your first blog post above!</p>
            </div>
        <?php endif;?>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <a href="admin.php?logout=1" style="color: #666;">🚪 Logout</a>
        </div>
    </div>
<?php endif;?>
</body>
</html>
