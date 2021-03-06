<?php

//shows alerts in color and makes the message more clear
function alert($type, $message)
{
	?>
		<div class="alert alert-<?php echo $type ?> alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-lable="Close">
				<span aria-hidden="true">x</span>
			</button><?php echo $message; ?>
		</div>
	<?php
}

function create_event($type, $description, $access_level)
{
    global $mysqli;

    switch ($type)
    {
        case 'create':
            $event_type_id = 1;
            break;
        case 'update':
            $event_type_id = 2;
            break;
        case 'delete':
            $event_type_id = 3;
            break;
        default:
            $event_type_id = 4;
    }
    $description = $mysqli->real_escape_string($description);
    $access_level = intval($access_level);
    $user_id = intval($_SESSION['user']['id']);

    $query = "
            INSERT INTO 
                    events(event_description, event_access_level_required, fk_user_id, fk_event_type_id)
            VALUES 
                    ('$description', '$access_level', '$user_id', '$event_type_id')";
    $result = $mysqli->query($query);

    // If result returns false, use the function query_error to show debugging info
    if(!$result)
    {

        query_error($query, __LINE__, __FILE__);
    }
}

//Shows connection errors and precisely where they are
function connect_error($error_no, $error, $line_number, $file_name)
{
	if(DEVELOPER_STATUS)
	{
		die('<p>Forbindelsesfejl ('. $error_no .'): '. $error .'</p><p>Linje: ' . $line_number . '</p><p>Fil: '. $file_name .' </p>');
	}
	else
	{
		die(CONNECT_ERROR);
	}
}

//Shows the errors in an SQL statement more clearly so that it's easier to debug
function query_error($query, $line_number, $file_name)
{
	if(DEVELOPER_STATUS)
	{
		global $mysqli;

		$message =
		'<strong>' . $mysqli->error . '</strong><br><strong>
		Linje:'.$line_number.'</strong><br><strong>
		Fil:'.$file_name.'</strong><pre class="prettyprint lang-sql linenums"><code>' . $query . '</code></pre>';

		alert('danger', $message);
		$mysqli->close();
	}
	else
	{
		alert('danger', SQL_ERROR);
		$mysqli->close();
	}
}

//used to print out the SQL statement so that you can see what it's doing
function prettyprint($data)
{
	?>
	<pre class="prettyprint lang-php"><code><?php print_r($data) ?></code></pre>
	<?php
}

/**
 * @param int $page_no: current page number
 * @param int $items_total: the counted total amount of items
 * @param int $page_length: the desired amount of items per page
 * @param int $page_around: the number of page links it shows to either side of the current page/link
 * @param bool $show_disabled_arrows: show disabled next or previous links, or hide them
 */
function pagination($page, $page_no, $items_total, $page_length, $page_around = 2, $show_disabled_arrows = true)
{
//$items_total
	// Only shows pagination if total items is greater than page length
	if($items_total > $page_length)
	{
		$pages_total = ceil($items_total / $page_length);

		//Page to start from, at least 3 below the current page
		$page_from = $page_no - $page_around;

		// If current page is in the last half of visible pages, set page_from to the total pages minus page_around x2 (default 2x2), plus 3
		if ($page_no > $pages_total - $page_around * 2)
		{
			$page_from = $pages_total - ($page_around * 2 + 2);
		}

		//if page from was calculated to be below 2, we start from the lowest number 2 (because we always have page 1)
		if($page_from < 2)
		{
			$page_from = 2;
		}

		//Page to end the for-loop with, at least 2 above (or what's set in the page_around) the current page
		$page_to = $page_no + $page_around;

		// If current page (page_no) is in the first half of visible pages, set page_to, to page_around x2 (default 2x2), plus 3. Default page_to, will be calcaluted to 7
		if($page_no <= $page_around * 2)
		{
			$page_to = $page_around * 2 + 3;
		}

		//If page_to was calculated to be above or equal to the total amount of pages, we end with to highest number possible. One below the total number, because we always have the last page.
		if($page_to >= $pages_total)
		{
			$page_to = $pages_total - 1;
		}

		//$page_to = min($page_no + $page_around, $pages_total - 1); // Same as above, just shorter syntax

		global $icons;

		echo '<ul class="pagination">';

		if($page_no > 1)
		{
			echo '<li><a href="index.php?page=' . $page . '&page-no=' . ($page_no - 1) . '" data-page="' . $page . '" data-params="page-no=' . ($page_no - 1) . '">' . $icons['previous'] . '</a></li>';
		}

		// If current page is not greater than 1, show disabled previous link
		else if ($show_disabled_arrows)
		{
			echo '<li class="disabled"><span>' . $icons['previous'] . '</span></li>';
		}

		// Show first page
		echo '<li' . ($page_no == 1 ? ' class="active"' : '') . '><a href="index.php?page=' . $page . '&page-no=1" data-page="' . $page . '" data-params="page-no=1">1</a></li>';


		// If page_from is greater than 2, we have skipped some pages, and show the 3 dots
		if($page_from > 2)
		{
			echo '<li class="disabled"><span>&hellip;</span></li>';
		}


	//use for loop.... start from number in page_from, and end with the number in page_to, increment with one for each loop
	for ($i = $page_from; $i <= $page_to; $i++)
	{

		echo '<li' . ($page_no == $i ? ' class="active" ' : '') . '><a href="index.php?page=' . $page . '&page-no=' . $i . '" data-page="' . $page . '" data-params="page-no=' . $i . '">' . $i . '</a></li>';

	}

		//If page_to is smaller than the second last page, we have skipped some pages in the end, so we show 3 dots
		if($page_to < $pages_total - 1)
		{
			echo '<li class="disabled"><span>&hellip;</span></li>';
		}

		// Show link to last page
		echo '<li' . ($page_no == $pages_total ? 'class="active"' : '') . '><a href="index.php?page=' . $page . '&page-no=' . $pages_total . '" data.page="' . $page . '" data-params="page-no' . $pages_total . '">' . $pages_total . '</a></li>';

	// if the current page is less than the total number of pages, show next button
		if ($page_no < $pages_total)
		{
			echo '<li><a href="index.php?page=' . $page . '&page-no=' . ($page_no + 1) . '" data-page="' . $page . '" data-params="page-no=' . ($page_no + 1) . '">' . $icons['next'] . '</a></li>';
		}
		//else if current page is not less than 1, show disabled cursor
		else if ($show_disabled_arrows)
		{
			echo '<li class="disabled"><span>' . $icons['next'] . '</span></li>';
		}
		echo '</ul>';
	}
}



function show_developer_info()
{
	?>
	<br>
	<pre class="prettyprint lang-php"><code>GET <?php print_r($_GET) ?></code></pre>
	<pre class="prettyprint lang-php"><code>POST <?php print_r($_POST) ?></code></pre>
	<pre class="prettyprint lang-php"><code>FILES <?php print_r($_FILES) ?></code></pre>
	<pre class="prettyprint lang-php"><code>SESSION <?php print_r($_SESSION) ?></code></pre>
	<pre class="prettyprint lang-php"><code>COOKIE <?php print_r($_COOKIE) ?></code></pre>
	<?php
}

// Take the user agent info from the browser, add a salt and hash the information with the algorithm sha256
function fingerprint()
{
    return hash('sha256', $_SERVER['HTTP_USER_AGENT'] . '%wr?øV/sd)b*<sf#');
}

function login($email, $password)
{
        // If one of the required fields is empty, show alert
        if(empty($email) || empty($password))
        {
            alert('warning', REQUIRED_FIELDS_EMPTY);
        }
        // If all required fields is not empty, continue
        else
        {
            global $mysqli;

            $email = $mysqli->escape_string($email);

            $query = "
                    SELECT 
                            user_id, user_password, role_access_level, user_name
                    FROM 
                            users
                    INNER JOIN
                            roles ON users.fk_role_id = roles.role_id
                    WHERE 
                            user_email = '$email'
                    AND 
                            user_status = 1";
            $result = $mysqli->query($query);

            // If result returns false, use the function query_error to show debugging info
            if(!$result)
            {

                query_error($query, __LINE__, __FILE__);
            }

            //If a user with they typed email was found in the database, do this
            if($result->num_rows == 1)
            {
                $row = $result->fetch_object();

                // Check if the typed password matched the hashed password in the Database
                if(password_verify($password, $row->user_password))
                {
                    // Give the current session a new id before saving user information into it
                    session_regenerate_id();

                    $_SESSION['user']['id']                 = $row->user_id;
                    $_SESSION['user']['access_level']       = $row->role_access_level;
                    $_SESSION['fingerprint']                = fingerprint();

                    //Use a function to insert event in log
                    create_event('info', '<a href="index.php?page=user-edit&id' . $row->user_id . '" data-page="user-edit" data-params="id=' . $row->user_id . ' ">' . $row->user_name . '</a>' . ' logged in', 100);

                    return true;
                }
                // If the typed password isn't a match to the hashed password in the database
                else
                {
                    alert('warning', EMAIL_OR_PW_INVALID . '4');
                }
            }
            // If no user with the typed email was found in the database, do this
            else
            {
                alert('warning', EMAIL_OR_PW_INVALID . '2');
            }
        }
    return false;
}

// Delete the sessions from login and give the session a new id
function logout()
{
    unset($_SESSION['user']);
    unset($_SESSION['fingerprint']);
    unset($_SESSION['last_activity']);
    // Give the current session a new id before saving user information into it
    session_regenerate_id();
}

/**
 * Function to check if user is Super Admin and only grants access if it matches
 * @return bool
 */
function is_super_admin()
{
	return $_SESSION['user']['access_level'] == 1000 ? true : false;
}

function check_fingerprint()
{
    // If the current fingerprint returned from the function doesn't match the fingerprint stored in session, logout!
    if ($_SESSION['fingerprint'] != fingerprint())
    {
        logout();
		?>
		<script type="text/javascript">
        	location.href='login.php';
		</script>
		<?php
        exit;
    }
}

function check_last_activity()
{
	// IF developer_status is false, use on session
	if(!DEVELOPER_STATUS)
	{
		// If session last_activity is set and the current timestamp + 30 mins is less than current timestamp, log the user out
		if(isset($_SESSION['last_activity']) && $_SESSION['last_activity'] + 1800 < time())
		{
			logout();
			?>
			<script type="text/javascript">
				location.href='login.php';
			</script>
			<?php
			exit;
		}
		// Or update the session with the current timestamp
		else
		{
			$_SESSION['last_activity'] = time();
		}
	}
}

function page_access($page)
{
	global $view_files;

	if ($view_files[$page]['required_access_lvl'] > $_SESSION['user']['access_level'])
	{
		?>
		<script type="text/javascript">
			location.href="index.php";
		</script>
		<?php
	}
}