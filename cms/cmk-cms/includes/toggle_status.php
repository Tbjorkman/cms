<?php
include '../config.php';
// Check if all required values is defined from post and the values are not empty
if (isset($_POST['type'], $_POST['status'], $_POST['id']) && !empty($_POST['type']) && !empty($_POST['id']))
{
    // Do switch on the value from type
    switch ($_POST['type'])
    {
        // If the value is users, do this (defined in the toggles attribute data-type)
        case 'users':

            if($_POST['id'] != $_SESSION['user']['id'])
            {
                //secure the value from id is int
                 $id = intval($_POST['id']);

                // Get the users from the Database
                $query = "
                        SELECT 
                            role_access_level
                        FROM  
                            users 
                        INNER JOIN 
                            roles ON users.fk_role_id = roles.role_id
                        WHERE 
                            user_id = $id";
                $result_user = $mysqli->query($query);

                // If result returns false, use the function query_error to show debugging info
                if(!$result_user)
                {
                    query_error($query, __LINE__, __FILE__);
                }

                if($result_user ->num_rows == 1)
                {
                    //Return the information from the Database as an object
                    $row = $result_user->fetch_object();

                    if ($row->role_access_level < $_SESSION['user']['access_level'] || $_SESSION['user']['access_level'] == 100)
                    {

                        // If status is true, save 1 to  $status, or save 0
                        $status =($_POST['status'] ? 1 : 0);

                        // Update status fro toggled user
                        $query = "
                        UPDATE
                            users
                        SET
                            user_status = $status
                        WHERE 
                            user_id = $id ";

                        $result = $mysqli->query($query);

                        // If result returns false, run the function query_error do show debugging info
                        if($result)
                        {
                            query_error($query, __LINE__, __FILE__);
                        }
                    }// Close: if ($row->role_access_level < $_SESSION['user']['access_level']...

                }// Close: if($result ->num_rows == 1)
            }// Close: if($_POST['id'] != $_SESSION['user']['id'])
            break;
    }
}
// return the bool value from $result in assoc array, with the key status and use json_encode to output data as a json object
json_encode(['status' => $result]);