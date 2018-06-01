-- Report on table data within a database using information_schema to inspect the data
DROP TABLE IF EXISTS collectionTwo_datareport;
SET @sql = NULL;
-- may need to be bigger depending on how many tables, columns you have and the length of data in those columns
SET @@group_concat_max_len = 1024 * 1024 * 1024;
SELECT GROUP_CONCAT(
	CONCAT_WS(
		'',
		'SELECT "', TABLE_NAME, '" AS tableName, "',
		COLUMN_NAME, '" AS field,
		COUNT(DISTINCT ', COLUMN_NAME, ') AS distinctValues,
		COUNT(`', COLUMN_NAME, '`) AS numRecords,
		SUM(IF(`', COLUMN_NAME, '` IS NULL, 1, 0)) AS numNulls,
		SUM(IF(TRIM(`', COLUMN_NAME, '`) = "", 1, 0)) AS numBlanks,
		MIN(LENGTH(`', COLUMN_NAME, '`)) AS minLength,
		MAX(LENGTH(`', COLUMN_NAME, '`)) AS maxLength,
		AVG(LENGTH(`', COLUMN_NAME, '`)) AS meanLength,
		LEFT(GROUP_CONCAT(DISTINCT TRIM(`', COLUMN_NAME, '`) SEPARATOR "|"),255) AS sampleValues
		FROM `', TABLE_NAME, '`
		GROUP BY 1 ')
	SEPARATOR "UNION \n\t")
INTO @sql
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = (SELECT DATABASE()) AND TABLE_NAME LIKE 'tbl%';
SET @sql = CONCAT('CREATE TABLE collectionTwo_datareport AS SELECT * FROM (', @sql, ') AS datareport');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
ALTER TABLE collectionTwo_datareport
	CHANGE COLUMN sampleValues sampleValues LONGTEXT DEFAULT NULL;
ALTER TABLE collectionTwo_datareport
	ADD COLUMN dataType VARCHAR(255) DEFAULT NULL
	AFTER field;

UPDATE collectionTwo_datareport d
	JOIN information_schema.COLUMNS c
		ON (
		d.tableName = c.TABLE_NAME AND
		d.field = c.COLUMN_NAME AND
		c.TABLE_SCHEMA = (SELECT database()
		)
		)
SET d.dataType = c.DATA_TYPE;
DROP TABLE IF EXISTS collectionTwo_trait_datareport;
CREATE TABLE collectionTwo_trait_datareport
(
	category varchar(50) null,
	traitType varchar(255) null,
	dataType varchar(20) null,
	bitUseInReports tinyint(1) null,
	numValues bigint(21) not null,
	distinctValues bigint(21) not null,
	minLength bigint(10) null,
	maxLength bigint(10) null,
	sampleValues LONGTEXT null,
	sampleComments LONGTEXT null
)
;
INSERT INTO collectionTwo_trait_datareport
	SELECT
		c.vchrCategory                                   AS category,
		type.vchrTraitType                               AS traitType,
		type.vchrDataType                                AS dataType,
		bitUseInReports,
		count(*)                                         AS numValues,
		COUNT(DISTINCT vchrValue)                        AS distinctValues,
		min(length(vchrValue))                           AS minLength,
		max(length(vchrValue))                           AS maxLength,
		LEFT(GROUP_CONCAT(DISTINCT LEFT(ifnull(vchrValue, ''), 255) SEPARATOR '|'), 1024)   AS sampleValues,
		LEFT(GROUP_CONCAT(DISTINCT LEFT(IFNULL(vchrComment, ''), 255) SEPARATOR '|'), 1024) AS sampleComments
	FROM
		tblTrait t LEFT JOIN
		tblTraitCategory c ON (t.intTraitCatID = c.intTraitCategoryID)
		LEFT JOIN
		tblTraitType type ON (t.intTraitTypeID = type.intTraitTypeID)
	GROUP BY category, traitType, dataType,t.bitUseInReports
	ORDER BY category, traitType;
