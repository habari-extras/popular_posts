<h3>Popular Posts</h3>
<ul>
  <?php $posts = $content->popular_posts; foreach( $posts as $post): ?>
    <li>
      <a href="<?php echo $post->permalink; ?>">
        <?php echo $post->title; ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>