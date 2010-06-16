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
		$this->add_template( 'block.popular_posts', dirname(__FILE__) . '/block.popular_posts.php' );

	}

	/**
	 * Add to the list of possible block types.
	 *
 	 **/
	public function filter_block_list( $block_list )
	{
		$block_list[ 'popular_posts' ] = _t( 'Popular Posts', 'popular_posts' );
		return $block_list;
	}

	/**
	 * Create a configuration form for this plugin
	 *
	 **/
	public function configure()
	{
		$form = new FormUI( 'popular_posts' );
		$form->append( 'checkbox', 'loggedintoo', 'popular_posts__loggedintoo', _t( 'Track views of logged-in users too', 'popular_posts' ) );
		$form->append( 'submit', 'save', 'Save' );
		$form->out();
	}

	/**
	 * Create a configuration form for the block
	 **/
	public function action_block_form_popular_posts( $form, $block )
	{
		$content = $form->append( 'text', 'quantity', $block, _t( 'Posts to show:', 'popular_posts' ) );
		$form->append( 'submit', 'save', _t( 'Save', 'popular_posts' ) );
	}

	/**
	 * Log the entry page view, when appropriate.
	 *
	 */
	public function action_add_template_vars( $theme, $handler_vars )
	{
		// If there is only one post
		if ( $theme->post instanceof Post && count( $theme->posts ) == 1 ) {

			// Only track users that aren't logged in, unless specifically overridden
			if ( !User::identify()->loggedin || Options::get( 'popular_posts__loggedintoo' ) ) {
				$set = Session::get_set( 'popular_posts', false );
				$post = $theme->post;
				if ( !in_array( $post->id, $set ) ){
					$views = $post->info->views;
					if ( $views == null ) {
						$views = 0;
					}
					$views += 1;
					$post->info->views = $views;
					$post->info->commit();

					Session::add_to_set( 'popular_posts', $post->id );
				}
			}

		}
	}

	/**
	 * Display a template with the popular entries
	 */
	public function theme_popular_posts($theme, $limit = 5)
	{
		$theme->popular_posts = Posts::get( array(
			'content_type' => 'entry',
			'has:info' => 'views',
			'orderby' => 'ABS(info_views_value) DESC', // As the postinfo value column is TEXT, ABS() forces the sorting to be numeric
			'limit' => $limit
		) );
		return $theme->display( 'popular_posts' );
	}

	/**
	 * Populate a block with the popular entries
	 **/
	public function action_block_content_popular_posts( $block, $theme )
	{
		if ( ! $limit = $block->quantity ) {
			$limit = 5;
		};

		$block->popular_posts = Posts::get( array(
			'content_type' => 'entry',
			'has:info' => 'views',
			'orderby' => 'ABS(info_views_value) DESC', // As the postinfo value column is TEXT, ABS() forces the sorting to be numeric
			'limit' => $limit
		) );
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
