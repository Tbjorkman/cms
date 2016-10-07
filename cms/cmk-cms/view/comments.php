<?php
if ( !isset($view_files) )
{
	require '../config.php';
	$view_file = 'comments';
}

page_access($view_file);

if(!isset($_GET['post-id']) || empty($_GET['post-id']))
{
	alert('danger', NO_ITEM_SELECTED );
}
else {
	// Get the page from the database
	$post_id = intval($_GET['post-id']);

//if session users is not defined, define it with an empty array
	if (!isset($_SESSION[$view_file])) $_SESSION[$view_file] = [];
//if these URL params are set, save their value to session
	if (isset($_GET['page-no'])) $_SESSION[$view_file]['page_no'] = $_GET['page-no'];
	if (isset($_GET['sort-by'])) $_SESSION[$view_file]['sort_by'] = $_GET['sort-by'];
	if (isset($_GET['order'])) $_SESSION[$view_file]['order'] = $_GET['order'];

	if (isset($_GET['page-length']) && $_GET['page-length'] >= min($page_lengths) && $_GET['page-length'] <= max($page_lengths)) {
		$_SESSION[$view_file]['page_length'] = $_GET['page-length'];
		unset($_SESSION[$view_file]['page_no']);
	}

// If search is defined in URL params and the value is not empty, save the value to session
	if (isset($_GET['search']) && !empty($_GET['search'])) {
		$_SESSION[$view_file]['search'] = $_GET['search'];
		unset($_SESSION[$view_file]['page_no']);
	}

// If search is defined in URL params and the value is empty, unset the session to clear search
	if (isset($_GET['search']) && empty($_GET['search'])) unset($_SESSION[$view_file]['search']);

// Defaults
	$page_length = isset($_SESSION[$view_file]['page_length']) ? intval($_SESSION[$view_file]['page_length']) : PAGE_LENGTH;
	$page_no = isset($_SESSION['page_no']) ? $_GET['page-no'] : 1;
	$sort_by = isset($_SESSION['sort_by']) ? $_GET['sort-by'] : 'created';
	$order = isset($_SESSION['order']) ? $mysqli->escape_string($_GET['order']) : 'asc';
	$search = isset($_SESSION[$view_file]['search']) ? $mysqli->escape_string($_SESSION[$view_file]['search']) : '';
	$icon_title = $icon_url = $icon_locked = $icon_status = '';

//if else to get the items to sort accordingly
	if ($order == 'desc') {
		$new_order = 'asc';
		$icon = $icons['sort-desc'];
	} else {
		$new_order = 'desc';
		$icon = $icons['sort-asc'];
	}

//a switch statement to run through the different properties. In this case running through the different values and sorting them according to the ASC or DESC variables above
	switch ($sort_by) {
		case 'created':
			$icon_create = $icon;
			$order_by = "post_created " . strtoupper($order);
			break;
		case 'comment':
			$icon_comment = $icon;
			$order_by = "comment_content" . strtoupper($order);
			break;
		case 'user':
			$icon_name = $icon;
			$order_by = "user_name " . strtoupper($order);
			break;
	}
// If delete and id is defined in URL params, the id is not empty, delete the selected user
	if (isset($_GET['delete'], $_GET['id']) && !empty($_GET['id'])) {
		// Get the selected page id from the URL param id
		$id = intval($_GET['id']);
		$ppst_id = intval($_GET['post-id']);

		// Get the page from the Database
		$query = "
				SELECT 
					comment_content, post_title, role_access_level
				FROM  
					comments
				INNER JOIN 
					posts ON comments.fk_post_id = posts.post_id
				INNER JOIN
					users ON comments.fk_user_id = users.user_id
				INNER JOIN 
					roles ON users.fk_role_id = roles.role_id
				WHERE 
					comment_id = $id";
		$result = $mysqli->query($query);

		// If result returns false, use the function query_error to show debugging info
		if (!$result) {
			query_error($query, __LINE__, __FILE__);
		}

		if ($result->num_rows == 1) {
			//Return the information from the Database as an object
			$row = $result->fetch_object();

			//Only delete the selected user if the access level is below the current users access level or is Super Administrator
			if ($row->role_access_level >= $_SESSION['user']['access_level'] == 10 || is_super_admin()) {
				$query = "
					DELETE FROM 
							comments
					WHERE 
							comment_id = $id";
				$result = $mysqli->query($query);

				// If result returns false, use the function query_error to show debugging info
				if (!$result) {
					query_error($query, __LINE__, __FILE__);
				}
			}

			//Use a function to insert event in log
			create_event('delete', 'af siden' . $row->post_title, $view_files[$view_file]['required_access_lvl']);
		}
	}

	?>

	<div class="page-title">
		<a class="<?php echo $buttons['create'] ?> pull-right"
		   href="index.php?page=comment-create&post-id=<?php echo $post_id ?>" data-page="comment-create"
		   data-params="post-id=<?php echo $post_id ?>"><?php echo $icons['create'] . CREATE_ITEM ?></a>
		<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['comments']['icon'] . ' Eksempel på indlæg 1'
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
						<input type="hidden" name="post-id" value="<?php echo $row->post_id ?>">
						<label class="font-weight-300">
							Vis
							<select class="form-control input-sm" name="page-length" data-change="submit-form">
								<?php
								foreach ($page_lengths as $key => $value) {
									$selected = $page_length == $key ? 'selected' : '';
									?>
									<option
										value="<?php echo $key ?>"<?php echo $selected; ?>><?php echo $value; ?></option>
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
						<input type="hidden" name="post-id" value="<?php echo $row->post_id ?>">
						<div class="input-group input-group-sm">
							<input type="search" name="search" id="search" class="form-control"
								   placeholder="<?php echo PLACEHOLDER_SEARCH ?>" value="">
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
							<a href="index.php?page=<?php echo $view_file ?>&sort-by=created&order=<?php echo $new_order ?>"
							   data-page="<?php echo $view_file ?>"
							   data-params="sort-by=created&order=<?php echo $new_order ?>"
							   title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icons['sort-desc'] . CREATED ?></a>
						</th>
						<th>
							<a href="index.php?page=<?php echo $view_file ?>&sort-by=content&order=<?php echo $new_order ?>"
							   data-page="<?php echo $view_file ?>"
							   data-params="sort-by=content&order=<?php echo $new_order ?>"
							   title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo CONTENT ?></a>
						</th>
						<th>
							<a href="index.php?page=<?php echo $view_file ?>&sort-by=user-name&order=<?php echo $new_order ?>"
							   data-page="<?php echo $view_file ?>"
							   data-params="sort-by=user-name&order=<?php echo $new_order ?>"
							   title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo USER ?></a>
						</th>
						<th class="icon"></th>
						<th class="icon"></th>
					</tr>
					</thead>

					<tbody>
					<?php
					//Separating the query helps to make it more dynamic so that you don't have to rewrite a lot of the query over and over
					$search_sql = '';

					if (!empty($search)) {
						$search_sql = "WHERE comment_created 
										LIKE '%$search%' 
									  OR post_title 
										LIKE '%$search%'";
					}

					$access_level_sql = '';

					// If current users access level is below 10, add filter to sql, so users with higher access level is not visible
					if ($_SESSION['user']['access_level'] < 10) {
						$user_access_level = intval($_SESSION['user']['access_level']);

						$access_level_sql = "
											AND
												role_access_level <= $user_access_level";
					}

					$query = "
							SELECT 
								comment_id, DATE_FORMAT(post_created, '" . DATETIME_FORMAT . "') AS comment_created_formatted, comment_content, user_name, post_id, post_title, role_access_level
							FROM 
								comments
							INNER JOIN
								posts ON comments.fk_post_id = posts.post_id
							INNER JOIN
								users ON comments.fk_user_id = users.user_id
							INNER JOIN 
								roles ON users.fk_role_id = roles.role_id 
							WHERE
								fk_post_id = $post_id $search_sql $access_level_sql
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

					if (!$result) {
						query_error($query, __Line__, __FILE__);
					}

					while ($row = $result->fetch_object()) {
						?>
						<tr>
							<td><?php echo $row->comment_created_formatted ?></td>
							<td><?php echo substr($row->comment_content, 0, 100) ?></td>

							<td><?php echo $row->user_name ?></td>

							<!-- REDIGER LINK -->
							<td class="icon">
								<a class="<?php echo $buttons['edit'] ?>"
								   href="index.php?page=comment-edit&post-id=<?php echo $row->post_id ?>&id=<?php echo $row->comment_id ?>"
								   data-page="comment-edit"
								   data-params="post-id=<?php echo $row->post_id ?>&id=<?php echo $row->comment_id ?>"
								   title="<?php echo EDIT_ITEM ?>"><?php echo $icons['edit'] ?></a>
							</td>

							<!-- SLET LINK -->
							<td class="icon">
								<a class="<?php echo $buttons['delete'] ?>" data-toggle="confirmation"
								   href="index.php?page=<?php echo $view_file ?>&post-id=<?php echo $row->post_id ?>&id=<?php echo $row->comment_id ?>&delete"
								   data-page="<?php echo $view_file ?>"
								   data-params="post-id=<?php echo $row->post_id ?>&id=<?php echo $row->comment_id ?>&delete"
								   title="<?php echo DELETE_ITEM ?>"><?php echo $icons['delete'] ?></a>
							</td>
						</tr>
						<?php
					}// Close: while( $row = $result->fetch_object())
					?>
					</tbody>
				</table>
			</div><!-- /.table-responsive -->

			<div class="row">
				<div class="col-md-3">
					<?php
					echo sprintf(SHOWING_ITEMS_AMOUNT, ($items_current_total == 0) ? 0 : $offset + 1, $offset + $items_current_total, $items_total)
					?>
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
}// Close : if(!isset($_GET['post-id']) || empty($_GET['post-id']))
if (DEVELOPER_STATUS) { show_developer_info(); }
