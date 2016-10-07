<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$root ='../';
	$include_path = $root . $include_path;
	$view_file = 'comment-create';
}

page_access($view_file);
?>

<div class="page-title">
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['comment-create']['icon'] . ' ' . $view_files['comment-create']['title']
		?>
	</span>
</div>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<div class="title"><?php echo CREATE_ITEM ?></div>
		</div>
	</div>

	<div class="card-body">
		<form method="post" data-page="<?php echo $view_file ?>">
<?php
			// Save variables with empty values, to be used in the form input values
			$content= '';

			// If the form has been submitted
			if (isset($_POST['save_item']))
			{
				//Escape inputs and save value to variables
				$content				= $mysqli->escape_string($_POST['content']);
				//If one of the fields is empty, show alert
				if( empty($_POST['content']))
				{
					alert('warning', REQUIRED_FIELDS_EMPTY);
				}
				// If all required fields is not empty, continue
				else
				{
					$user_id = intval($_SESSION['user']['id']);
					$post_id = intval($_GET['post-id']);

					// insert the page into the database
					$query = "
						INSERT INTO
							comments (comment_content, fk_user_id, fk_post_id)
						VALUES ('$content', $user_id, $post_id)";
					prettyprint($query);
					$result = $mysqli->query($query);

					// If result returns false, use the function query_error
					if(!$result)
					{
						query_error($query, __LINE__, __FILE__);
					}
					// Get the newly created page_id
					$comment_id = $mysqli->insert_id;

					// use the function to insert page in
					create_event('create', 'af siden <a href="index.php?page=comments-edit&id' . $comment_id . '" data-page="' . $view_file . '-edit" data-params="id=' . $comment_id . '"> Comment created </a>', $view_files[$view_file]['required_access_lvl']);

					alert('success', ITEM_CREATED . '<a href="index.php?page=comments&post-id=' .  $post_id . '" data-page"' . $view_file . '">' . RETURN_TO_OVERVIEW . '</a>');
				} // CLOSES: if( empty($_POST['content']))... else
			} // CLOSES: if (isset($_POST['save_item']))

			include $include_path . 'form-comment.php'
?>
		</form>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
