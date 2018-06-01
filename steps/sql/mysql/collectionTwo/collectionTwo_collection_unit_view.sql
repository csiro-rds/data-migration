CREATE OR REPLACE VIEW collectionTwoCollectionUnit AS
SELECT
	`m`.`intMaterialID`                                  AS `intMaterialID`,
	`m`.`tintTemplate`                                   AS `tintTemplate`,
	`m`.`vchrMaterialName`                               AS `vchrMaterialName`,
	`m`.`intSiteVisitID`                                 AS `intSiteVisitID`,
	`m`.`vchrAccessionNo`                                AS `vchrAccessionNo`,
	`m`.`vchrRegNo`                                      AS `vchrRegNo`,
	`m`.`vchrCollectorNo`                                AS `vchrCollectorNo`,
	`m`.`intBiotaID`                                     AS `intBiotaID`,
	`m`.`vchrIDBy`                                       AS `vchrIDBy`,
	`m`.`dtIDDate`                                       AS `dtIDDate`,
	`m`.`intIDRefID`                                     AS `intIDRefID`,
	`m`.`vchrIDMethod`                                   AS `vchrIDMethod`,
	`m`.`vchrIDAccuracy`                                 AS `vchrIDAccuracy`,
	`m`.`vchrIDNameQual`                                 AS `vchrIDNameQual`,
	`m`.`vchrIDNotes`                                    AS `vchrIDNotes`,
	`m`.`vchrInstitution`                                AS `vchrInstitution`,
	`m`.`vchrCollectionMethod`                           AS `vchrCollectionMethod`,
	`m`.`vchrAbundance`                                  AS `vchrAbundance`,
	`m`.`vchrMacroHabitat`                               AS `vchrMacroHabitat`,
	`m`.`vchrMicroHabitat`                               AS `vchrMicroHabitat`,
	`m`.`vchrSource`                                     AS `vchrSource`,
	`m`.`intAssociateOf`                                 AS `intAssociateOf`,
	`m`.`vchrSpecialLabel`                               AS `vchrSpecialLabel`,
	`m`.`vchrOriginalLabel`                              AS `vchrOriginalLabel`,
	`m`.`dtDateCreated`                                  AS `dtDateCreated`,
	`m`.`vchrWhoCreated`                                 AS `vchrWhoCreated`,
	`m`.`dtDateLastUpdated`                              AS `dtDateLastUpdated`,
	`m`.`vchrWhoLastUpdated`                             AS `vchrWhoLastUpdated`,
	`m`.`intTrapID`                                      AS `intTrapID`,
	`m`.`vchrIDRefPage`                                  AS `vchrIDRefPage`,
	`m`.`GUID`                                           AS `GUID`,
	`a`.`txtAssocDescription`			     AS `txtAssocDescription`,
	group_concat(DISTINCT `mp`.`txtNotes` SEPARATOR '|') AS `txtNotes`
FROM (
	`tblMaterial` `m`
	LEFT JOIN `tblMaterialPart` `mp` ON ((`m`.`intMaterialID` = `mp`.`intMaterialID`))
	LEFT JOIN `tblAssociate` `a` ON ((`m`.`intMaterialID` = `a`.`intFromIntraCatID` AND `a`.`intFromCatID` = 1))
     )
	
GROUP BY m.`intMaterialID`,
	m.`tintTemplate`,
	m.`vchrMaterialName`,
	m.`intSiteVisitID`,
	m.`vchrAccessionNo`,
	m.`vchrRegNo`,
	m.`vchrCollectorNo`,
	m.`intBiotaID`,
	m.`vchrIDBy`,
	m.`dtIDDate`,
	m.`intIDRefID`,
	m.`vchrIDMethod`,
	m.`vchrIDAccuracy`,
	m.`vchrIDNameQual`,
	m.`vchrIDNotes`,
	m.`vchrInstitution`,
	m.`vchrCollectionMethod`,
	m.`vchrAbundance`,
	m.`vchrMacroHabitat`,
	m.`vchrMicroHabitat`,
	m.`vchrSource`,
	m.`intAssociateOf`,
	m.`vchrSpecialLabel`,
	m.`vchrOriginalLabel`,
	m.`dtDateCreated`,
	m.`vchrWhoCreated`,
	m.`dtDateLastUpdated`,
	m.`vchrWhoLastUpdated`,
	m.`intTrapID`,
	m.`vchrIDRefPage`,
	m.`GUID`,
	mp.`txtNotes`,
	a.`txtAssocDescription`;