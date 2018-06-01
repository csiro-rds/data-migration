# The following block creates a crosstab view `taxon_traits` with a column for each vchrTraitType.
SET @sql = NULL;
SET @@group_concat_max_len = 1024 * 10;
SELECT GROUP_CONCAT(
		   CONCAT(
			   'MAX(IF(t.intTraitTypeID=', tt.intTraitTypeID,
			   ',NULLIF(TRIM(CONCAT_WS(" ",t.vchrValue,CONCAT("(",t.vchrComment,")"))),""),NULL))AS`',
			   tt.vchrTraitType, '`') SEPARATOR ",\n") AS query
FROM tblTraitType tt
	JOIN tblTraitCategory c ON (c.intTraitCategoryID = tt.intCategoryID)
WHERE c.vchrCategory = 'Taxon'
ORDER BY vchrCategory, vchrTraitType
INTO @`sql`;
SET @`sql` = CONCAT('SELECT t.intIntraCatID AS intBiotaID, ', @`sql`, ' FROM tblTrait t JOIN tblTraitType tt USING(intTraitTypeID)
JOIN tblTraitCategory c ON (c.intTraitCategoryID = tt.intCategoryID) WHERE c.vchrCategory = ''Taxon'' GROUP BY  1');
SET @`sql` = CONCAT('CREATE OR REPLACE VIEW taxon_traits AS ', @`sql`);
PREPARE stmt FROM @sql;
EXECUTE stmt;

DEALLOCATE PREPARE stmt;

CREATE OR REPLACE VIEW collectionTwoTaxon AS
	SELECT
		NULLIF(TRIM(b.intBiotaID), '')                AS intBiotaID,
		NULLIF(TRIM(b.intParentID), '')               AS intParentID,
		NULLIF(TRIM(b.vchrEpithet), '')               AS vchrEpithet,
		NULLIF(TRIM(b.vchrYearOfPub), '')             AS vchrYearOfPub,
		NULLIF(TRIM(b.vchrAuthor), '')                AS vchrAuthor,
		NULLIF(TRIM(b.vchrNameQualifier), '')         AS vchrNameQualifier,
		NULLIF(TRIM(b.chrElemType), '')               AS chrElemType,
		NULLIF(TRIM(b.vchrRank), '')                  AS vchrRank,
		NULLIF(TRIM(b.intOrder), '')                  AS bintOrder,
		NULLIF(TRIM(b.vchrParentage), '')             AS vchrParentage,
		NULLIF(TRIM(b.bitChangedComb), '')            AS bitChangedComb,
		NULLIF(TRIM(b.bitShadowed), '')               AS bitShadowed,
		NULLIF(TRIM(b.bitUnplaced), '')               AS bitUnplaced,
		NULLIF(TRIM(b.bitUnverified), '')             AS bitUnverified,
		NULLIF(TRIM(b.bitAvailableName), '')          AS bitAvailableName,
		NULLIF(TRIM(b.bitLiteratureName), '')         AS bitLiteratureName,
		IFNULL(
		# CMS-1208 Calculate name for parents which do not have a name (Unplaced names)
			NULLIF(TRIM(b.vchrFullName), '')
			,
			IFNULL(
				CONCAT(
					NULLIF(
						TRIM(p.vchrEpithet), ''), ' (Unplaced)'),
				'Unplaced'
			)
		)                                             AS vchrFullName,
		NULLIF(TRIM(b.chrKingdomCode), '')            AS chrKingdomCode,
		NULLIF(TRIM(b.dtDateCreated), '')             AS dtDateCreated,
		NULLIF(TRIM(b.vchrWhoCreated), '')            AS vchrWhoCreated,
		NULLIF(TRIM(b.dtDateLastUpdated), '')         AS dtDateLastUpdated,
		NULLIF(TRIM(b.vchrWhoLastUpdated), '')        AS vchrWhoLastUpdated,
		NULLIF(TRIM(b.txtDistQual), '')               AS txtDistQual,
		GROUP_CONCAT(
			DISTINCT c.vchrCommonName SEPARATOR '|'
		)                                             AS vchrCommonName,
		NULLIF(TRIM(b.GUID), '')                      AS bGUID,
		NULLIF(TRIM(b.intImportedWithProjectID), '')  AS intImportedWithProjectID,
		NULLIF(TRIM(b.vchrAvailableNameStatus), '')   AS vchrAvailableNameStatus,
		NULLIF(TRIM(b.vchrParentKingdom), '')         AS vchrParentKingdom,
		NULLIF(TRIM(b.vchrParentPhylum), '')          AS vchrParentPhylum,
		NULLIF(TRIM(b.vchrParentClass), '')           AS vchrParentClass,
		NULLIF(TRIM(b.vchrParentOrder), '')           AS vchrParentOrder,
		NULLIF(TRIM(b.vchrParentFamily), '')          AS vchrParentFamily,
		NULLIF(TRIM(b.vchrParentGenus), '')           AS vchrParentGenus,
		NULLIF(TRIM(b.vchrParentSpecies), '')         AS vchrParentSpecies,
		NULLIF(TRIM(b.vchrParentSubspecies), '')      AS vchrParentSubspecies,
		NULLIF(TRIM(r.chrCode), '')                   AS chrCode,
		NULLIF(TRIM(r.vchrLongName), '')              AS vchrLongName,
		NULLIF(TRIM(r.vchrTextBeforeInFullName), '')  AS vchrTextBeforeInFullName,
		NULLIF(TRIM(r.vchrTextAfterInFullName), '')   AS vchrTextAfterInFullName,
		NULLIF(TRIM(r.bitJoinToParentInFullName), '') AS bitJoinToParentInFullName,
		NULLIF(TRIM(r.vchrChecklistDisplayAs), '')    AS vchrChecklistDisplayAs,
		NULLIF(TRIM(r.bitAvailableNameAllowed), '')   AS bitAvailableNameAllowed,
		NULLIF(TRIM(r.bitUnplacedAllowed), '')        AS bitUnplacedAllowed,
		NULLIF(TRIM(r.bitChgCombAllowed), '')         AS bitChgCombAllowed,
		NULLIF(TRIM(r.bitLituratueNameAllowed), '')   AS bitLituratueNameAllowed,
		NULLIF(TRIM(r.chrCategory), '')               AS chrCategory,
		NULLIF(TRIM(r.intOrder), '')                  AS rintOrder,
		NULLIF(TRIM(r.GUID), '')                      AS rGUID,
		IFNULL(
		# CMS-1208 Calculate name for parents which do not have a name (Unplaced names)
			NULLIF(TRIM(p.vchrFullName), '')
			,
			IFNULL(
				CONCAT(
					NULLIF(
						TRIM(a.vchrEpithet), ''), ' (Unplaced)'),
				'Unplaced'
			)
		)                                             AS parentFullName,
		NULLIF(TRIM(p.GUID), '')                      AS pGUID,
		NULLIF(TRIM(n.txtNote), '')                   AS txtNote,
		NULLIF(TRIM(ref.vchrRefCode), '')             AS vchrRefCode,
		NULLIF(TRIM(ref.vchrAuthor), '')              AS RefAuthor,
		NULLIF(TRIM(ref.vchrTitle), '')               AS vchrTitle,
		tt.`ORIGINAL_GENUS`,
		tt.`ORIGINAL_SPECIES`,
		tt.`ORIG_PUB`,
		tt.`REV_AUTHOR`,
		tt.`REV_YEAR`,
		tt.`REV_PUB`,
		tt.`DESIGNATOR`,
		tt.`Species ID`,
		tt.`Number of Species`,
		tt.`Understanding`,
		tt.`Fossil-Extant`,
		tt.`ID_Key_Number`,
		tt.`Species Group`

	FROM tblBiota b
		LEFT JOIN tblBiotaDefRank r ON b.chrElemType = r.chrCode
		LEFT JOIN tblBiota p ON b.intParentID = p.intBiotaID
		LEFT JOIN tblBiota a ON p.intParentID = a.intBiotaID
		LEFT JOIN tblNote n ON (
			b.intBiotaID = n.intIntraCatID AND n.intNoteTypeID IN (
				SELECT intNoteTypeID
				FROM tblNoteType
				WHERE vchrNoteType = 'Taxonomy')
			)
		LEFT JOIN tblReference ref USING (intRefID)
		LEFT JOIN taxon_traits tt ON (b.intBiotaID = tt.intBiotaID)
		LEFT JOIN tblCommonName c ON (b.intBiotaID = c.intBiotaID)
	# CMS-1326 CollectionTwo Taxon Migration Review - data import - limit source data for taxa
    WHERE b.vchrParentage NOT LIKE '\\\\30295\\\\29622\\\\24116%'
        AND b.vchrParentage NOT LIKE '\\\\30295\\\\29622\\\\49012%'
        AND b.vchrParentage NOT LIKE '\\\\30295\\\\29622\\\\30084\\\\42503%'
        AND b.vchrParentage NOT LIKE '\\\\46998%'
        AND b.vchrParentage NOT LIKE '\\\\46999%'
        AND b.vchrParentage NOT LIKE '\\\\30295\\\\29966%'
        AND b.vchrParentage NOT LIKE '\\\\30295\\\\43176%'
        AND b.vchrParentage NOT LIKE '\\\\30295\\\\29622\\\\42502%'
    # end of CMS-1326
	GROUP BY b.intBiotaID, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61,62, 63, 64, 65, 66, 67
	ORDER BY b.vchrParentage;
