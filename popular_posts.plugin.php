<?php

class PopularPosts extends Plugin
{
	/**
	 * Add the necessary template
	 *
	 **/
	public function action_init()
	{
		$this->add_template( 'popular_posts', dirname(__FILE__) . '/popular_posts.php' );
	}

	/**
	 * Add a configuration action for this plugin
	 *
	 **/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * Create a configuration form for this plugin
	 *
	 **/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure'):
					$form = new FormUI(strtolower(get_class($this)));
					$form->append('checkbox', 'loggedintoo', 'popular_posts__loggedintoo', _t('Track views of logged-in users too'));
					$form->append('submit', 'save', 'Save');
					$form->out();
				break;
			}
		}
	}

	/**
	 * Log the entry page view, when appropriate.
	 *
	 */
	public function action_add_template_vars( $theme, $handler_vars )
	{
		// If there is only one post
		if ( $theme->post instanceof Post && count($theme->posts) == 1 ) {

			// Only track users that aren't logged in, unless specifically overridden
			if ( !User::identify()->loggedin || Options::get('popular_posts__loggedintoo') ) {
				$set = Session::get_set('popular_posts', true);
				$post = $theme->post;
				if ( !isset($set['viewed'][$post->id]) ){
					$views = $post->info->views;
					if ( $views == null ) {
						$views = 0;
					}
					$views += 1;
					$post->info->views = $views;
					$post->info->commit();
				}
				Session::add_to_set( 'popular_posts', array( $post->id => true), 'viewed' );
			}

		}
	}

	/**
	 * Display a template with the popular entries
	 */
	public function theme_popular_posts($theme, $limit = 5)
	{
		$theme->popular_posts = posts::get(array(
			'content_type' => 'entry',
			'has:info' => 'views',
			'orderby' => 'info_views_value DESC',
			'limit' => $limit
		));
		return $theme->display( 'popular_posts' );
	}

	public function help()
	{
		return <<< END_HELP
<p>To output a list of popular posts, insert this code where you want them to appear:</p>
<blockquote><code>&lt;?php \$theme-&gt;popular_posts(); ?&gt;</code></blockquote>
<p>You can also pass the number of popular posts you want to retrieve:</p>
<blockquote><code>&lt;?php \$theme-&gt;popular_posts(7); ?&gt;</code></blockquote>
<p>This will retrieve the seven most popular posts. The default is five.</p>
<p>The default theme inserts an HTML unordered list of links to the popular
pages.  If you want to alter this, you should copy the
<tt>popular_posts.php</tt> template included with this plugin to your current
theme directory and make changes to it there.</p>
END_HELP;
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'PopularPosts', 'a52dad06-1b46-4832-93d7-2f9a7d783f54', $this->info->version );
	}

}

?>
