<?php
    // Initialize the session
    session_start();
    
    // Check if the user is already logged in, if yes then redirect him to welcome page
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
    {
        header("location: /Planificateur/index.php");
        exit;
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
            if(empty(trim($_POST["username"] ?? "")))
            {
                $username_err = "Please enter username.";
            } 
            else
            {
                $username = trim($_POST["username"]);
            }
            
            // Check if password is empty
            if(empty(trim($_POST["password"] ?? "")))
            {
                $password_err = "Please enter your password.";
            } 
            else
            {
                $password = trim($_POST["password"]);
            }
            
            // Validate credentials
            if(empty($username_err) && empty($password_err))
            {
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
                        SELECT `u`.`id` AS `id`, `u`.`password` AS `password`, `u`.`logInRedirect` AS `Redirection` 
                        FROM `users` AS `u` 
                        WHERE `u`.`username` = :username;
                    ");
                    if($stmt)
                    {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bindParam(":username", $username, \PDO::PARAM_STR);
                        
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
                                        
                                        // Redirect user to welcome page
                                        header("location: {$row["Redirection"]}");
                                    } 
                                    elseif($row["password"] === "" || $row["password"] === null)
                                    {
                                        // Prepare an update statement
                                        if($stmt1 = $pdo->prepare("UPDATE `users` AS `u` SET `u`.`password` = :password WHERE `u`.`id` = :id;"))
                                        {
                                            // Bind variables to the prepared statement as parameters
                                            $stmt1->bindParam(":password", password_hash($password, PASSWORD_DEFAULT), \PDO::PARAM_STR);
                                            $stmt1->bindParam(":id", $id, \PDO::PARAM_INT);
                                            
                                            // Attempt to execute the prepared statement
                                            if($stmt1->execute())
                                            {
                                                // Store data in session variables
                                                $_SESSION["loggedin"] = true;
                                                $_SESSION["id"] = $id;
                                                $_SESSION["username"] = $username;
                                                
                                                // Redirect user to welcome page
                                                header("location: {$row["Redirection"]}");
                                            } 
                                            else
                                            {
                                                echo "Oops! Something went wrong. Please try again later.";
                                            }
                                        }
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
                                // Display an error message if username doesn't exist
                                $username_err = "No account found with that username.";
                            }
                        } 
                        else
                        {
                            echo "Oops! Something went wrong. Please try again later.";
                        }
                    }
                    else
                    {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                }
            }
        }
    }

    // Close session.
    session_write_close();
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/Planificateur/lib/account/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? "has-error" : ""; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? "has-error" : ""; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <!-- <p>Don't have an account? <a href="register.php">Sign up now</a>.</p> -->
        </form>
    </div>
</body>
</html>