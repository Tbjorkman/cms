<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$root ='../';
	$include_path = $root . $include_path;
	$view_file = 'page-create';
}

page_access($view_file);
?>

<div class="page-title">
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['page-create']['icon'] . ' ' . $view_files['page-create']['title']
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
			$title = $url_key = $meta_description_tmp = '';
			$meta_robots = 'index, follow';

			// If the form has been submitted
			if (isset($_POST['save_item']))
			{
				//Escape inputs and save value to variables
				$title 					= $mysqli->escape_string($_POST['title']);
				$url_key 				= $mysqli->escape_string($_POST['url_key']);
				$meta_robots 			= $mysqli->escape_string($_POST['meta_robots']);
				$meta_description_tmp 	= $_POST['meta_description'];

				//If one of the fields is empty, show alert
				if( empty($_POST['title']) || empty($_POST['url_key']) || empty($_POST['meta_robots']))
				{
					alert('warning', REQUIRED_FIELDS_EMPTY);
				}
				// If all required fields is not empty, continue
				else
				{
					// Match users with this email
					$query = "
                      SELECT 
                            page_url_key 
                      FROM
                            pages
                      WHERE
                            page_url_key = '$url_key'";
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
						// If meta_description is empty, save NULL value to the variable meta_description, and if not escape the value from the form and add single quotes
						$meta_description = empty($_POST['meta_description']) ? 'NULL' : "'" . $mysqli->escape_string($_POST['meta_description']) . "'";
						// insert the page into the database
							$query = "
                            INSERT INTO
                                pages (page_url_key, page_title, page_meta_robots, page_meta_description)
                            VALUES ('$url_key', '$title', '$meta_robots', $meta_description)";
							$result = $mysqli->query($query);

							// If result returns false, use the function query_error
							if(!$result)
							{
								query_error($query, __LINE__, __FILE__);
							}
							// Get the newly created page_id
							$page_id = $mysqli->insert_id;

						// use the funtion to insert page in
							create_event('create', 'af siden <a href="index.php?page=page-edit&id' . $page_id . '" data-page="page-edit" data-params="id=' . $page_id . '">' . $title. '</a>', $view_files[$view_file]['required_access_lvl']);

							alert('success', ITEM_CREATED . '<a href="index.php?page=pages" data-page"pages">' . RETURN_TO_OVERVIEW . '</a>');
						} // CLOSES: if ($_POST['password'] != $_POST['confirm ..... else
					} // CLOSES: if(!$result->num_rows > 0) ..... else
			} // CLOSES: if (isset($_POST['save_item']))
			 include $include_path . 'form-page.php'
?>
		</form>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
