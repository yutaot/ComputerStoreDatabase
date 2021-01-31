CREATE TABLE Motherboard(
	Motherboard_Model_Name	CHAR(20)	PRIMARY KEY,
	Price 				    FLOAT		NOT NULL
)

CREATE TABLE CPU(
	CPU_Model_Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Frequency			FLOAT		NOT NULL,
	Core				INTEGER	    NOT NULL
)

--TODO: do we need the RAM table if we decompose?
CREATE TABLE RAM(
	RAM_Model_Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Frequency			FLOAT		NOT NULL,
	Memory_Types		CHAR(20)	NOT NULL,
	Size				INTEGER	    NOT NULL
)

CREATE TABLE RAM_Memory(
	Memory_Types		CHAR(20)	PRIMARY KEY,
	Frequency			FLOAT 	    NOT NULL
)

CREATE TABLE RAM_Model(
    RAM_Model_Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Memory_Types		CHAR(20)	NOT NULL,
	Size				INTEGER	    NOT NULL,
	FOREIGN KEY (Memory_Types) REFERENCES RAM_Memory
	    ON DELETE CASCADE
		ON UPDATE CASCADE
)

CREATE TABLE Storage(
	Storage_Model_Name	CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Size				INTEGER	NOT NULL
)

CREATE TABLE HDD(
	Storage_Model_Name		CHAR(20)	PRIMARY KEY,
	RPM				        INTEGER 	NOT NULL,
	FOREIGN KEY (Storage_Model_Name) REFERENCES Storage
        ON DELETE CASCADE
		ON UPDATE CASCADE
)

CREATE TABLE SSD(
	Storage_Model_Name		 CHAR(20)	PRIMARY KEY,
	Interface			     CHAR(20)	NOT NULL,
	FOREIGN KEY (Storage_Model_Name) REFERENCES Storage
        ON DELETE CASCADE
		ON UPDATE CASCADE
)

CREATE TABLE Mounts_Storage_Motherboard(
	Storage_Model_Name 		 CHAR(20),
    Motherboard_Model_Name	 CHAR(20)
	PRIMARY KEY(Storage_Model_Name, Motherboard_Model_Name),
	FOREIGN KEY(Storage_Model_Name) REFERENCES Storage,
	FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
		ON DELETE CASCADE
		ON UPDATE CASCADE
)

CREATE TABLE Controls_CPU_Motherboard(
	CPU_Model_Name 		     CHAR(20),
    Motherboard_Model_Name	 CHAR(20)
	PRIMARY KEY(CPU_Model_Name, Motherboard_Model_Name),
	FOREIGN KEY(CPU_Model_Name) REFERENCES CPU,
	FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

CREATE TABLE Inserts_RAM_Motherboard(
	RAM_Model_Name 		     CHAR(20),
    Motherboard_Model_Name	 CHAR(20)
	PRIMARY KEY(RAM_Model_Name, Motherboard_Model_Name),
	FOREIGN KEY(RAM_Model_Name) REFERENCES RAM,
	FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

CREATE TABLE Connects_Motherboard_Computer(
    Motherboard_Model_Name	 CHAR(20)		NOT NULL,
    Computer_Model_Name	     CHAR(20),
    Operating_System		 CHAR(20)		NOT NULL,
	Chassis_Brand		     CHAR(20)		NOT NULL,
	Size				     CHAR(20)		NOT NULL,
	Price				     FLOAT		    NOT NULL,
	PRIMARY KEY(Motherboard_Model_Name, Computer_Model_Name),
	FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
		ON DELETE NO ACTION
        ON UPDATE CASCADE
)

-- Customer / Computer

CREATE TABLE Customer(
	Customer_ID		INTEGER	PRIMARY KEY,
	Name			CHAR(50)	NOT NULL,
	Email			CHAR(50)	NOT NULL
)

--TODO: do we need the GPU table if we decompose it
CREATE TABLE GPU(
	GPU_Model_Name 	CHAR(50)	PRIMARY KEY,
	CUDA_core		INTEGER	NOT NULL,
	Frequency		FLOAT		NOT NULL,
	Price			FLOAT		NOT NULL
)

CREATE TABLE GPU_CUDACore(
	CUDA_core			INTEGER	PRIMARY KEY,
	Frequency			FLOAT 	NOT NULL
)


CREATE TABLE GPU_Model(
	GPU_Model_Name 	CHAR(50)	PRIMARY KEY,
	CUDA_core		INTEGER	NOT NULL,
	Price			FLOAT		NOT NULL,
	FOREIGN KEY (CUDA_core) REFERENCES GPU_CUDACore
	    ON DELETE CASCADE
	    ON UPDATE CASCADE
)

-- Note: There is a total participation from GPU to Mounts which means GPU Model Name cannot be null but since GPU Model Name is a PK, NOT NULL is not required.
CREATE TABLE Mounts_GPU_Computer(
	Computer_Model_Name	    CHAR(50),
	GPU_Model_Name		    CHAR(50),
	PRIMARY KEY(Computer_Model_Name, GPU_Model_Name),
	FOREIGN KEY(Computer_Model_Name) REFERENCES Connects_Motherboard_Computer
		ON DELETE NO ACTION
		ON UPDATE CASCADE,
	FOREIGN KEY(GPU_Model_Name) REFERENCES GPU
        ON DELETE NO ACTION
		ON UPDATE CASCADE
)

-- Note: There is a total participation from Customer to Purchases which means CustomerID cannot be null but since CustomerID is a PK, NOT NULL is not required.
CREATE TABLE Purchases_Computer_Customer(
	Computer_Model_Name	CHAR(50),
	Customer_ID			CHAR(50),
	OrderID			    INTEGER,
	PRIMARY KEY(Computer_Model_Name, Customer_ID, OrderID),
	FOREIGN KEY(Computer_Model_Name) REFERENCES Connects_Motherboard_Computer
		ON DELETE NO ACTION
		ON UPDATE CASCADE,
	FOREIGN KEY(Customer_ID) REFERENCES Customer
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

-- Note: There is a total participation from Customer to Purchases which means CustomerID cannot be null but since CustomerID is a PK, NOT NULL is not required.

CREATE TABLE Purchases_Accessory_Customer(
	Accessories_Model_Name	CHAR(50),
	Customer_ID			    CHAR(50),
	OrderID			        INTEGER,
	PRIMARY KEY(Accessories_Model_Name, Customer_ID, OrderID),
	FOREIGN KEY(Accessories_Model_Name) REFERENCES Accessory
		ON DELETE NO ACTION
		ON UPDATE CASCADE,
	FOREIGN KEY(Customer_ID) REFERENCES Customer
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

-- Accessory

CREATE TABLE Accessory(
	Accessory_Model_Name	CHAR(50)	PRIMARY KEY,
	Price 				    FLOAT		NOT NULL
)

CREATE TABLE Monitor(
	Accessory_Model_Name	CHAR(50)	PRIMARY KEY,
	Refresh_Rate			CHAR(20)	NOT NULL,
	Resolution			    CHAR(20)	NOT NULL,
	FOREIGN KEY (Accessory_Model_Name) REFERENCES Accessory
        ON DELETE CASCADE
        ON UPDATE CASCADE
)

CREATE TABLE Mouse(
	Accessory_Model_Name    CHAR(50)	PRIMARY KEY,
	Sensor_Type			    CHAR(20)	NOT NULL,
	Connection_Type		    CHAR(20)	NOT NULL,
	FOREIGN KEY (Accessory_Model_Name) REFERENCES Accessory
        ON DELETE CASCADE
	    ON UPDATE CASCADE
)
