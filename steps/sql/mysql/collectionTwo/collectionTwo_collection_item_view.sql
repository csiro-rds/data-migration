# The following block creates a crosstab view `taxon_traits` with a column for each vchrTraitType.
SET @sql = NULL;
SET @@group_concat_max_len = 1024 * 10;
SELECT GROUP_CONCAT(
		   CONCAT(
			   'GROUP_CONCAT(IF(t.intTraitTypeID=', tt.intTraitTypeID,
			   ',NULLIF(TRIM(CONCAT_WS(" ",t.vchrValue,CONCAT("(",t.vchrComment,")"))),""),NULL))AS`',
			   tt.vchrTraitType, '`') SEPARATOR ",\n") AS query
FROM tblTraitType tt
	JOIN tblTraitCategory c ON (c.intTraitCategoryID = tt.intCategoryID)
WHERE c.vchrCategory = 'Material'
ORDER BY vchrCategory, vchrTraitType
INTO @`sql`;
SET @`sql` = CONCAT('SELECT t.intIntraCatID AS intMaterialID, ', @`sql`, ' FROM tblTrait t JOIN tblTraitType tt USING(intTraitTypeID)
JOIN tblTraitCategory c ON (c.intTraitCategoryID = tt.intCategoryID) WHERE c.vchrCategory = ''Material'' GROUP BY  1');
SET @`sql` = CONCAT('CREATE OR REPLACE VIEW material_traits AS ', @`sql`);
PREPARE stmt FROM @sql;
EXECUTE stmt;

DEALLOCATE PREPARE stmt;

CREATE OR REPLACE VIEW collectionTwoCollectionItem AS
  SELECT
    DISTINCT
    i.newMaterialId  as materialId,
    i.itemType,
    i.preparationMethod,
    i.newMaterialId,
    i.accessionSuffix,
    m.intMaterialID,
    m.vchrMaterialName,
    m.intSiteVisitID,
    m.vchrAccessionNo,
    m.vchrRegNo,
    m.vchrCollectorNo,
    m.intBiotaID,
    m.vchrInstitution,
    m.vchrCollectionMethod,
    m.vchrAbundance,
    m.vchrMacroHabitat,
    m.vchrMicroHabitat,
    m.vchrSource,
    m.vchrSpecialLabel,
    m.vchrOriginalLabel,
    m.dtDateCreated,
    m.vchrWhoCreated,
    m.dtDateLastUpdated,
    m.vchrWhoLastUpdated,
    i.PartNotes,
    mt.OTHER_ID,
    m.GUID
  FROM
    collectionTwoItemPartSplit i
  JOIN
    tblMaterial m
  ON
    i.intMaterialID = m.intMaterialId
  LEFT JOIN
    material_traits mt  ON m.intMaterialID = mt.intMaterialID
  WHERE
    i.newMaterialId <> '0';
