CREATE OR REPLACE VIEW collectionTwo_location_traits AS
	SELECT
		trait.intIntraCatID,
		traitType.vchrTraitType,
		GROUP_CONCAT(trait.vchrValue SEPARATOR '|') AS vchrValue
	FROM tblTrait trait
		JOIN
		tblTraitCategory c
			ON (trait.intTraitCatID = c.intTraitCategoryID AND c.vchrCategory = 'Site')
		JOIN
		tblTraitType traitType ON (trait.intTraitTypeID = traitType.intTraitTypeID)
	GROUP BY intIntraCatID, vchrTraitType;
CREATE OR REPLACE VIEW
	collectionTwo_location AS
	SELECT
		s.`intSiteID`,
		s.`vchrSiteName`,
		s.`intPoliticalRegionID`,
		IFNULL(r5.`vchrName`, '')             AS r5Name,
		IFNULL(CASE r5.`vchrRank`
			   WHEN 'Country'
				   THEN 'country'
			   WHEN 'State'
				   THEN 'state'
			   WHEN 'Contry'
				   THEN 'country'
			   WHEN 'Region'
				   THEN 'region'
			   WHEN 'State/Province'
				   THEN 'state'
			   END, 'other')                  AS r5Rank,
		IFNULL(r5.`intPoliticalRegionID`, '') AS r5ID,
		IFNULL(r4.`vchrName`, '')             AS r4Name,
		IFNULL(CASE r4.`vchrRank`
			   WHEN 'Country'
				   THEN 'country'
			   WHEN 'State'
				   THEN 'state'
			   WHEN 'Contry'
				   THEN 'country'
			   WHEN 'Region'
				   THEN 'region'
			   WHEN 'State/Province'
				   THEN 'state'
			   END, 'other')                  AS r4Rank,
		IFNULL(r4.`intPoliticalRegionID`, '') AS r4ID,
		IFNULL(r3.`vchrName`, '')             AS r3Name,
		IFNULL(CASE r3.`vchrRank`
			   WHEN 'Country'
				   THEN 'country'
			   WHEN 'State'
				   THEN 'state'
			   WHEN 'Contry'
				   THEN 'country'
			   WHEN 'Region'
				   THEN 'region'
			   WHEN 'State/Province'
				   THEN 'state'
			   END, 'other')                  AS r3Rank,
		IFNULL(r3.`intPoliticalRegionID`, '') AS r3ID,
		IFNULL(r2.`vchrName`, '')             AS r2Name,
		IFNULL(CASE r2.`vchrRank`
			   WHEN 'Country'
				   THEN 'country'
			   WHEN 'State'
				   THEN 'state'
			   WHEN 'Contry'
				   THEN 'country'
			   WHEN 'Region'
				   THEN 'region'
			   WHEN 'State/Province'
				   THEN 'state'
			   END, 'other')                  AS r2Rank,
		IFNULL(r2.`intPoliticalRegionID`, '') AS r2ID,
		IFNULL(r1.`vchrName`, '')             AS r1Name,
		IFNULL(CASE r1.`vchrRank`
			   WHEN 'Country'
				   THEN 'country'
			   WHEN 'State'
				   THEN 'state'
			   WHEN 'Contry'
				   THEN 'country'
			   WHEN 'Region'
				   THEN 'region'
			   WHEN 'State/Province'
				   THEN 'state'
			   END, 'other')                  AS r1Rank,
		IFNULL(r1.`intPoliticalRegionID`, '') AS r1ID,
		s.`intSiteGroupID`,
		s.`tintLocalType`,
		s.`vchrLocal`,
		s.`vchrDistanceFromPlace`,
		s.`vchrDirFromPlace`,
		s.`vchrInformalLocal`,
		s.`tintPosCoordinates`,
		s.`tintPosAreaType`,
		s.`fltPosY1`,
		s.`fltPosX1`,
		s.`fltPosX2`,
		s.`fltPosY2`,
		s.`tintPosXYDisplayFormat`,
		s.`vchrPosSource`,
		s.`vchrPosError`,
		s.`vchrPosWho`,
		s.`vchrPosDate`,
		s.`vchrPosOriginal`,
		s.`vchrPosUTMSource`,
		s.`vchrPosUTMMapProj`,
		s.`vchrPosUTMMapName`,
		s.`vchrPosUTMMapVer`,
		s.`tintElevType`,
		s.`fltElevUpper`,
		s.`fltElevLower`,
		s.`fltElevDepth`,
		if (s.`vchrElevUnits` = 'f', 'ft', s.`vchrElevUnits`) AS vchrElevUnits,
		s.`vchrElevSource`,
		s.`vchrElevError`,
		s.`vchrGeoEra`,
		s.`vchrGeoState`,
		s.`vchrGeoPlate`,
		s.`vchrGeoFormation`,
		s.`vchrGeoMember`,
		s.`vchrGeoBed`,
		s.`vchrGeoName`,
		s.`vchrGeoAgeBottom`,
		s.`vchrGeoAgeTop`,
		s.`vchrGeoNotes`,
		s.`dtDateCreated`                     AS sitedtDateCreated,
		s.`vchrWhoCreated`                    AS sitevchrWhoCreated,
		s.`dtDateLastUpdated`                 AS sitedtDateLastUpdated,
		s.`vchrWhoLastUpdated`                AS sitevchrWhoLastUpdated,
		s.`intOrder`,
		s.`tintTemplate`                      AS siteTintTemplate,
		s.`GUID`                              AS siteGUID,
		IF(
			t.vchrTraitType = 'NEAREST_PLACE',
			t.vchrValue,
			NULL
		)                                     AS nearestNamedPlace,
		IF(
			t.vchrTraitType = 'Other',
			t.vchrValue,
			NULL
		)                                     AS collectionTwoTraitOther

	FROM
		tblSite s LEFT JOIN
		collectionTwo_location_traits t
			ON t.intIntraCatID = s.intSiteID

		LEFT JOIN tblPoliticalRegion r1 ON s.intPoliticalRegionID = r1.intPoliticalRegionID
		LEFT JOIN tblPoliticalRegion r2 ON r1.intParentID = r2.intPoliticalRegionID
		LEFT JOIN tblPoliticalRegion r3 ON r2.intParentID = r3.intPoliticalRegionID
		LEFT JOIN tblPoliticalRegion r4 ON r3.intParentID = r4.intPoliticalRegionID
		LEFT JOIN tblPoliticalRegion r5 ON r4.intParentID = r5.intPoliticalRegionID


	ORDER BY s.intSiteID;
