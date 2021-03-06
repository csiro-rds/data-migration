CREATE OR REPLACE VIEW cms_identification_view AS
	SELECT
		t.SCIENTIFICNAME,
		i.LEGACYID_IDENT,
		i.LEGACYID_TAXON,
		i.LEGACYID_CU,
		i.VERBATIMDATEIDENTIFIED,
		i.DATEIDENTIFIED,
		CASE i.CURRENTDETFLAG
		WHEN 'Y'
			THEN 'yes'
		WHEN 'N'
			THEN 'no'
		END                                AS CURRENTDETFLAG,
		i.IDENTIFIEDBY,
		i.TAGNAME,
		i.AUTHORISEDBY,
		i.IDENTIFICATIONREFERENCES,
		i.IDENTIFICATIONREMARKS,
		i.INTERNALNOTE,
		REPLACE(i.IDENTIFIERROLE, '.', '') AS IDENTIFIERROLE,
		IF(
			i.IDENTIFICATIONQUALIFIER = '?',
			'questionable',
			REPLACE(i.IDENTIFICATIONQUALIFIER, '.', '')
		)                                  AS IDENTIFICATIONQUALIFIER,
		CASE i.NAMEADDENDUM
		WHEN 's. lat.'
			THEN 'sl'
		WHEN 's. l.'
			THEN 'sl'
		WHEN 's. str.'
			THEN 'sstr'
		WHEN 's. s.'
			THEN 'sstr'
		WHEN 'agg.'
			THEN 'agg'
		END
										   AS NAMEADDENDUM,
		i.IDENTIFICATIONMETHOD,
		i.REFERENCEPAGE,
		i.IDENTIFICATIONREFERENCECODE,
		i.IDENTIFICATIONVERIFYSTATUS,
		i.IDENTIFICATIONSEQUENCE,
		i.CREATEDBY,
		i.CREATEDATE,
		i.UPDATEDBY,
		i.UPDATEDATE,
		u.RECORDNUMBER,
		u.EXCHANGEUNITID

	FROM CMS_IDENTIFICATION_V i
		JOIN
		cms_taxon_view t ON i.LEGACYID_TAXON = t.LEGACYID_TAXON
		JOIN
		CMS_COLLECTION_UNIT_V u ON u.LEGACYID_CU = i.LEGACYID_CU
	ORDER BY
		i.LEGACYID_CU, i.IDENTIFICATIONSEQUENCE, i.LEGACYID_TAXON;
