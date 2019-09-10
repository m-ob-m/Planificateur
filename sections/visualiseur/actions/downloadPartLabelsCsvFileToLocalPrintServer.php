<?php
    /**
     * \name		downloadPartLabelsCsvFileToLocalPrintServer.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-07-23
     *
     * \brief 		From the server's point of view, uploads a csv file containning labelling information to the client
     * \details     From the server's point of view, uploads a csv file containning labelling information to the client
     */

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    $inputData =  json_decode(file_get_contents("php://input"));

    try
    {
        // INCLUDE
        require_once __DIR__ . "/../../../lib/numberFunctions/numberFunctions.php";
        require_once __DIR__ . "/../../job/controller/jobController.php";
        require_once __DIR__ . "/../../../parametres/type/controller/typeController.php";
        require_once __DIR__ . "/../../../parametres/model/controller/modelController.php";
        require_once __DIR__ . "/../../../parametres/generic/controller/genericController.php";

        /* 
        * No session required to open this page! Be careful concerning what you put here. 
        * Advanced user account control might become available in a later release.
        */

        // Vérification des paramètres
        $partIDs = $inputData->id ?? null;

        if($partIDs === null)
        {
            throw new \Exception("No part identifier provided. There is nothing to print.");
        }
        elseif(!is_array($partIDs))
        {
            $partIDs = [$partIDs];
        }

        $fileContents = "";
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            $csvDelimiter = ";";
            $fileContents .= "Job{$csvDelimiter}Dimensions{$csvDelimiter}Model{$csvDelimiter}Type{$csvDelimiter}PO\r\n";
            foreach($partIDs as $partID)
            {
                $jobTypePorte = \JobTypePorte::withID($db, $partID);
                $jobType = \JobType::withID($db, $jobTypePorte->getJobTypeId());
                $job = \Job::withID($db, $jobType->getJobId());
                $model = \Model::withID($db, $jobType->getModel()->getId());
                $type = \Type::withImportNo($db, $jobType->getType()->getImportNo());
                $generic = $type->getGeneric();
                $lengthMixedNumber = toMixedNumber($jobTypePorte->getLength() / 25.4, 16, true);
                $widthMixedNumber = toMixedNumber($jobTypePorte->getWidth() / 25.4, 16, true);

                $fileContents .= "{$job->getName()}{$csvDelimiter}" . 
                    "{$lengthMixedNumber}\" X {$widthMixedNumber}\"{$csvDelimiter}" . 
                    "{$model->getDescription()}{$csvDelimiter}" . 
                    "{$type->getDescription()}{$csvDelimiter}" . 
                    "{$job->getCustomerPurchaseOrderNumber()}\r\n";
            }
            $db->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $db->getConnection()->rollback();
            throw $e;
        }
        finally
        {
            $db = null;
        }

        writeLabellinginformationCsvFile($fileContents);
        
        // Retour au javascript
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
        echo json_encode($responseArray);
    }

    /**
     * Writes the labelling information csv file on the client computer.
     *
     * @param string $fileContents The labelling information in semicolon separated value format with an header row
     *
     * @throws \Exception If file cannot be written
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */
    function writeLabellinginformationCsvFile(?string $fileContents = null) : void
    {
        $remoteHost = IPAddressToUNCHostString($_SERVER["REMOTE_ADDR"]);
        
        $folder = "\\\\{$remoteHost}\\" . LABEL_PRINT_SERVER_SHARE_INTERNAL_PATH;
        $domain = LABEL_PRINT_SERVER_SHARE_DOMAIN;
        $username = LABEL_PRINT_SERVER_SHARE_USERNAME;
        $password = LABEL_PRINT_SERVER_SHARE_PASSWORD;

        if(!testConnectionToSharedFolder($folder))
        {
            connectToSharedFolder($folder, $domain, $username, $password);
        }

        try
        {
            $minimumIterationsAmount = 1;
            $maximumIterationsAmount = 10;
            for($i = $minimumIterationsAmount; $i <= $maximumIterationsAmount; $i++)
            {
                $filePath = "{$folder}\\fabplan{$i}.csv";
                $fileHandle = @fopen($filePath, "x");
                if($fileHandle === false) 
                {
                    if($i < $maximumIterationsAmount)
                    {
                        continue;
                    }
                    else
                    {
                        throw new \Exception("The printing queue is full.");
                    }
                }
                else
                {
                    if(fwrite($fileHandle, $fileContents) === false)
                    {
                        throw new \Exception("Cannot write file \"{$filePath}\" on the print server.");
                    }
                    fclose($fileHandle);
                    break;
                }
            }
        }
        catch(\Exception $e)
        {
            disconnectFromSharedFolder($folder);
            throw $e;
        }
    }

    /**
     * Connects to a shared folder.
     *
     * @param string $sharedFolder The path of the shared folder to connect to
     * @param string|null $domain The domain to use for the connection
     * @param string|null $username The username to use for the connection
     * @param string|null $password The password to use for the connection
     *
     * @throws \Exception If connection fails.
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */
    function connectToSharedFolder($sharedFolder, $domain = null, $username = null, $password = null) : void
    {
        $command = "net \"use\" " . escapeshellarg($sharedFolder);
        
        if($domain !== null && $username !== null)
        {
            $command .= " " . escapeshellarg("/user:{$domain}\\{$username}");
        }
        elseif($username !== null)
        {
            $command .= " " . escapeshellarg("/user:{$username}");
        }

        if($password !== null)
        {
            $command .= " " . escapeshellarg($password);
        }

        $output = array();
		$returnStatus = null;
		exec($command, $output, $returnStatus);
        //echo $command . "\r\n\r\n" . implode("\r\n", $output) . "\r\n\r\n" . $returnStatus . "\r\n";

        if($returnStatus !== 0)
        {
            throw new \Exception("Failed to connect to {$sharedFolder} with the provided credentials.");
        }
    }

    /**
     * Disconnects from a shared folder.
     *
     * @param string $sharedFolder The path of the shared folder to disconnect from
     *
     * @throws \Exception If disconnection fails.
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */
    function disconnectFromSharedFolder($sharedFolder)
    {
        $command = "net \"use\" " . escapeshellarg($sharedFolder) . " \"/delete\"";
        $output = array();
        $returnStatus = null;
        exec($command, $output, $returnStatus);
        //echo $command . "\r\n\r\n" . implode("\r\n", $output) . "\r\n\r\n" . $returnStatus . "\r\n";
		
        if($returnStatus !== 0)
        {
            throw new \Exception("Failed to disconnect from {$sharedFolder}.");
        }
    }

    /**
     * Tests if a connection to a shared folder already exists.
     *
     * @param string $sharedFolder The path of the shared folder to test the connection for
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return bool True if a connection to the specified folder already exists. False otherwise.
     */
    function testConnectionToSharedFolder($sharedFolder) : bool
    {
        $command = "dir " . escapeshellarg($sharedFolder);
        $output = array();
        $returnStatus = null;
        exec($command, $output, $returnStatus);
		//echo $command . "\r\n\r\n" . implode("\r\n", $output) . "\r\n\r\n" . $returnStatus . "\r\n";
		
        if($returnStatus === 0)
        {
            // A connection to the folder was established because no domain, username or password were required. The connection now exists.
            return true;
        }
        else
        {
            // An error code was returned. The connection doesn't already exist
            return false;
        }
    }

    /**
     * Turns an IPv4 or IPv6 address into a UNC host string.
     *
     * @param string $ip An IP address
     *
     * @throws \Exception If the provided IP address is invalid
     * @author Marc-Olivier Bazin-Maurice
     * @return string A UNC host string
     */
    function IPAddressToUNCHostString(string $ip) : string
    {
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            return $ip;
        }
        elseif(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            return preg_replace(["/:/", "/%/"], ["-", "s"], $_SERVER["REMOTE_ADDR"]) . ".ipv6-literal.net";
        }
        else {
            throw new \Exception("The client could not be identified properly.");
        }
    }
?>