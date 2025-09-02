<?php
/**
 * post_template.php — Template for all post_<id>.php files
 */
define('BLOG_ADMIN_ACCESS', true);
require __DIR__ . '/config.php';
SecureConfig::setSecurityHeaders();


$post_data = [
    'id'         => __POST_ID__,
    'title'      => '__TITLE__',
    'created_at' => '__DATE__'
];
$content_html = '__CONTENT__';
$image_url    = '__IMAGE_URL__';


$pdo = getPdo();
$postId = $post_data['id'];
$comments = [];
$error = '';

try {
    $stmt = $pdo->prepare(
        "SELECT author, comment_text, created_at
         FROM post_comments
         WHERE post_id = ?
         ORDER BY created_at ASC"
    );
    $stmt->execute([$postId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $author = trim($_POST['author'] ?? '');
    $text   = trim($_POST['comment_text'] ?? '');
    
    if ($author !== '' && $text !== '') {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO post_comments (post_id, author, comment_text, created_at)
                 VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([
                $postId,
                htmlspecialchars($author, ENT_QUOTES),
                htmlspecialchars($text, ENT_QUOTES)
            ]);
            header("Location: post_{$postId}.php#comments");
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Unable to save comment. Please try again.';
        }
    } else {
        $error = 'Name and comment cannot be empty.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo htmlspecialchars($post_data['title']); ?> - SM Rafi Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header__content">
                <div class="header__logo">
                    <a href="../#hero"><img src="https://www.smrafi.com/wp-content/uploads/2025/08/10.png" alt="SM Rafi Logo" class="logo-img"></a>
                </div>
                <nav class="header__nav">
                    <ul class="nav-list">
                        <li><a href="../#portfolio" class="nav-link">Portfolio</a></li>
                        <li><a href="../#about" class="nav-link">About Rafi</a></li>
                        <li><a href="../#contact" class="nav-link">Contact</a></li>
                         <li><a href="index.php" class="nav-link">← Back to Blogs</a></li>
                    </ul>
                </nav>
                <div class="header__socials">
                    <a href="https://www.linkedin.com/in/s-mohammad-rafi-61b5b2368" target="_blank" class="social-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/smrafi0699?igsh=MTk1bDFyZTloajl1Yg==" target="_blank" class="social-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/share/153KAfX5Hp/" target="_blank" class="social-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://www.youtube.com/@rafi0699t" target="_blank" class="social-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                    <a href="https://github.com/rafi-699" target="_blank" class="social-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>
                       

    <div style="height:80px;"></div>
    
    <main class="main-content">
        <div class="container">
            <h1 class="post-title"><?php echo htmlspecialchars($post_data['title']); ?></h1>
            <div class="post-meta"><span class="post-date"><?php echo $post_data['created_at']; ?></span></div>
            
            <?php if ($image_url): ?>
                <div class="post-image">
                    <img src="<?php echo htmlspecialchars($image_url, ENT_QUOTES); ?>"
                         alt="<?php echo htmlspecialchars($post_data['title'], ENT_QUOTES); ?>"
                         loading="lazy">
                </div>
            <?php endif; ?>
            
            <article class="post-content">
                <?php echo $content_html; ?>
            </article>
            
   
            <section id="comments" class="comments-section">
                <h2>Comments</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($comments)): ?>
                    <ul class="comments-list">
                        <?php foreach ($comments as $c): ?>
                            <li class="comment-item">
                                <div class="comment-author"><?php echo htmlspecialchars($c['author']); ?></div>
                                <div class="comment-date"><?php echo date('F j, Y \a\t g:i A', strtotime($c['created_at'])); ?></div>
                                <div class="comment-text"><?php echo nl2br(htmlspecialchars($c['comment_text'])); ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php endif; ?>
                
                <form method="POST" class="form-group">
                    <h3>Leave a Comment</h3>
                    <div class="form-group">
                        <label for="author">Name <span style="color: red;">*</span></label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="comment_text">Comment <span style="color: red;">*</span></label>
                        <textarea id="comment_text" name="comment_text" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="submit_comment">Post Comment</button>
                </form>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__logo">
                    <a href="../#hero"><img src="https://www.smrafi.com/wp-content/uploads/2025/08/10.png" alt="SM Rafi Logo" class="logo-img"></a>
                </div>
                <div class="footer__nav">
                    <a href="../#hero">Home</a>
                    <a href="../#portfolio">Portfolio</a>
                    <a href="../#projects">Projects</a>
                    <a href="../#about">About</a>
                    <a href="../#contact">Contact</a>
                    <a href="index.php" class="nav-link">← Back to Blog</a>
                </div>
                <div class="footer__socials">
                    <a href="https://www.linkedin.com/in/s-mohammad-rafi-61b5b2368" target="_blank">LinkedIn</a>
                    <a href="https://www.instagram.com/smrafi0699?igsh=MTk1bDFyZTloajl1Yg==" target="_blank">Instagram</a>
                    <a href="https://www.facebook.com/share/153KAfX5Hp/" target="_blank">Facebook</a>
                    <a href="https://x.com/SMRafiAI" target="_blank">X</a>
                    <a href="https://www.youtube.com/@rafi0699t" target="_blank">YouTube</a>
                    <a href="https://github.com/rafi-699" target="_blank">GitHub</a>
                </div>
            </div>
            <div class="footer__copyright">
                <p>&copy; 2025 SM Rafi. All rights reserved.</p>
            </div>
        </div>
    </footer>


</body>
</html>
