<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$view_file = 'events';
}

page_access($view_file);

//if session users is not defined, define it with an empty array
if (!isset($_SESSION[$view_file]) )    $_SESSION[$view_file]               = [];
//if these URL params are set, save their value to session
if (isset($_GET['page-no']))        $_SESSION[$view_file]['page_no']       = $_GET['page-no'];
if (isset($_GET['sort-by']))        $_SESSION[$view_file]['sort_by']       = $_GET['sort-by'];
if (isset($_GET['order']))          $_SESSION[$view_file]['order']         = $_GET['order'];

if (isset($_GET['page-length']) && $_GET['page-length'] >= min($page_lengths) && $_GET['page-length'] <= max($page_lengths))
{
	$_SESSION[$view_file]['page_length']   = $_GET['page-length'];
	unset($_SESSION[$view_file]['page_no']);
}

// If search is defined in URL params and the value is not empty, save the value to session
if(isset($_GET['search']) && !empty($_GET['search']))
{
	$_SESSION[$view_file]['search'] = $_GET['search'];
	unset($_SESSION[$view_file]['page_no']);
}

// If search is defined in URL params and the value is empty, unset the session to clear search
if(isset($_GET['search']) && empty($_GET['search'])) unset($_SESSION[$view_file]['search']);

// Defaults
$page_length    = isset($_SESSION[$view_file]['page_length'])  ? intval($_SESSION[$view_file]['page_length'])      : PAGE_LENGTH;
$page_no        = isset($_SESSION['page_no'])               ? $_GET['page-no']                                     : 1;
$sort_by	    = isset($_SESSION['sort_by'])               ? $_GET['sort-by']	                                   : 'timestamp';
$order			= isset($_SESSION['order'])	                ? $mysqli->escape_string($_GET['order'])	           : 'desc';
$search         = isset($_SESSION[$view_file]['search'])    ? $mysqli->escape_string($_SESSION[$view_file]['search']) : '';
$icon_timestamp	= $icon_type = $icon_description = $icon_user_name = $icon_role_name = '';

//if else to get the items to sort accordingly
if ($order == 'desc')
{
	$new_order	= 'asc';
	$icon		= $icons['sort-desc'];
}
else
{
	$new_order	= 'desc';
	$icon		= $icons['sort-asc'];
}

//a switch statement to run through the different properties. In this case running through the different values and sorting them according to the ASC or DESC variables above
switch($sort_by)
{
	case 'timestamp':
		$icon_timestamp	= $icon;
		$order_by       ="event_time " . strtoupper($order);
		break;
	case 'type':
		$icon_type		= $icon;
		$order_by       ="event_type name " . strtoupper($order);
		break;
	case 'description':
		$icon_description		= $icon;
		$order_by       ="event_description " . strtoupper($order);
		break;
	case 'user-name':
		$icon_user_name		= $icon;
		$order_by       ="user_name " . strtoupper($order);
		break;
	case 'role-name':
		$icon_role_name	= $icon;
		$order_by       ="role_name " . strtoupper($order);
		break;
}
?>

<div class="page-title">
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files[$view_file]['icon'] . ' ' . $view_files[$view_file]['title']
		?>
	</span>
</div>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<div class="title"><?php echo LOGBOOK_DESCRIPTION ?></div>
		</div>
	</div>

	<div class="card-body">
		<div class="row">
			<div class="col-md-4">
				<form class="form-inline" data-page="<?php echo $view_file ?>">
					<input type="hidden" name="page" value="<?php echo $view_file ?>">
					<label class="font-weight-300">
						Vis
						<select class="form-control input-sm" name="page-length" data-change="submit-form">
							<?php
							foreach ($page_lengths as $key => $value)
							{
								$selected = $page_length == $key ? 'selected' : '';
								?>
								<option value="<?php echo $key ?>"<?php echo $selected; ?>><?php echo $value; ?></option>
								<?php
							}
							?>
						</select>
						elementer
					</label>
				</form>
			</div>
			<div class="col-md-5 col-md-offset-3 text-right">
				<form data-page="events">
					<input type="hidden" name="page" value="events">
					<div class="input-group input-group-sm">
						<input type="search" name="search" id="search" class="form-control" placeholder="<?php echo PLACEHOLDER_SEARCH ?>" value="">
						<span class="input-group-btn">
							<button class="btn btn-default" type="submit"><?php echo $icons['search'] ?></button>
						</span>
					</div>
				</form>
			</div>
		</div>

		<div class="table-responsive">
			<table class="table table-hover table-striped">
				<thead>
				<tr>
					<th>
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=timestamp&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=timestamp&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_timestamp . DATE_AND_TIME ?></a>
					</th>
					<th>
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=type&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=type&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_type . TYPE ?></a>
					</th>
					<th>
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=description&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=description&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_description . DESCRIPTION ?></a>
					</th>
					<th>
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=user-name&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=user-name&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_user_name . USER ?></a>
					</th>
					<th>
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=role-name&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=role-name&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_role_name . ROLE ?></a>
					</th>
				</tr>
				</thead>

				<tbody>
<?php
//Separating the query helps to make it more dynamic so that you don't have to rewrite a lot of the query over and over
$search_sql = '';

if (!empty($search))
{
	$search_sql ="AND 
					(event_description LIKE '%$search%' 
				  OR 
				  	user_name LIKE '%$search%'
				  OR 
				  	role_name LIKE '%$search%')";
}

$access_level_sql = '';

if($_SESSION['user']['access_level'] < 1000)
{
	$user_access_level 	= intval($_SESSION['user']['access_level']);

	$access_level_sql 	= " AND 
								event_access_level_required <= $user_access_level
							AND
								role_access_level <= $user_access_level";
}

				 $query = "
							SELECT 
								DATE_FORMAT(event_time, '" . DATETIME_FORMAT . "') AS event_time_formatted, event_description, event_type_name, event_type_class, user_name, role_name
							FROM 
								events 
						    INNER JOIN
						    	event_types ON events.fk_event_type_id = event_types.event_type_id
						    INNER JOIN 
						    	users ON events.fk_user_id = users.user_id
							INNER JOIN 
								roles ON users.fk_role_id = roles.role_id 
							WHERE
								1=1 $search_sql $access_level_sql
							";
$result = $mysqli->query($query);

$items_total = $result->num_rows;

$offset = ($page_no - 1) * $page_length;

$query .= " 
                            ORDER BY 
                                    $order_by 
                            LIMIT 
                                    $page_length
                            OFFSET
                                    $offset";

$result = $mysqli->query($query);

$items_current_total = $result->num_rows;

prettyprint($query);

if(!$result)
{
	query_error($query, __Line__, __FILE__);
}

while( $row = $result->fetch_object()) {
	?>
				<tr>
					<td><?php echo $row->event_time_formatted ?></td>
					<td><span class="label label-<?php echo $row->event_type_class ?>"><?php echo constant($row->event_type_name) ?></span></td>
					<td><?php echo $row->event_description ?></td>
					<td><?php echo $row->user_name ?></td>
					<td><?php echo $row->role_name ?></td>
				</tr>
	<?php
}
	?>
				</tbody>
			</table>
		</div><!-- /.table-responsive -->

		<div class="row">
			<div class="col-md-3">
				<?php echo sprintf(SHOWING_ITEMS_AMOUNT, ($items_current_total == 0) ? 0 : $offset + 1, $offset + $items_current_total) ?>
			</div>
			<div class="col-md-9 text-right">
					<?php
						pagination($view_file, $page_no, $items_total, $page_length);
					?>
			</div>
		</div>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
