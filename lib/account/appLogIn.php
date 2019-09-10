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
            require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/config.php";
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
                
                // Prepare a select statement
                $sql = "SELECT id, username, password FROM users WHERE username = :username";
                $pdo = (new \FabplanConnection())->getConnection();
                if($stmt = $pdo->prepare($sql))
                {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
                    
                    // Set parameters
                    $param_username = trim($credentials->username);
                    
                    // Attempt to execute the prepared statement
                    if($stmt->execute())
                    {
                        // Check if username exists, if yes then verify password
                        if($stmt->rowCount() == 1)
                        {
                            $row = $stmt->fetch();
                            $id = $row["id"];
                            $username = $row["username"];
                            $hashed_password = $row["password"];
                            if(password_verify($password, $hashed_password))
                            {                                
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;                            
                                
                                // Successfully logged in.
                            }
                            else
                            {
                                throw new \Exception("Password is not valid.");
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
                else
                {
                    throw new \Exception("Failed to prepare query to database. Please try again later.");
                }
                
                // Close statement
                unset($stmt);
                
                // Close connection
                unset($pdo);
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