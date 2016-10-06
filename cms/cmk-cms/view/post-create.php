<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$root ='../';
	$include_path = $root . $include_path;
	$view_file = 'post-create';
}

page_access($view_file);
?>

<div class="page-title">
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['post-create']['icon'] . ' ' . $view_files['post-create']['title']
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
		<form method="post" data-page="post-create" <?php echo $view_file ?>>
			<?php
			// Save variables with empty values, to be used in the form input values
			$title = $url_key = $content= $meta_description_tmp = '';

			// If the form has been submitted
			if (isset($_POST['save_item']))
			{
				//Escape inputs and save value to variables
				$title 					= $mysqli->escape_string($_POST['title']);
				$url_key 				= $mysqli->escape_string($_POST['url_key']);
				$content				= $mysqli->escape_string($_POST['content']);
				$meta_description_tmp 	= $_POST['meta_description'];

				//If one of the fields is empty, show alert
				if( empty($_POST['title']) || empty($_POST['url_key']))
				{
					alert('warning', REQUIRED_FIELDS_EMPTY);
				}
				// If all required fields is not empty, continue
				else
				{
					// Match users with this email
					$query = "
                      SELECT 
                            post_url_key 
                      FROM
                            posts
                      WHERE
                            post_url_key = '$url_key'";
					$result = $mysqli->query($query);

					// If result returns false, use the function query_error to show debugging info
					if(!$result)
					{

						query_error($query, __LINE__, __FILE__);
					}

					// If any row(S) was found, the email is not available, so show alert
					if($result->num_rows > 0)
					{
						alert('warning', URL_NOT_AVAILABLE);
					}
					// If url_key is available, continue
					else
					{
						$user_id = intval($_SESSION['user']['id']);
						// If meta_description is empty, save NULL value to the variable meta_description, and if not escape the value from the form and add single quotes
						$meta_description = empty($_POST['meta_description']) ? 'NULL' : "'" . $mysqli->escape_string($_POST['meta_description']) . "'";
						// insert the page into the database
						$query = "
                            INSERT INTO
                                posts (post_url_key, post_title, post_content, fk_user_id, post_meta_description)
                            VALUES ('$url_key', '$title', '$content', $user_id, $meta_description)";
						prettyprint($query);
						$result = $mysqli->query($query);

						// If result returns false, use the function query_error
						if(!$result)
						{
							query_error($query, __LINE__, __FILE__);
						}
						// Get the newly created page_id
						$post_id = $mysqli->insert_id;

						// use the funtion to insert page in
						create_event('create', 'af siden <a href="index.php?page=post-edit&id' . $post_id . '" data-page="post-edit" data-params="id=' . $post_id . '">' . $title. '</a>', $view_files[$view_file]['required_access_lvl']);

						alert('success', ITEM_CREATED . '<a href="index.php?page=posts" data-page"posts">' . RETURN_TO_OVERVIEW . '</a>');
					} // CLOSES: if ($_POST['password'] != $_POST['confirm ..... else
				} // CLOSES: if(!$result->num_rows > 0) ..... else
			} // CLOSES: if (isset($_POST['save_item']))

			include $include_path . 'form-post.php'
			?>
		</form>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
