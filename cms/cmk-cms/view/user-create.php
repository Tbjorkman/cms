<?php
if ( !isset($view_files) )
{
	require '../config.php';
    $root ='../';
	$include_path = $root . $include_path;
	$view_file = 'user-create';
}

page_access($view_file);
?>

<div class="page-title">
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['user-create']['icon'] . ' ' . $view_files['user-create']['title']
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
		<form method="post" data-page="user-create">

<?php
// Save variables with empty values, to be used in the form input values
$name = $email = $role_id = $password_required_label = '';
$password_required = 'required';
$password_required_label = '';

    // If the form has been submitted
    if (isset($_POST['save_item']))
    {
        //Escape inputs and save value to variables
        $name = $mysqli->escape_string($_POST['name']);
        $email = $mysqli->escape_string($_POST['email']);

        //If one of the fields is empty, show alert
        if( empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['role']))
        {
            alert('warning', REQUIRED_FIELDS_EMPTY);
        }
        // If all required fields is not empty, continue
        else
        {
            // Match users with this email
            $query = "
                      SELECT 
                            user_id 
                      FROM
                            users
                      WHERE
                            user_email = '$email'";
            $result = $mysqli->query($query);

            // If result returns false, use the function query_error to show debugging info
            if(!$result)
            {

                query_error($query, __LINE__, __FILE__);
            }

            // If any row(S) was found, the email is not available, so show alert
            if($result->num_rows > 0)
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
					// Get the id for the selected role
					$role_id = intval($_POST['role']);
					//Get the selected role's access level from the database

					$query = "
							SELECT 
								role_access_level
							FROM
								roles
							WHERE
								role_id = $role_id";
					$result = $mysqli->query($query);

					// If result returns false, use the function query_error to show debugging info
					if(!$result)
					{

						query_error($query, __LINE__, __FILE__);
					}

					$role = $result->fetch_object();

					// If selected role's access level is higher than, or equal to the current users access level, or the current user is not super admin, overwrite the selected role_id with the default value (role_id for 'User')
					if($role->role_access_level >= $_SESSION['user']['access_level'] || $_SESSION['user']['access_level'] != 1000)
					{
						$role_id = 4;
					}


                    //Use password_hash with the algorithm from the predefined constant PASSWORD_DEFAULT, and default cost
                    $password_hash =  password_hash($_POST['password'], PASSWORD_DEFAULT);

                    $query = "
                            INSERT INTO
                                users (user_name, user_email, user_password, fk_role_id)
                            VALUES ('$name', '$email', '$password_hash', $role_id)";
                    $result = $mysqli->query($query);

                    // If result returns false, use the function query_error
                    if(!$result)
                    {
                        query_error($query, __LINE__, __FILE__);
                    }
                    $user_id = $mysqli->insert_id;

                    create_event('create', 'af brugeren <a href="index.php?page=user-create&id' . $user_id . '" data-page="user-edit" data-params="id=' . $user_id . '">' . $name . '</a>', 100);

                    alert('success', ITEM_CREATED . '<a href="index.php?side=user" data-page"users">' - RETURN_TO_OVERVIEW . '</a>');
                } // CLOSES: if ($_POST['password'] != $_POST['confirm ..... else
            } // CLOSES: if(!$result->num_rows > 0) ..... else

        } // CLOSES: if( empty($_POST['name']) || empty..... else
    } // CLOSES: if (isset($_POST['save_item']))

 include $include_path . 'form-user.php' ?>
		</form>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
