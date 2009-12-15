<h2>Popular Posts</h2>
<ul>
<?php
	foreach ( $popular_posts as $post ) {
		echo "<li><a href='{$post->permalink}'>{$post->title}</a></li>\n";
	}
?>
</ul>
