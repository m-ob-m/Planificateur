<?php
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    $credentials =  json_decode(file_get_contents("php://input"));

    // Initialize the session
    session_start();
    try
    {   
        // Check if the user is already logged in, if yes then redirect him to welcome page
        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
        {
            // Already logged in.
        }
        else
        { 
            // Include config file
            require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
            
            // Define variables and initialize with empty values
            $username = $password = "";
            $username_err = $password_err = "";
            
            // Processing form data when form is submitted
            if($_SERVER["REQUEST_METHOD"] == "POST")
            {
                $username = null;
                $password = null;
                
                // Check if username is empty
                if(empty(trim($credentials->username ?? "")))
                {
                    throw new \Exception("No username provided.");
                } 
                else
                {
                    $username = trim($credentials->username);
                }
                
                // Check if password is empty
                if(empty(trim($credentials->password ?? "")))
                {
                    throw new \Exception("No password provided.");
                } 
                else
                {
                    $password = trim($credentials->password);
                }
                
                // Open a PDO connection to Fabplan with the authentication user
                $pdo = null;
                try{
                    $pdo = (new \FabplanConnection(DATABASE_AUTHENTICATION_USER_NAME))->getConnection();
                }
                catch(\Exception $e)
                {
                    echo $e->getMessage();
                }

                if($pdo !== null)
                {
                    // Prepare a select statement
                    $stmt = $pdo->prepare("
                        SELECT `u`.`id` AS `id`, `u`.`password` AS `password`
                        FROM `users` AS `u` 
                        WHERE `u`.`username` = :username;
                    ");
                    if($stmt)
                    {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bindParam(":username", $credentials->username, PDO::PARAM_STR);
                        
                        // Attempt to execute the prepared statement
                        if($stmt->execute())
                        {
                            // Check if username exists, if yes then verify password
                            if($stmt->rowCount() == 1)
                            {
                                if($row = $stmt->fetch())
                                {
                                    $id = $row["id"];
    
                                    if(password_verify($password, $row["password"]))
                                    {
                                        // Store data in session variables
                                        $_SESSION["loggedin"] = true;
                                        $_SESSION["id"] = $id;
                                        $_SESSION["username"] = $username;
                                    }
                                    else
                                    {
                                        // Display an error message if password is not valid
                                        $password_err = "The password you entered was not valid.";
                                    }
                                }
                            } 
                            else
                            {
                                throw new \Exception("Username does not exist.");
                            }
                        } 
                        else
                        {
                            throw new \Exception("Failed to query database. Please try again later.");
                        }
                    }
                }
                else
                {
                    throw new \Exception("Failed to prepare query to database. Please try again later.");
                }
            }
        }

        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = null;
    }
    catch(Exception $e)
    {
        $responseArray["status"] = "failure";
        $responseArray["failure"]["message"] = $e->getMessage();
    }
    finally
    {
        // Close the session.
        session_write_close();
        
        echo json_encode($responseArray);
    }
?>