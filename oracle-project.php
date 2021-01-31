<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.  
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values
 
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the 
  OCILogon below to be your ORACLE username and password -->

<html>
    <head>
        <title>CPSC 304 PHP/Oracle Demonstration</title>
    </head>

    <body>
        <h2>Reset</h2>
        <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

        <form method="POST" action="oracle-project.php">
            <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
            <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
            <p><input type="submit" value="Reset" name="reset"></p>
        </form>

        <hr />

        <h2>Insert Customer Information into the Computer Store Database</h2>
        <form method="POST" action="oracle-project.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            Customer ID : <input type="text" name="insID"> <br />(must be > 5 for demo)<br /><br />
            Name: <input type="text" name="insName"> <br /><br />
            Email: <input type="text" name="insEmail"> <br /><br />

            <input type="submit" value="Insert" name="insertSubmit"></p>
        </form>

        <hr />

        <h2>Update Email in Customer Table</h2>
        <p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>

        <form method="POST" action="oracle-project.php"> <!--refresh page when submitted-->
            <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
            Old Email: <input type="text" name="oldName"> <br /><br />
            New Email: <input type="text" name="newName"> <br /><br />

            <input type="submit" value="Update" name="updateSubmit"></p>
        </form>

        <hr />

        <h2>Count the Tuples in DemoTable</h2>
        <form method="GET" action="oracle-project.php"> <!--refresh page when submitted-->
            <input type="hidden" id="countTupleRequest" name="countTupleRequest">
            <input type="submit" name="countTuples"></p>
        </form>

        <h2>Display the Tuples in Customer Table</h2>
        <form method="GET" action="oracle-project.php"> <!--refresh page when submitted-->
            <input type="hidden" id="displayTupleRequest" name="displayTupleRequest">
            <input type="submit" name="displayTuples"></p>
        </form>

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr); 
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection. 
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function printResult($result) { //prints results from a select statement
            echo "<br>Retrieved data from table Customer:<br>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row["Customer_ID"] . "</td><td>" . $row["Full_Name"] . "</td><td>" . $row["Email"] . "</td></tr>"; //or just use "echo $row[0]" 
                // echo $row[0];
            }

            echo "</table>";
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example, 
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_yuta123", "a13449731", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        function handleUpdateRequest() {
            global $db_conn;

            $old_name = $_POST['oldName'];
            $new_name = $_POST['newName'];

            // you need the wrap the old name and new name values with single quotations
            executePlainSQL("UPDATE Customer SET EMAIL= '" . $new_name . "' WHERE EMAIL='" . $old_name . "'");
            OCICommit($db_conn);
        }

        function handleResetRequest() {
            global $db_conn;
            // Drop old table
            // all tables with FK should be dropped first
            executePlainSQL("DROP TABLE Mounts_Storage_Motherboard");
            executePlainSQL("DROP TABLE Controls_CPU_Motherboard");
            executePlainSQL("DROP TABLE Inserts_RAM_Motherboard");
            executePlainSQL("DROP TABLE Mounts_GPU_Computer");
            executePlainSQL("DROP TABLE Purchases_Computer_Customer");
            executePlainSQL("DROP TABLE Purchases_Accessory_Customer");
            executePlainSQL("DROP TABLE RAM_Model");
            executePlainSQL("DROP TABLE GPU_Model");
            executePlainSQL("DROP TABLE HDD");
            executePlainSQL("DROP TABLE SSD");
            executePlainSQL("DROP TABLE Monitor");
            executePlainSQL("DROP TABLE Mouse");
            executePlainSQL("DROP TABLE Connects_Motherboard_Computer");
            // then drop the tables with no FK
            executePlainSQL("DROP TABLE Motherboard");
            executePlainSQL("DROP TABLE CPU");
            // executePlainSQL("DROP TABLE RAM");
            executePlainSQL("DROP TABLE RAM_Memory");
            executePlainSQL("DROP TABLE Storage");
            executePlainSQL("DROP TABLE Customer");
            // executePlainSQL("DROP TABLE GPU");
            executePlainSQL("DROP TABLE GPU_CUDACore");
            executePlainSQL("DROP TABLE Accessory");
            
            // Create new table
            echo "<br> creating new tables <br>";
             // Computer and Motherboard DDLs
             executePlainSQL('CREATE TABLE Motherboard(
                "Motherboard_Model_Name"  CHAR(100) PRIMARY KEY, 
                "Price"                   FLOAT NOT NULL
            )');
            executePlainSQL('CREATE TABLE CPU(
	            "CPU_Model_Name"		CHAR(100)	PRIMARY KEY,
	            "Price" 				FLOAT		NOT NULL,
	            "Frequency"			FLOAT		NOT NULL,
	            "Core"				INTEGER	    NOT NULL
            )');
            // executePlainSQL('CREATE TABLE RAM(
            //     "RAM_Model_Name"		CHAR(100)	PRIMARY KEY,
            //     "Price" 				FLOAT		NOT NULL,
            //     "Frequency"			FLOAT		NOT NULL,
            //     "Memory_Types"		CHAR(100)	NOT NULL,
            //     "Size"				INTEGER	    NOT NULL
            // )');
            executePlainSQL('CREATE TABLE RAM_Memory(
                "Memory_Types"		CHAR(100)	PRIMARY KEY,
                "Frequency"			FLOAT 	    NOT NULL
            )');
            executePlainSQL('CREATE TABLE RAM_Model(
                "RAM_Model_Name"		CHAR(100)	PRIMARY KEY,
                "Price" 				FLOAT		NOT NULL,
                "Memory_Types"		CHAR(100)	NOT NULL,
                "Size"				INTEGER	    NOT NULL,
                FOREIGN KEY ("Memory_Types") REFERENCES RAM_Memory
                    ON DELETE CASCADE
            )');
            executePlainSQL('CREATE TABLE Storage(
                "Storage_Model_Name"	CHAR(100)	PRIMARY KEY,
                "Price" 				FLOAT		NOT NULL,
                "Size"				INTEGER	    NOT NULL
            )');
            executePlainSQL('CREATE TABLE HDD(
                "Storage_Model_Name"		CHAR(100)	PRIMARY KEY,
                "RPM"				        INTEGER 	NOT NULL,
                FOREIGN KEY ("Storage_Model_Name") REFERENCES Storage
                    ON DELETE CASCADE
            )');
            executePlainSQL('CREATE TABLE SSD(
                "Storage_Model_Name"		 CHAR(100)	PRIMARY KEY,
                "Interface"			     CHAR(100)	NOT NULL,
                FOREIGN KEY ("Storage_Model_Name") REFERENCES Storage
                    ON DELETE CASCADE
            )');
            executePlainSQL('CREATE TABLE Mounts_Storage_Motherboard(
                "Storage_Model_Name" 		 CHAR(100),
                "Motherboard_Model_Name"	 CHAR(100),
                PRIMARY KEY("Storage_Model_Name", "Motherboard_Model_Name"),
                FOREIGN KEY("Storage_Model_Name") REFERENCES Storage,
                FOREIGN KEY("Motherboard_Model_Name") REFERENCES Motherboard
                    ON DELETE CASCADE
            )');

            executePlainSQL('CREATE TABLE Controls_CPU_Motherboard(
                "CPU_Model_Name" 		     CHAR(100),
                "Motherboard_Model_Name"	 CHAR(100),
                PRIMARY KEY("CPU_Model_Name", "Motherboard_Model_Name"),
                FOREIGN KEY("CPU_Model_Name") REFERENCES CPU,
                FOREIGN KEY("Motherboard_Model_Name") REFERENCES Motherboard
            )');
            executePlainSQL('CREATE TABLE Inserts_RAM_Motherboard(
                "RAM_Model_Name" 		     CHAR(100),
                "Motherboard_Model_Name"	 CHAR(100),
                PRIMARY KEY("RAM_Model_Name", "Motherboard_Model_Name"),
                FOREIGN KEY("RAM_Model_Name") REFERENCES RAM_Model,
                FOREIGN KEY("Motherboard_Model_Name") REFERENCES Motherboard
            )');
            executePlainSQL('CREATE TABLE Connects_Motherboard_Computer(
                "Motherboard_Model_Name"	 CHAR(100)   NOT NULL,
                "Computer_Model_Name"	     CHAR(100),
                "Operating_System"		 CHAR(100)		NOT NULL,
                "Size"				     CHAR(100)		NOT NULL,
                "Price"				     FLOAT		    NOT NULL,
                PRIMARY KEY("Motherboard_Model_Name", "Computer_Model_Name"),
                FOREIGN KEY("Motherboard_Model_Name") REFERENCES Motherboard
            )');

            // Customer SQL DDLs
            executePlainSQL('CREATE TABLE Customer(
                "Customer_ID"		CHAR(100)	    PRIMARY KEY,
                "Full_Name"		CHAR(100)	NOT NULL,
                "Email"			CHAR(100)	NOT NULL
            )');
            // executePlainSQL('CREATE TABLE GPU(
            //     "GPU_Model_Name" 	CHAR(100)	PRIMARY KEY,
            //     "CUDA_core"		INTEGER	NOT NULL,
            //     "Frequency"		FLOAT		NOT NULL,
            //     "Price"			FLOAT		NOT NULL
            // )');
            executePlainSQL('CREATE TABLE GPU_CUDACore(
                "CUDA_core"			INTEGER	PRIMARY KEY,
                "Frequency"			FLOAT 	NOT NULL
            )');
            executePlainSQL('CREATE TABLE GPU_Model(
                "GPU_Model_Name" 	CHAR(100)	PRIMARY KEY,
                "CUDA_core"		INTEGER	NOT NULL,
                "Price"			FLOAT		NOT NULL,
                FOREIGN KEY ("CUDA_core") REFERENCES GPU_CUDACore
                    ON DELETE CASCADE
            )');
            executePlainSQL('CREATE TABLE Mounts_GPU_Computer(
                "Computer_Model_Name"	    CHAR(100),
                "Motherboard_Model_Name"    CHAR(100),    
                "GPU_Model_Name"		    CHAR(100),
                PRIMARY KEY("Computer_Model_Name", "Motherboard_Model_Name", "GPU_Model_Name"),
                FOREIGN KEY("Computer_Model_Name", "Motherboard_Model_Name") REFERENCES Connects_Motherboard_Computer,
                FOREIGN KEY("GPU_Model_Name") REFERENCES GPU_Model
            )');
            executePlainSQL('CREATE TABLE Purchases_Computer_Customer(
                "Computer_Model_Name"	CHAR(100),
                "Motherboard_Model_Name"CHAR(100),
                "Customer_ID"			CHAR(100),
                "OrderID"			    INTEGER,
                PRIMARY KEY("Computer_Model_Name", "Motherboard_Model_Name", "Customer_ID", "OrderID"),
                FOREIGN KEY("Computer_Model_Name", "Motherboard_Model_Name") REFERENCES Connects_Motherboard_Computer,
                FOREIGN KEY("Customer_ID") REFERENCES Customer
            )');
            
            // Accessories DDLs
            executePlainSQL('CREATE TABLE Accessory(
                "Accessory_Model_Name"	CHAR(100)	PRIMARY KEY,
                "Price" 				    FLOAT		NOT NULL
            )');

            executePlainSQL('CREATE TABLE Monitor(
                "Accessory_Model_Name"	CHAR(100)	PRIMARY KEY,
                "Refresh_Rate"			CHAR(100)	NOT NULL,
                "Resolution"			    CHAR(100)	NOT NULL,
                FOREIGN KEY ("Accessory_Model_Name") REFERENCES Accessory
                    ON DELETE CASCADE
            )');

            executePlainSQL('CREATE TABLE Mouse(
                "Accessory_Model_Name"    CHAR(100)	PRIMARY KEY,
                "Sensor_Type"			    CHAR(100)	NOT NULL,
                "Connection_Type"		    CHAR(100)	NOT NULL,
                FOREIGN KEY ("Accessory_Model_Name") REFERENCES Accessory
                    ON DELETE CASCADE
            )');

            executePlainSQL('CREATE TABLE Purchases_Accessory_Customer(
                "Accessory_Model_Name"	    CHAR(100),
                "Customer_ID"			    CHAR(100),
                "OrderID"			        INTEGER,
                PRIMARY KEY("Accessory_Model_Name", "Customer_ID", "OrderID"),
                FOREIGN KEY("Accessory_Model_Name") REFERENCES Accessory,
                FOREIGN KEY("Customer_ID") REFERENCES Customer
            )');
            
            // //TODO: Remember FK must refer to a existing value in the parent table

            // Inserting tuples to computer / motherboard
            executePlainSQL("INSERT INTO Motherboard VALUES ('Asus ROG Strix B450-F', 175)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('Asus ROG Strix Z490-E', 384)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('Asus Prime Z390-A', 228)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('MSI Creator TRX40 Motherboard', 945)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('GIGABYTE Z490 AORUS Master', 516)");
            
            executePlainSQL("INSERT INTO CPU VALUES ('AMD Ryzen 7 3800X', 495, 3.9 , 8)");
            executePlainSQL("INSERT INTO CPU VALUES ('Intel Core i7-9700K', 455, 4.9 , 8)");
            executePlainSQL("INSERT INTO CPU VALUES ('AMD Ryzen 5 3600XT', 350, 4.5 , 6)");
            executePlainSQL("INSERT INTO CPU VALUES ('Intel Core i7-10700', 460, 4.8 , 8)");
            executePlainSQL("INSERT INTO CPU VALUES ('Intel Core i9-10920X', 980, 4.8 , 12)");

            // executePlainSQL("INSERT INTO RAM VALUES ('CMW32GX4M2C3200C16', 190, 3200, 'DDR4 SDRAM', 32)");
            // executePlainSQL("INSERT INTO RAM VALUES ('HX426C16FB3K2/16', 90, 2666, 'DDR4 SDRAM', 16)");
            // executePlainSQL("INSERT INTO RAM VALUES ('M378A2K43CB1-CTD', 92, 2666, 'DDR4 SDRAM', 16)");
            // executePlainSQL("INSERT INTO RAM VALUES ('OWC2400DDR4S64S', 380, 2400, 'DDR4 SDRAM', 64)");
            // executePlainSQL("INSERT INTO RAM VALUES ('PVE416G240C6GY', 77, 2400, 'DDR4 SDRAM', 16)");

            executePlainSQL("INSERT INTO RAM_Memory VALUES ('DDR4 SDRAM1', 3200)");
            executePlainSQL("INSERT INTO RAM_Memory VALUES ('DDR4 SDRAM2', 2666)");
            executePlainSQL("INSERT INTO RAM_Memory VALUES ('DDR4 SDRAM3', 2666)");
            executePlainSQL("INSERT INTO RAM_Memory VALUES ('DDR4 SDRAM4', 2400)");
            executePlainSQL("INSERT INTO RAM_Memory VALUES ('DDR4 SDRAM5', 2400)");

            executePlainSQL("INSERT INTO RAM_Model VALUES ('CMW32GX4M2C3200C16', 190, 'DDR4 SDRAM1', 32)");
            executePlainSQL("INSERT INTO RAM_Model VALUES ('HX426C16FB3K2/16', 90, 'DDR4 SDRAM2', 16)");
            executePlainSQL("INSERT INTO RAM_Model VALUES ('M378A2K43CB1-CTD', 92, 'DDR4 SDRAM3', 16)");
            executePlainSQL("INSERT INTO RAM_Model VALUES ('OWC2400DDR4S64S', 380, 'DDR4 SDRAM4', 64)");
            executePlainSQL("INSERT INTO RAM_Model VALUES ('PVE416G240C6GY', 77, 'DDR4 SDRAM5', 16)");

            executePlainSQL("INSERT INTO Storage VALUES ('IronWolf NAS', 145, 4)");
            executePlainSQL("INSERT INTO Storage VALUES ('Blue', 60, 1)");
            executePlainSQL("INSERT INTO Storage VALUES ('LaCie Rugged Mini', 140, 2)");
            executePlainSQL("INSERT INTO Storage VALUES ('WD Gold', 832, 18)");
            executePlainSQL("INSERT INTO Storage VALUES ('N300', 165, 4)");

            executePlainSQL("INSERT INTO Storage VALUES ('Samsung 970 EVO', 122, 500)");
            executePlainSQL("INSERT INTO Storage VALUES ('Samsung 970 PRO', 470, 1)");
            executePlainSQL("INSERT INTO Storage VALUES ('Crucial MX500', 153, 1)");
            executePlainSQL("INSERT INTO Storage VALUES ('WDS100T2B0A', 135, 1)");
            executePlainSQL("INSERT INTO Storage VALUES ('OWCS3DN3P2T40', 1360, 4)");

            executePlainSQL("INSERT INTO HDD VALUES ('IronWolf NAS', 5900)");
            executePlainSQL("INSERT INTO HDD VALUES ('Blue', 7200)");
            executePlainSQL("INSERT INTO HDD VALUES ('LaCie Rugged Mini', 5400)");
            executePlainSQL("INSERT INTO HDD VALUES ('WD Gold', 7200)");
            executePlainSQL("INSERT INTO HDD VALUES ('N300', 7200)");

            executePlainSQL("INSERT INTO SSD VALUES ('Samsung 970 EVO', 'M.2')");
            executePlainSQL("INSERT INTO SSD VALUES ('Samsung 970 PRO', 'M.2')");
            executePlainSQL("INSERT INTO SSD VALUES ('Crucial MX500', 'SATA')");
            executePlainSQL("INSERT INTO SSD VALUES ('WDS100T2B0A', 'SATA')");
            executePlainSQL("INSERT INTO SSD VALUES ('OWCS3DN3P2T40', 'M.2')");

            executePlainSQL("INSERT INTO Mounts_Storage_Motherboard VALUES ('Samsung 970 EVO', 'Asus ROG Strix B450-F')");
            executePlainSQL("INSERT INTO Mounts_Storage_Motherboard VALUES ('Samsung 970 PRO', 'Asus ROG Strix B450-F')");
            executePlainSQL("INSERT INTO Mounts_Storage_Motherboard VALUES ('Crucial MX500', 'MSI Creator TRX40 Motherboard')");
            executePlainSQL("INSERT INTO Mounts_Storage_Motherboard VALUES ('WD Gold', 'MSI Creator TRX40 Motherboard')");
            executePlainSQL("INSERT INTO Mounts_Storage_Motherboard VALUES ('N300', 'MSI Creator TRX40 Motherboard')");

            executePlainSQL("INSERT INTO Controls_CPU_Motherboard VALUES ('AMD Ryzen 7 3800X', 'Asus ROG Strix B450-F')");
            executePlainSQL("INSERT INTO Controls_CPU_Motherboard VALUES ('Intel Core i7-9700K', 'Asus ROG Strix B450-F')");
            executePlainSQL("INSERT INTO Controls_CPU_Motherboard VALUES ('AMD Ryzen 5 3600XT', 'MSI Creator TRX40 Motherboard')");
            executePlainSQL("INSERT INTO Controls_CPU_Motherboard VALUES ('Intel Core i7-10700', 'MSI Creator TRX40 Motherboard')");
            executePlainSQL("INSERT INTO Controls_CPU_Motherboard VALUES ('Intel Core i9-10920X', 'MSI Creator TRX40 Motherboard')");

            executePlainSQL("INSERT INTO Inserts_RAM_Motherboard VALUES ('CMW32GX4M2C3200C16', 'Asus ROG Strix B450-F')");
            executePlainSQL("INSERT INTO Inserts_RAM_Motherboard VALUES ('HX426C16FB3K2/16', 'Asus ROG Strix B450-F')");
            executePlainSQL("INSERT INTO Inserts_RAM_Motherboard VALUES ('M378A2K43CB1-CTD', 'MSI Creator TRX40 Motherboard')");
            executePlainSQL("INSERT INTO Inserts_RAM_Motherboard VALUES ('OWC2400DDR4S64S', 'MSI Creator TRX40 Motherboard')");
            executePlainSQL("INSERT INTO Inserts_RAM_Motherboard VALUES ('PVE416G240C6GY', 'MSI Creator TRX40 Motherboard')");

            executePlainSQL("INSERT INTO Connects_Motherboard_Computer VALUES ('Asus ROG Strix B450-F', 'LenovoIdeaCentre510A', 'Windows 10Home 64 bit', 'Mid Tower', 500)");
            executePlainSQL("INSERT INTO Connects_Motherboard_Computer VALUES ('Asus ROG Strix Z490-E', 'AlienwareAurora 20Q11', 'Windows 10Home 64 bit', 'Mid Tower', 2500)");
            executePlainSQL("INSERT INTO Connects_Motherboard_Computer VALUES ('Asus Prime Z390-A', 'Acer AspireTC-895-UA91', 'Windows 10Home 64 bit', 'Mid Tower', 430)");
            executePlainSQL("INSERT INTO Connects_Motherboard_Computer VALUES ('MSI Creator TRX40 Motherboard', 'Apple iMac21-Inch', 'MacOS X', '528×450×175', 1400)");
            executePlainSQL("INSERT INTO Connects_Motherboard_Computer VALUES ('GIGABYTE Z490 AORUS Master', 'CORSAIR ONEi140', 'Windows 10Home 64 bit', 'Full Tower', 4500)");
            
            // Inserting tuples to customer table
            executePlainSQL("INSERT INTO Customer VALUES ('1','Bob', 'bob@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES ('2','Alex', 'alex@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES ('3','Charlie', 'charlie@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES ('4','Mary', 'mary@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES ('5','Sara', 'sara@gmail.com')");

            // Inserting tuples to gpu table

            executePlainSQL("INSERT INTO GPU_CUDACore VALUES (3584, 1493)");
            executePlainSQL("INSERT INTO GPU_CUDACore VALUES (4352, 1665)");
            executePlainSQL("INSERT INTO GPU_CUDACore VALUES (2304, 1247)");
            executePlainSQL("INSERT INTO GPU_CUDACore VALUES (1664, 1140)");
            executePlainSQL("INSERT INTO GPU_CUDACore VALUES (2880, 967)");

            executePlainSQL("INSERT INTO GPU_Model VALUES ('Asus GeForceGTX 1080 Ti 11GB STRIX', 3584, 600)");
            executePlainSQL("INSERT INTO GPU_Model VALUES ('ASUS-ROG-STRIX-RTX2080TI-O11G-GAMING', 4352, 1500)");
            executePlainSQL("INSERT INTO GPU_Model VALUES ('SapphireRadeon RX5600 XT 6 GBPULSE VideoCard', 2304, 300)");
            executePlainSQL("INSERT INTO GPU_Model VALUES ('MSI GeForceGTX 970 4 GBTwin Frozr VVideo Card', 1664, 330)");
            executePlainSQL("INSERT INTO GPU_Model VALUES ('EVGA GeForceGTX 780 3 GBSuperclockedACX Video Card', 2880, 650)");

            executePlainSQL("INSERT INTO Mounts_GPU_Computer VALUES ('Asus ROG Strix B450-F', 'LenovoIdeaCentre510A', 'Asus GeForceGTX 1080 Ti 11GB STRIX')");
            executePlainSQL("INSERT INTO Mounts_GPU_Computer VALUES ('Asus ROG Strix Z490-E', 'AlienwareAurora 20Q11', 'ASUS-ROG-STRIX-RTX2080TI-O11G-GAMING')");
            executePlainSQL("INSERT INTO Mounts_GPU_Computer VALUES ('Asus Prime Z390-A', 'Acer AspireTC-895-UA91', 'SapphireRadeon RX5600 XT 6 GBPULSE VideoCard')");
            executePlainSQL("INSERT INTO Mounts_GPU_Computer VALUES ('MSI Creator TRX40 Motherboard', 'Apple iMac21-Inch', 'MSI GeForceGTX 970 4 GBTwin Frozr VVideo Card')");
            executePlainSQL("INSERT INTO Mounts_GPU_Computer VALUES ('GIGABYTE Z490 AORUS Master', 'CORSAIR ONEi140', 'EVGA GeForceGTX 780 3 GBSuperclockedACX Video Card')");
            
            executePlainSQL("INSERT INTO Purchases_Computer_Customer VALUES ('Asus ROG Strix B450-F', 'LenovoIdeaCentre510A', '1', 10)");
            executePlainSQL("INSERT INTO Purchases_Computer_Customer VALUES ('Asus ROG Strix Z490-E', 'AlienwareAurora 20Q11', '2', 11)");
            executePlainSQL("INSERT INTO Purchases_Computer_Customer VALUES ('Asus Prime Z390-A', 'Acer AspireTC-895-UA91', '3', 12)");
            executePlainSQL("INSERT INTO Purchases_Computer_Customer VALUES ('MSI Creator TRX40 Motherboard', 'Apple iMac21-Inch', '4', 13)");
            executePlainSQL("INSERT INTO Purchases_Computer_Customer VALUES ('GIGABYTE Z490 AORUS Master', 'CORSAIR ONEi140', '5', 14)");

            // Inserting tuples to accessory
            executePlainSQL("INSERT INTO Accessory VALUES ('Microsoft Wireless Mobile Microsoft 15.95 Mouse 1850 -Black- U7Z-00002', '15.95')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Razer DeathAdder Essential Gaming Mouse: 6400 DPI Optical Sensor', '39.99')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Apple Pro Display XDR Standard Glass', '6499')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Apple Pro Display XDR Nano-texture glass', '7499')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Apple Magic Keyboard', '99')");

            executePlainSQL("INSERT INTO Accessory VALUES ('Dell 27 Inch 4k Monitor 2020', '1599')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Samsung Ultra 8k Monitor 2020', '2099')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Asus 4k Pro Monitor', '1599')");

            executePlainSQL("INSERT INTO Accessory VALUES ('Microsoft Wireless Mobile Mouse 1850 - Black - U7Z-00002', '10')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Apple Magic Mouse', '199')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Logitech Gaming Mouse', '150')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Asus Ultimate Gaming Mice', '300')");

            // Inserting tuples to Monitor
            executePlainSQL("INSERT INTO Monitor VALUES ('Dell 27 Inch 4k Monitor 2020', '120 Hz', '4k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Apple Pro Display XDR Standard Glass', '60 Hz', '6k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Apple Pro Display XDR Nano-texture glass', '60 Hz', '6k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Samsung Ultra 8k Monitor 2020', '120 Hz', '8k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Asus 4k Pro Monitor', '120 Hz', '4k')");

            // Inserting tuples to Mouse
            executePlainSQL("INSERT INTO Mouse VALUES ('Microsoft Wireless Mobile Mouse 1850 - Black - U7Z-00002', 'Laser', 'USB Wireless')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Razer DeathAdder Essential Gaming Mouse: 6400 DPI Optical Sensor', 'Optical', 'Wired')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Apple Magic Mouse', 'Optical', 'Wireless')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Logitech Gaming Mouse', 'Laser', 'Wired')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Asus Ultimate Gaming Mice', 'Optical', 'USB Wireless')");

            executePlainSQL("INSERT INTO Purchases_Accessory_Customer VALUES ('Asus Ultimate Gaming Mice', '1', '20')");
            executePlainSQL("INSERT INTO Purchases_Accessory_Customer VALUES ('Apple Pro Display XDR Standard Glass', '1', '21')");
            executePlainSQL("INSERT INTO Purchases_Accessory_Customer VALUES ('Logitech Gaming Mouse', '2', '22')");
            executePlainSQL("INSERT INTO Purchases_Accessory_Customer VALUES ('Dell 27 Inch 4k Monitor 2020', '5', '23')");
            executePlainSQL("INSERT INTO Purchases_Accessory_Customer VALUES ('Samsung Ultra 8k Monitor 2020', '4', '24')");

            OCICommit($db_conn);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['insID'],
                ":bind2" => $_POST['insName'],
                ":bind3" => $_POST['insEmail']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into Customer values (:bind1, :bind2, :bind3)", $alltuples);
            OCICommit($db_conn);
        }

        function handleCountRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM Customer");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
            }
        }

        function handleDisplayRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT * FROM Customer");
            printResult($result);
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('resetTablesRequest', $_POST)) {
                    handleResetRequest();
                } else if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                } else if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('countTuples', $_GET)) {
                    handleCountRequest();
                }
                if (array_key_exists('displayTuples', $_GET)) {
                    handleDisplayRequest();
                }
                disconnectFromDB();
            }
        }

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        } else if (isset($_GET['countTupleRequest'])) {
            handleGETRequest();
        } else if (isset($_GET['displayTupleRequest'])) {
            handleGETRequest();
        }
		?>
	</body>
</html>

