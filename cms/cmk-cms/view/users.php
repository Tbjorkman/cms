<?php
if ( !isset($view_files) )
{
	require '../config.php';

    //security('../');

}
//if session users is not defined, define it with an empty array
if (!isset($_SESSION['users']) )    $_SESSION['users']                  = [];
//if these URL params are set, save their value to session
if (isset($_GET['page-no']))        $_SESSION['users']['page_no']       = $_GET['page-no'];
if (isset($_GET['sort-by']))        $_SESSION['users']['sort_by']       = $_GET['sort-by'];
if (isset($_GET['order']))          $_SESSION['users']['order']         = $_GET['order'];

if (isset($_GET['page-length']) && $_GET['page-length'] >= min($page_lengths) && $_GET['page-length'] <= max($page_lengths))
{
    $_SESSION['users']['page_length']   = $_GET['page-length'];
    unset($_SESSION['users']['page_no']);
}

// If search is defined in URL params and the value is not empty, save the value to session
if(isset($_GET['search']) && !empty($_GET['search']))
{
    $_SESSION['users']['search'] = $_GET['search'];
    unset($_SESSION['users']['page_no']);
}

// If search is defined in URL params and the value is empty, unset the session to clear search
if(isset($_GET['search']) && empty($_GET['search'])) unset($_SESSION['users']['search']);

// Defaults
$page_length    = isset($_SESSION['users']['page_length'])  ? intval($_SESSION['users']['page_length'])            : PAGE_LENGTH;
$page_no        = isset($_SESSION['page_no'])               ? $_GET['page-no']                                     : 1;
$sort_by	    = isset($_SESSION['sort_by'])               ? $_GET['sort-by']	                                   : 'created';
$order			= isset($_SESSION['order'])	                ? $mysqli->escape_string($_GET['order'])	           : 'desc';
$search         = isset($_SESSION['users']['search'])       ? $mysqli->escape_string($_SESSION['users']['search']) : '';
$icon_created	= $icon_name = $icon_email = $icon_role = $icon_status = '';

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
	case 'created':
		$icon_created	= $icon;
        $order_by       ="user_created " . strtoupper($order);
		break;
	case 'name':
		$icon_name		= $icon;
        $order_by       ="user_name " . strtoupper($order);
		break;
	case 'email':
		$icon_email		= $icon;
        $order_by       ="user_email " . strtoupper($order);
		break;
	case 'role':
		$icon_role		= $icon;
        $order_by       ="role_name " . strtoupper($order);
		break;
	case 'status':
		$icon_status	= $icon;
        $order_by       ="user_status " . strtoupper($order);
		break;
}
// If delete and id is defined in URL params and the id is not empty, delete the selected user
if (isset($_GET['delete'], $_GET['id']) && !empty($_GET['id']))
{
    $id = intval($_GET['id']);

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
    $query = "
            DELETE FROM 
                    users
            WHERE 
                    user_id = $id";
    $result = $mysqli->query($query);

    // If result returns false, use the function query_error to show debugging info
    if(!$result)
    {
        query_error($query, __LINE__, __FILE__);
    }

    //Use a function to insert event in log
    create_event('delete', 'af brugeren' . $row->user_name, 100);
}
?>

<div class="page-title">
	<a class="<?php echo $buttons['create'] ?> pull-right" href="index.php?page=user-create" data-page="user-create"><?php echo $icons['create'] . CREATE_ITEM ?></a>
	<span class="title">
		<?php
		// Get icon and title from Array $files, defined in config.php
		echo $view_files['users']['icon'] . ' ' . $view_files['users']['title']
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
				<form class="form-inline" data-page="users">
					<input type="hidden" name="page" value="users">
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
            //echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option';
        }
?>
						</select>
						elementer
					</label>
				</form>
			</div>
			<div class="col-md-5 col-md-offset-3 text-right">
				<form data-page="users">
					<input type="hidden" name="page" value="users">
					<div class="input-group input-group-sm">
						<input type="search" name="search" id="search" class="form-control" placeholder="<?php echo PLACEHOLDER_SEARCH ?>" value="<?php echo $search ?>">
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
						<a href="index.php?page=users&sort-by=created&order=<?php echo $new_order ?>" data-page="users" data-params="sort-by=created&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_created . CREATED ?></a>
					</th>
					<th>
						<a href="index.php?page=users&sort-by=name&order=<?php echo $new_order ?>" data-page="users" data-params="sort-by=name&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_name . NAME ?></a>
					</th>
					<th>
						<a href="index.php?page=users&sort-by=email&order=<?php echo $new_order ?>" data-page="users" data-params="sort-by=email&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_email . EMAIL ?></a>
					</th>
					<th>
						<a href="index.php?page=users&sort-by=role&order=<?php echo $new_order ?>" data-page="users" data-params="sort-by=role&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_role . ROLE ?></a>
					</th>
					<th class="toggle">
						<a href="index.php?page=users&sort-by=status&order=<?php echo $new_order ?>" data-page="users" data-params="sort-by=status&order=<?php echo $new_order ?>" title="<?php echo SORT_BY_THIS_COLUMN ?>"><?php echo $icon_status . STATUS ?></a>
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
                    $search_sql ="WHERE user_name 
                                    LIKE '%$search%' 
						          OR user_email 
						            LIKE '%$search%'";
                }

					$query = "
							SELECT 
								user_id, user_status, DATE_FORMAT (user_created, '%a, %e. %b %Y kl. %H:%I') AS user_created_formatted, user_name, user_email, role_name
							FROM 
								users 
							INNER JOIN 
								roles ON users.fk_role_id = roles.role_id $search_sql
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

					while( $row = $result->fetch_object())
                    {
                        ?>
                        <tr>
                            <td><?php echo $row->user_created_formatted ?></td>
                            <td><?php echo $row->user_name ?></td>
                            <td><?php echo $row->user_email ?></td>
                            <td><?php echo constant($row->role_name) ?></td>

                            <!-- TOGGLE TIL AKTIVER/DEAKTIVER ELEMENT -->
                            <td class="toggle">
                                <input type="checkbox" class="toggle-checkbox" id="<?php echo $row->user_id ?>" data-type="users" <?php if($row->user_status == 1) { echo 'checked';} ?> >
                            </td>

                            <!-- REDIGER LINK -->
                            <td class="icon">
                                <a class="<?php echo $buttons['edit'] ?>" href="index.php?page=user-edit&id=<?php echo $row->user_id ?>" data-page="user-edit" data-params="id=<?php echo $row->user_id ?>" title="<?php echo EDIT_ITEM ?>"><?php echo $icons['edit'] ?></a>
                            </td>

                            <!-- SLET LINK -->
                            <td class="icon">
                                <a class="<?php echo $buttons['delete'] ?>" data-toggle="confirmation" href="index.php?page=users&id=<?php echo $row->user_id ?>&delete" data-page="users" data-params="id=<?php echo $row->user_id ?>&delete" title="<?php echo DELETE_ITEM ?>"><?php echo $icons['delete'] ?></a>
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
				<?php echo sprintf(SHOWING_ITEMS_AMOUNT, $offset + 1, $offset + $items_current_total, $items_total) ?>
			</div>
			<div class="col-md-9 text-right">
				<ul class="pagination">
                    <?php

                            pagination($page_no, $items_total, $page_length);



                    ?>
					<!--<li class="disabled"><a href=""><?php //echo $icons['previous'] ?></a></li>
					<li class="active"><span>1</span></li>
					<li><a href="index.php?page=users&page-no=2" data-page="users" data-params="page-no=2">2</a></li>
					<li><a href="index.php?page=users&page-no=3" data-page="users" data-params="page-no=3">3</a></li>
					<li><a href="index.php?page=users&page-no=4" data-page="users" data-params="page-no=4">4</a></li>
					<li><a href="index.php?page=users&page-no=5" data-page="users" data-params="page-no=5">5</a></li>
					<li class="disabled">
						<span>&hellip;</span>
					</li>
					<li><a href="index.php?page=users&page-no=9" data-page="users" data-params="page-no=9">9</a></li>
					<li><a href="index.php?page=users&page-no=2" data-page="users" data-params="page-no=2"><?php //echo $icons['next'] ?></a></li>-->
				</ul>
			</div>
		</div>
	</div>
</div>

<?php
if (DEVELOPER_STATUS) { show_developer_info(); }
