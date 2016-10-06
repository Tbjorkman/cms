<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$view_file = 'pages';
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
$page_length    = isset($_SESSION[$view_file]['page_length'])  		? intval($_SESSION[$view_file]['page_length'])      			: PAGE_LENGTH;
		$page_no        = isset($_SESSION['page_no'])               ? $_GET['page-no']                                     			: 1;
$sort_by	    = isset($_SESSION['sort_by'])               		? $_GET['sort-by']	                                   			: 'title';
$order			= isset($_SESSION['order'])	                		? $mysqli->escape_string($_GET['order'])	           			: 'asc';
$search         = isset($_SESSION[$view_file]['search'])      		? $mysqli->escape_string($_SESSION[$view_file]['search']) 		: '';
$icon_title	= $icon_url = $icon_locked = $icon_status = '';

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
	case 'title':
		$icon_title	= $icon;
		$order_by       ="page_title " . strtoupper($order);
		break;
	case 'url':
		$icon_url		= $icon;
		$order_by       ="page_url_key " . strtoupper($order);
		break;
	case 'locked':
		$icon_locked		= $icon;
		$order_by       ="page_protected " . strtoupper($order);
		break;
	case 'status':
		$icon_status	= $icon;
		$order_by       ="page_status " . strtoupper($order);
		break;
}
// If delete and id is defined in URL params, the id is not empty, delete the selected user
if (isset($_GET['delete'], $_GET['id']) && !empty($_GET['id']))
{
    // Get the selected page id from the URL param id
	$id = intval($_GET['id']);

	// Get the page from the Database
	$query = "
				SELECT 
					page_protected, page_title
				FROM  
					pages 
				WHERE 
					page_id = $id";
	$result = $mysqli->query($query);

	// If result returns false, use the function query_error to show debugging info
	if(!$result)
	{
		query_error($query, __LINE__, __FILE__);
	}

	if($result ->num_rows == 1)
	{
		//Return the information from the Database as an object
		$row = $result->fetch_object();

		//Only delete the selected user if the access level is below the current users access level or is Super Administrator
		if($row->page_protected != 1 || is_super_admin() )
		{
			$query = "
            DELETE FROM 
                    pages
            WHERE 
                    page_id = $id";
			$result = $mysqli->query($query);

			// If result returns false, use the function query_error to show debugging info
			if(!$result)
			{
				query_error($query, __LINE__, __FILE__);
			}
		}

		//Use a function to insert event in log
		create_event('delete', 'af siden' . $row->page_title, $view_files[$view_file]['required_access_lvl']);
	}
}
?>

<div class="page-title">
	<a class="<?php echo $buttons['create'] ?> pull-right" href="index.php?page=page-create" data-page="page-create"><?php echo $icons['create'] . CREATE_ITEM ?></a>
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['pages']['icon'] . ' ' . $view_files['pages']['title']
		?>
	</span>
</div>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<div class="title"><?php echo OVERVIEW_TABLE_HEADER ?></div>
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
							//Loop through the array $page_lengths defined in config.php
							foreach ($page_lengths as $key => $value)
							{
								// If the current $page_length matches the key in the array, save selected in the variable $selected or save empty string
								$selected = $page_length == $key ? 'selected' : '';
								//Add option to select with the key
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
				<form data-page="<?php echo $view_file ?>">
					<input type="hidden" name="page" value="<?php echo $view_file ?>">
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
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=title&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=title&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_title . TITLE ?></a>
					</th>
					<th>
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=url&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=url&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_url . URL ?></a>
					</th>
					<th class="icon"></th>

                <?php if ($_SESSION['user']['access_level'] == 1000) { // Only show protection toggle for Super Admins?>
					<th class="toggle">
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=locked&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=locked&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_locked . LOCKED ?></a>
					</th>
                <?php  } ?>
					<th class="toggle">
						<a href="index.php?page=<?php echo $view_file ?>&sort-by=status&order=<?php echo $new_order ?>" data-page="<?php echo $view_file ?>" data-params="sort-by=status&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_status . STATUS ?></a>
					</th>
					<th class="icon"></th>
					<th class="icon"></th>
				</tr>
				</thead>

				<tbody>
<?php
//Separating the query helps to make it more dynamic so that you don't have to rewrite a lot of the query over and over
$search_sql = '';

	if (!empty($search))
	{
			$search_sql ="AND (page_titel 
							LIKE '%$search%' 
						  OR page_meta_description 
							LIKE '%$search%')";
	}

			$query = "
					SELECT 
						page_id, page_status, page_protected, page_url_key, page_title
					FROM 
						pages $search_sql
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
		<td><?php $row->page_title ?></td>
		<td><a href="../<?php echo $row->page_url_key ?>" target="_blank">/<?php echo $row->page_url_key ?></a></td>

		<!-- LINK TIL SIDEINDHOLD -->
		<td class="icon">
			<a href="index.php?page=page-content&page-id=<?php echo $row->page_id ?>" title="<?php echo $view_files['page-content']['title'] ?>" data-page="page-content" data-params="page-id=<?php echo $row->page_id ?>"><?php echo $view_files['page-content']['icon'] ?></a>
		</td>

        <?php if ( is_super_admin()) { // Only show protection toggle for Super Admins?>
		<!-- TOGGLE TIL BESKYT/BESKYT IKKE ELEMENT -->
		<td class="toggle">
			<input type="checkbox" class="toggle-checkbox" id="<?php echo $row->page_id ?>" data-type="page-protected" <?php if($row->page_protected == 1) echo 'checked'; ?>
		</td>
        <?php } ?>

		<!-- TOGGLE TIL AKTIVER/DEAKTIVER ELEMENT -->
		<td class="toggle">
            <?php if ( $row->page_protected != 1 || is_super_admin() ){ // If page is not protected or we are super admin, sho status toggle?>
			<input type="checkbox" class="toggle-checkbox" id="<?php echo $row->page_id ?>" data-type="page-status" <?php if($row->page_status == 1) echo 'checked'; ?>
            <?php } ?>
		</td>

		<!-- REDIGER LINK -->
		<td class="icon">
			<a class="<?php echo $buttons['edit'] ?>" href="index.php?page=page-edit&id=<?php echo $row->page_id ?>" data-page="page-edit"
			   data-params="id=<?php echo $row->page_id ?>" title="<?php echo EDIT_ITEM ?>"><?php echo $icons['edit'] ?></a>
		</td>

		<!-- SLET LINK -->
		<td class="icon">
        <?php if ( $row->page_protected != 1 || is_super_admin() ){ // If page is not protected unless/or we are super admin, show delete link ?>
			<a class="<?php echo $buttons['delete'] ?>" data-toggle="confirmation"
			   href="index.php?page=<?php echo $view_file; ?>&id=<?php echo $row->page_id ?>&delete" data-page="<?php echo $view_file; ?>" data-params="id=<?php echo $row->page_id ?>&delete"
			   title="<?php echo DELETE_ITEM ?>"><?php echo $icons['delete'] ?></a>
        <?php } ?>
		</td>
	</tr>
	<?php
		}
	?>

				</tbody>
			</table>
		</div><!-- /.table-responsive -->

		<div class="row">
			<div class="col-md-3">
				<?php echo sprintf(SHOWING_ITEMS_AMOUNT, ($items_current_total == 0) ? 0 : $offset + 1, $offset + $items_current_total, $items_total) ?>
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
