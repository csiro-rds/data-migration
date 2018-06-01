-- Report on table data within a database using INFORMATION_SCHEMA to inspect the data

SET @sql = NULL;
-- may need to be bigger depending on how many tables, columns you have and the length of data in those columns
SET @@group_concat_max_len = 1024 * 1024 * 1024;


SELECT
	GROUP_CONCAT(
		CONCAT_WS(
			'',
			'SELECT "', c.TABLE_NAME, '" AS tableName, "',
			c.COLUMN_NAME, '" AS field,"',
			c.DATA_TYPE, '" AS dataType,',
			'COUNT(DISTINCT `', c.COLUMN_NAME, '`) AS distinctValues,
COUNT(`', c.COLUMN_NAME, '`) AS numRecords,
MIN(LENGTH(`', c.COLUMN_NAME, '`)) AS minLength,
MAX(LENGTH(`', c.COLUMN_NAME, '`)) AS maxLength,
AVG(LENGTH(`', c.COLUMN_NAME, '`)) AS meanLength,
LEFT(GROUP_CONCAT(DISTINCT TRIM(`', c.COLUMN_NAME, '`) SEPARATOR "|"),1024) AS sampleValues
FROM `', c.TABLE_NAME, '`
WHERE `', c.COLUMN_NAME, '` IS NOT NULL
AND trim(`', c.COLUMN_NAME, '`) != ""
GROUP BY 1 '
		) SEPARATOR "UNION \n\t")
		AS query
FROM INFORMATION_SCHEMA.COLUMNS c
	JOIN INFORMATION_SCHEMA.TABLES t ON
										 c.TABLE_NAME = t.TABLE_NAME AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
WHERE c.TABLE_SCHEMA = (SELECT DATABASE()) AND c.TABLE_NAME NOT IN ('collectionOne_datareport')
	  AND t.TABLE_ROWS > 0
INTO
	@sql;

SET @sql = CONCAT('CREATE OR REPLACE VIEW collectionOne_datareport AS ', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
