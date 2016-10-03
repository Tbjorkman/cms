<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$include_path = '../' . $include_path;

	//security('../');
}
?>

<div class="page-title" xmlns="http://www.w3.org/1999/html">
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['user-edit']['icon'] . ' ' . $view_files['user-edit']['title']
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
		<form method="post" data-page="user-edit">
			<?php
			if(!isset($_GET['id']) || empty($_GET['id']))
			{
				alert('warning',  NO_USER_SELECTED . '<a href="index.php?page=users" data-page="users">'. RETURN_TO_OVERVIEW .'</a>');
			}
			// Get the seleceted users id from the URL param
			$id = intval($_GET['id']);
			// Get the users from the Database
			$query = "
				SELECT 
					user_name , user_email, fk_role_id 
				FROM  
					users 
				WHERE 
					user_id = $id";
			$result = $mysqli->query($query);

			// If result returns false, use the function query_error to show debugging info
			if(!$result)
			{
				query_error($query, __LINE__, __FILE__);
			}

			//Return the information from the Database as an object
			$row = $result->fetch_object();

			// Save the values from the Database to the variables used for values in the forms input elements
			$name 	= $row->user_name;
			$email	= $row->user_email;
			$role	= $row->fk_role_id;
			$password_required = '';
			$password_required_label = '<small class="text-muted">(' . OPTIONAL . ')</small>';

			// If the form has been submitted
			if (isset($_POST['save_item']))
			{
				//Escape inputs and save value to variables
				$name = $mysqli->escape_string($_POST['name']);
				$email = $mysqli->escape_string($_POST['email']);
				$role = intval($_POST['role']);

				//If one of the fields is empty, show alert
				if( empty($_POST['name']) || empty($_POST['email']) || empty($_POST['role']))
				{
					alert('warning', REQUIRED_FIELDS_EMPTY);
				}
				// If all required fields is not empty, continue
				else
				{
					// Match users with this email
					$query = "SELECT 
									user_id 
							  FROM
									users
							  WHERE
									user_email = '$email'
							  AND 
							  		user_id = $id";

					$result = $mysqli->query($query);

					// If result returns false, use the function query_error to show debugging info
					if(!$result)
					{

						query_error($query, __LINE__, __FILE__);
					}

					// If any row(S) was found, the email is not available, so show alert
					if(!$result->num_rows > 0)
					{
						alert('warning', EMAIL_NOT_AVAILABLE);
					}
					// If email is available, continue
					else
					{
						// If the typed password isn't the same, show alert
						if ($_POST['password'] != $_POST['confirm_password'])
						{
							alert('warning', PASSWORD_MISMATCH);
						}
						//If the password matched, continue
						else
						{
							$password_sql = '';
							//If password field is not empty, generate a new password and save the sql-part of the update and 							default cost
							if(!empty($_POST['password']))
							{
							//Use password_hash with the algorithm from the predefined constant PASSWORD_DEFAULT, and default cost
								$password_hash 	= password_hash($_POST['password'], PASSWORD_DEFAULT);
								$password_sql 	= ", user_password = '$password_hash'";
							}

							$query = "
									UPDATE 
										users 
									SET	
										user_name = '$name', user_email = '$email', fk_role_id = $role $password_sql
									WHERE 
										user_id = $id";
							$result = $mysqli->query($query);

							// If result returns false, use the function query_error
							if(!$result)
							{
								query_error($query, __LINE__, __FILE__);
							}

							$user_id = $mysqli->insert_id;

							create_event('update', 'af brugeren <a href="index.php?page=user-edit&id' . $user_id . '" data-page="user-edit" data-params="id=' . $user_id . '">' . $name . '</a>', 100);

							alert('success', ITEM_UPDATED . '<a href="index.php?side=user" data-page"users">' - RETURN_TO_OVERVIEW . '</a>');
						} // CLOSES: if ($_POST['password'] != $_POST['confirm ..... else
					} // CLOSES: if(!$result->num_rows > 0) ..... else

				} // CLOSES: if( empty($_POST['name']) || empty..... else
			} // CLOSES: if (isset($_POST['save_item']))

			 include $include_path . 'form-user.php';
			?>
		</form>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
