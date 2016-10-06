<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$root ='../';
	$include_path = $root . $include_path;
	$view_file = 'post-edit';
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
			<div class="title"><?php echo EDIT_ITEM ?></div>
		</div>
	</div>

	<div class="card-body">
		<form method="post" data-page="<?php echo $view_file ?>" data-params="<?php echo $_GET['id'] ?>">
			<?php
			if(!isset($_GET['id']) || empty($_GET['id']))
			{
				alert('danger', NO_ITEM_SELECTED );
			}
			else
			{
				// Get the id from the session
				$id = intval($_GET['id']);

				// Get the page-info from the Database
				$query = "
				SELECT 
					post_url_key, post_title, post_content, post_meta_description
				FROM  
					posts
				WHERE 
					post_id = $id";
				$result = $mysqli->query($query);

				// If result returns false, use the function query_error to show debugging info
				if(!$result)
				{
					query_error($query, __LINE__, __FILE__);
				}

				//Return the information from the Database as an object
				$row = $result->fetch_object();

				// Save variables with empty values, to be used in the form input values
				$title 					= $row->post_title;
				$url_key 				= $row->post_url_key;
				$content 				= $row->post_content;
				$meta_description_tmp 	= $row->post_meta_description;
			}
			// If the form has been submitted
			if (isset($_POST['save_item']))
			{
				//Escape inputs and save value to variables
				$title 					= $mysqli->escape_string($_POST['title']);
				$url_key 				= $mysqli->escape_string($_POST['url_key']);
				$content 				= $mysqli->escape_string($_POST['content']);
				$meta_description_tmp 	= $_POST['meta_description'];

				//If one of the fields is empty, show alert
				if( empty($_POST['title']))
				{
					alert('warning', REQUIRED_FIELDS_EMPTY);
				}
				// If all required fields is not empty, continue
				else
				{
					// Match users with this email
					$query = "
                      SELECT 
                            post_id 
                      FROM
                            posts
                      WHERE
                            post_url_key = '$url_key'
                      AND 
                      		post_id = $id";
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
						// If meta_description is empty, save NULL value to the variable meta_description, and if not escapre the value from the form and add single quotes
						 $meta_description = empty($_POST['meta_description']) ? 'NULL' : "'" . $mysqli->escape_string($_POST['meta_description']) . "'";
						// insert the page into the database
						$query = "
                            UPDATE
                                posts 
                            SET
                            	post_url_key = '$url_key', post_title = '$title', 
                            	post_content = '$content', post_meta_description = $meta_description
                            WHERE 
                            	post_id = $id
                            ";
						$result = $mysqli->query($query);

						// If result returns false, use the function query_error
						if(!$result)
						{
							query_error($query, __LINE__, __FILE__);
						}
						// Get the newly created post_id
						$post_id = $mysqli->insert_id;

						// use the function to insert event in log
						create_event('update', 'af siden <a href="index.php?page=post-edit&id' . $id . '" data-page=" . $view_file . " data-params="id=' . $id . '">' . $title. '</a>', $view_files[$view_file]['required_access_lvl']);

						alert('success', ITEM_UPDATED . '<a href="index.php?page=posts" data-page"posts">' - RETURN_TO_OVERVIEW . '</a>');
					} // CLOSES: if ($_POST['password'] != $_POST['confirm ..... else
				} // CLOSES: if(!$result->num_rows > 0) ..... else
			} // CLOSES: if (isset($_POST['save_item']))

			include $include_path . 'form-post.php' ?>
		</form>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
