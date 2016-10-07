<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$root ='../';
	$include_path = $root . $include_path;
	$view_file = 'comment-edit';
}

page_access($view_file);
?>

<div class="page-title">
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['comment-edit']['icon'] . ' ' . $view_files['comment-edit']['title']
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
							comment_content
						FROM  
							comments
						WHERE 
							comment_id = $id";
				$result = $mysqli->query($query);

				// If result returns false, use the function query_error to show debugging info
				if(!$result)
				{
					query_error($query, __LINE__, __FILE__);
				}

				//Return the information from the Database as an object
				$row 		= $result->fetch_object();

				// Save variables with empty values, to be used in the form input values
				$content 	= $row->comment_content;
			}
			// If the form has been submitted
			if (isset($_POST['save_item']))
			{
				//Escape inputs and save value to variables
				$content 				= $mysqli->escape_string($_POST['content']);

				//If one of the fields is empty, show alert
				if( empty($_POST['content']))
				{
					alert('warning', REQUIRED_FIELDS_EMPTY);
				}
				// If all required fields is not empty, continue
				else
				{
					// insert the page into the database
					$query = "
						UPDATE
							comments 
						SET
							comment_content = '$content'
						WHERE 
							comment_id = $id
						";
					$result = $mysqli->query($query);

					// If result returns false, use the function query_error
					if(!$result)
					{
						query_error($query, __LINE__, __FILE__);
					}
					// Get the newly created post_id
					$comment_id = $mysqli->insert_id;

					// use the function to insert event in log
					create_event('update', 'af comment <a href="index.php?page=post-edit&id' . $id . '" data-page=" . $view_file . " data-params="id=' . $id . '"> Comment created</a>', $view_files[$view_file]['required_access_lvl']);

					alert('success', ITEM_UPDATED . '<a href="index.php?page=posts" data-page"posts">' - RETURN_TO_OVERVIEW . '</a>');
				} // CLOSES: if( empty($_POST['content'])) ..... else
			} // CLOSES: if (isset($_POST['save_item']))

			include $include_path . 'form-comment.php' ?>
		</form>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
